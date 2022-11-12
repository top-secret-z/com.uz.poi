<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace poi\page;

use poi\data\poi\AccessiblePoiList;
use poi\data\poi\PoiAction;
use poi\data\poi\PoiEditor;
use poi\data\poi\ViewablePoi;
use poi\system\option\PoiOptionHandler;
use poi\system\POICore;
use wcf\data\attachment\Attachment;
use wcf\page\AbstractPage;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\LanguageFactory;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\MetaTagHandler;
use wcf\system\reaction\ReactionHandler;
use wcf\system\request\LinkHandler;
use wcf\system\tagging\TagEngine;
use wcf\system\WCF;

/**
 * Shows a poi.
 */
class PoiPage extends AbstractPage
{
    /**
     * poi
     */
    public $poiID = 0;

    public $poi;

    /**
     * comments
     */
    public $commentObjectTypeID = 0;

    public $commentManager;

    public $commentList;

    /**
     * list of other pois by this author
     */
    public $userPoiList;

    /**
     * list of tags
     */
    public $tags = [];

    /**
     * like data for pois
     */
    public $poiLikeData = [];

    /**
     * attachment list
     */
    public $attachmentList;

    /**
     * user profile of the poi author
     */
    public $userProfile;

    /**
     * poi's category
     */
    public $category;

    /**
     * option handler
     */
    public $optionHandler;

    /**
     * time of visit for current user
     */
    public $visitTime;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['id'])) {
            $this->poiID = \intval($_REQUEST['id']);
        }
        $this->poi = ViewablePoi::getPoi($this->poiID);
        if ($this->poi === null) {
            throw new IllegalLinkException();
        }

        // check permissions
        if (!$this->poi->canRead()) {
            throw new PermissionDeniedException();
        }

        $this->canonicalURL = $this->poi->getLink();
        $this->category = $this->poi->getCategory();
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // init options
        $this->optionHandler = new PoiOptionHandler(false);
        $this->optionHandler->setPoi($this->poi->getDecoratedObject());
        $this->optionHandler->enableEditMode(false);

        // update poi visit
        if ($this->poi->isNew()) {
            $poiAction = new PoiAction([$this->poi->getDecoratedObject()], 'markAsRead', [
                'viewablePoi' => $this->poi,
            ]);
            $poiAction->executeAction();
        }

        $poiEditor = new PoiEditor($this->poi->getDecoratedObject());
        $poiEditor->updateCounters(['views' => 1]);

        // get author's user profile
        $this->userProfile = $this->poi->getUserProfile();

        // get comments
        if ($this->poi->enableComments) {
            $this->commentObjectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.uz.poi.poiComment');
            $this->commentManager = CommentHandler::getInstance()->getObjectType($this->commentObjectTypeID)->getProcessor();
            $this->commentList = CommentHandler::getInstance()->getCommentList($this->commentManager, $this->commentObjectTypeID, $this->poiID);
        }

        // get other pois by this author
        $this->userPoiList = new AccessiblePoiList();
        $this->userPoiList->getConditionBuilder()->add('poi.userID = ?', [$this->poi->userID]);
        $this->userPoiList->getConditionBuilder()->add('poi.poiID <> ?', [$this->poi->poiID]);
        $this->userPoiList->sqlLimit = 5;
        $this->userPoiList->readObjects();

        // get tags
        if (MODULE_TAGGING && WCF::getSession()->getPermission('user.tag.canViewTag')) {
            $this->tags = TagEngine::getInstance()->getObjectTags(
                'com.uz.poi.poi',
                $this->poi->poiID,
                [$this->poi->languageID === null ? LanguageFactory::getInstance()->getDefaultLanguageID() : ""]
            );
        }

        // likes
        if (MODULE_LIKE) {
            $objectType = ReactionHandler::getInstance()->getObjectType('com.uz.poi.likeablePoi');
            ReactionHandler::getInstance()->loadLikeObjects($objectType, [$this->poiID]);
            $this->poiLikeData = ReactionHandler::getInstance()->getLikeObjects($objectType);
        }

        // set location
        POICore::getInstance()->setLocation($this->poi->getCategory()->getParentCategories(), $this->poi->getCategory());

        // add meta/og tags
        MetaTagHandler::getInstance()->addTag('og:title', 'og:title', $this->poi->getSubject() . ' - ' . WCF::getLanguage()->get(PAGE_TITLE), true);
        MetaTagHandler::getInstance()->addTag('og:url', 'og:url', LinkHandler::getInstance()->getLink('Poi', ['application' => 'poi', 'object' => $this->poi, 'appendSession' => false]), true);
        MetaTagHandler::getInstance()->addTag('og:type', 'og:type', 'article', true);
        MetaTagHandler::getInstance()->addTag('og:description', 'og:description', $this->poi->getTeaser(), true);

        // add attachments as og:image tags
        $i = 0;
        $this->attachmentList = $this->poi->getAttachments();
        $this->poi->loadEmbeddedObjects();
        MessageEmbeddedObjectManager::getInstance()->setActiveMessage('com.uz.poi.poi', $this->poiID);
        $attachments = \array_merge(($this->attachmentList !== null ? $this->attachmentList->getGroupedObjects($this->poiID) : []), MessageEmbeddedObjectManager::getInstance()->getObjects('com.woltlab.wcf.attachment'));

        foreach ($attachments as $attachment) {
            if ($attachment->showAsImage() && $attachment->width >= 200 && $attachment->height >= 200) {
                MetaTagHandler::getInstance()->addTag('og:image' . $i, 'og:image', LinkHandler::getInstance()->getLink('Attachment', ['object' => $attachment]), true);
                MetaTagHandler::getInstance()->addTag('og:image:width' . $i, 'og:image:width', $attachment->width, true);
                MetaTagHandler::getInstance()->addTag('og:image:height' . $i, 'og:image:height', $attachment->height, true);
                $i++;
            }
        }

        // add tags as meta keywords
        if (!empty($this->tags)) {
            $keywords = '';
            foreach ($this->tags as $tag) {
                if (!empty($keywords)) {
                    $keywords .= ', ';
                }
                $keywords .= $tag->name;
            }
            MetaTagHandler::getInstance()->addTag('keywords', 'keywords', $keywords);
        }

        // get visit time
        if (!$this->poi->isVisitor()) {
            $this->visitTime = 0;
        } else {
            $sql = "SELECT    time
                    FROM    poi" . WCF_N . "_poi_visit
                    WHERE        userID = ? AND poiID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([WCF::getUser()->userID, $this->poiID]);
            $this->visitTime = $statement->fetchColumn();
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'poi' => $this->poi,
            'poiID' => $this->poiID,
            'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.uz.poi.poi')),
            'userProfile' => $this->userProfile,
            'commentCanAdd' => WCF::getSession()->getPermission('user.poi.canAddComment'),
            'commentList' => $this->commentList,
            'commentObjectTypeID' => $this->commentObjectTypeID,
            'lastCommentTime' => $this->commentList ? $this->commentList->getMinCommentTime() : 0,
            'likeData' => (MODULE_LIKE && $this->commentList) ? $this->commentList->getLikeData() : [],
            'userPoiList' => $this->userPoiList,
            'tags' => $this->tags,
            'poiLikeData' => $this->poiLikeData,
            'attachmentList' => $this->attachmentList,
            'allowSpidersToIndexThisPage' => true,
            'category' => $this->category,
            'options' => $this->optionHandler->getOptions(),
            'visitTime' => $this->visitTime,
        ]);
    }
}
