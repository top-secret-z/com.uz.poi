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
namespace poi\data\poi;

use LogicException;
use poi\data\category\PoiCategory;
use poi\data\cover\photo\CoverPhoto;
use wcf\data\attachment\GroupedAttachmentList;
use wcf\data\DatabaseObject;
use wcf\data\IMessage;
use wcf\data\TUserContent;
use wcf\system\bbcode\AttachmentBBCode;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\image\cover\photo\CoverPhotoImage;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Represents a poi.
 */
class Poi extends DatabaseObject implements IMessage, IRouteController
{
    use TUserContent;

    /**
     * option values
     */
    protected $optionValues;

    /**
     * true if embedded objects have already been loaded
     */
    protected $embeddedObjectsLoaded = false;

    /**
     * poi's category
     */
    protected $category;

    /**
     * poi's cover photo
     */
    protected $coverPhoto;

    protected $coverPhotoImage;

    /**
     * list of visited pois
     */
    protected static $visitorCache;

    /**
     * Returns and assigns embedded attachments.
     */
    public function getAttachments()
    {
        if (MODULE_ATTACHMENT == 1 && $this->attachments) {
            $attachmentList = new GroupedAttachmentList('com.uz.poi.poi');
            $attachmentList->getConditionBuilder()->add('attachment.objectID IN (?)', [$this->poiID]);
            $attachmentList->readObjects();
            $attachmentList->setPermissions([
                'canDownload' => WCF::getSession()->getPermission('user.poi.canDownloadAttachment'),
                'canViewPreview' => WCF::getSession()->getPermission('user.poi.canDownloadAttachment'),
            ]);

            AttachmentBBCode::setAttachmentList($attachmentList);

            return $attachmentList;
        }

        return null;
    }

    /**
     * Returns the category of the poi.
     */
    public function getCategory()
    {
        if ($this->category === null && $this->categoryID) {
            $this->category = PoiCategory::getCategory($this->categoryID);
        }

        return $this->category;
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @inheritDoc
     */
    public function getFormattedMessage()
    {
        $this->loadEmbeddedObjects();

        $processor = new HtmlOutputProcessor();
        $processor->process($this->getMessage(), 'com.uz.poi.poi', $this->poiID);

        return $processor->getHtml();
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->getFormattedMessage();
    }

    /**
     * Returns a simplified version of the formatted message.
     */
    public function getSimplifiedFormattedMessage()
    {
        $this->loadEmbeddedObjects();

        $processor = new HtmlOutputProcessor();
        $processor->setOutputType('text/simplified-html');
        $processor->process($this->getMessage(), 'com.uz.poi.poi', $this->poiID);

        return $processor->getHtml();
    }

    /**
     * Returns a version of this message optimized for use in emails.
     */
    public function getMailText($mimeType = 'text/plain')
    {
        switch ($mimeType) {
            case 'text/plain':
                $processor = new HtmlOutputProcessor();
                $processor->setOutputType('text/plain');
                $processor->process($this->getMessage(), 'com.uz.poi.poi', $this->poiID);

                return $processor->getHtml();
            case 'text/html':
                return $this->getSimplifiedFormattedMessage();
        }

        throw new LogicException('Unreachable');
    }

    /**
     * Loads the embedded objects.
     */
    public function loadEmbeddedObjects()
    {
        if ($this->hasEmbeddedObjects && !$this->embeddedObjectsLoaded) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects('com.uz.poi.poi', [$this->poiID]);
            $this->embeddedObjectsLoaded = true;
        }
    }

    /**
     * @inheritDoc
     */
    public function getExcerpt($maxLength = 255)
    {
        return StringUtil::truncateHTML($this->getSimplifiedFormattedMessage(), $maxLength);
    }

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        return LinkHandler::getInstance()->getLink('Poi', [
            'application' => 'poi',
            'object' => $this,
            'forceFrontend' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getSubject();
    }

    /**
     * Returns true if the active user can delete this poi.
     */
    public function canDelete()
    {
        if (WCF::getSession()->getPermission('mod.poi.canDeletePoi')) {
            return true;
        }

        if ($this->isOwner() && WCF::getSession()->getPermission('user.poi.canDeletePoi')) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the active user can edit this poi.
     */
    public function canEdit()
    {
        if (WCF::getSession()->getPermission('mod.poi.canEditPoi')) {
            return true;
        }

        if ($this->isAuthor() && WCF::getSession()->getPermission('user.poi.canEditPoi')) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the active user can view this poi.
     */
    public function canRead()
    {
        if ($this->isDeleted && !WCF::getSession()->getPermission('mod.poi.canViewDeletedPoi')) {
            return false;
        }

        if ($this->isDisabled && !WCF::getSession()->getPermission('mod.poi.canModeratePoi') && !$this->isAuthor()) {
            return false;
        }

        if ($this->getCategory()) {
            return $this->getCategory()->isAccessible();
        }

        return WCF::getSession()->getPermission('user.poi.canViewPoi');
    }

    /**
     * @inheritDoc
     */
    public function isVisible()
    {
        return $this->canRead();
    }

    /**
     * Returns poi's ip address, converts into IPv4 if applicable.
     */
    public function getIpAddress()
    {
        if ($this->ipAddress) {
            return UserUtil::convertIPv6To4($this->ipAddress);
        }

        return '';
    }

    /**
     * Returns the subject of this poi.
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Returns the teaser of this poi.
     */
    public function getTeaser()
    {
        return $this->teaser;
    }

    /**
     * Returns true if the active user is the owner of the poi.
     */
    public function isOwner()
    {
        return WCF::getUser()->userID && $this->userID == WCF::getUser()->userID;
    }

    /**
     * Returns true if the active user is the author of the poi.
     * Leave for potential extension
     */
    public function isAuthor()
    {
        return $this->isOwner();
    }

    /**
     * Returns true if this poi has got old versions in edit history.
     */
    public function hasOldVersions()
    {
        if (!MODULE_EDIT_HISTORY) {
            return false;
        }
        if (EDIT_HISTORY_EXPIRATION == 0) {
            return $this->lastVersionTime > 0;
        }

        return $this->lastVersionTime > (TIME_NOW - EDIT_HISTORY_EXPIRATION * 86400);
    }

    /**
     * Returns a specific option value.
     */
    public function getOptionValue($optionID)
    {
        if ($this->optionValues === null) {
            $this->optionValues = [];
            $sql = "SELECT    optionID, optionValue
                    FROM    poi" . WCF_N . "_poi_option_value
                    WHERE    poiID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$this->poiID]);

            $this->optionValues = $statement->fetchMap('optionID', 'optionValue');
        }

        if (isset($this->optionValues[$optionID])) {
            return $this->optionValues[$optionID];
        }

        return '';
    }

    /**
     * Returns true if the active user is a visitor of this poi.
     */
    public function isVisitor()
    {
        if (!WCF::getUser()->userID) {
            return false;
        }

        if (self::$visitorCache === null) {
            self::loadVisitorCache();
        }

        return isset(self::$visitorCache[$this->poiID]);
    }

    /**
     * Returns the ids of visited pois.
     */
    public static function getVisitorCache()
    {
        if (self::$visitorCache === null) {
            self::loadVisitorCache();
        }

        return self::$visitorCache;
    }

    /**
     * Loads the list of visited pois.
     */
    protected static function loadVisitorCache()
    {
        self::$visitorCache = [];
        if (!WCF::getUser()->userID) {
            return;
        }

        $sql = "SELECT DISTINCT    poiID
                FROM            poi" . WCF_N . "_poi_visit
                WHERE            userID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([WCF::getUser()->userID]);
        while ($poiID = $statement->fetchColumn()) {
            self::$visitorCache[$poiID] = $poiID;
        }
    }

    /**
     * Returns the number of visits from visit table.
     */
    public function getVisits()
    {
        $sql = "SELECT        COUNT(*) as count
                FROM        poi" . WCF_N . "_poi_visit
                WHERE        poiID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->poiID]);

        return $statement->fetchColumn();
    }

    /**
     * Check whether Poi has a cover photo
     */
    public function hasCoverPhoto()
    {
        return $this->coverPhotoID;
    }

    /**
     * Returns Poi's cover photo
     */
    public function getCoverPhoto()
    {
        if ($this->coverPhoto === null && $this->coverPhotoID) {
            $this->coverPhoto = new CoverPhoto($this->coverPhotoID);
        }

        return $this->coverPhoto;
    }

    /**
     * Returns Poi's cover photo image
     */
    public function getCoverPhotoImage()
    {
        if ($this->coverPhotoImage === null) {
            $this->coverPhotoImage = new CoverPhotoImage($this->getCoverPhoto());
        }

        return $this->coverPhotoImage;
    }

    /**
     * Sets Poi's cover photo
     */
    public function setCoverPhoto(CoverPhoto $coverPhoto)
    {
        if ($coverPhoto->coverPhotoID == $this->coverPhotoID) {
            $this->coverPhoto = $coverPhoto;
        }
    }
}
