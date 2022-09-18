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
namespace poi\system\comment\manager;

use poi\data\poi\Poi;
use poi\data\poi\PoiEditor;
use poi\data\poi\ViewablePoiList;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\comment\manager\AbstractCommentManager;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Poi comment manager implementation.
 */
class PoiCommentManager extends AbstractCommentManager implements IViewableLikeProvider
{
    /**
     * @inheritdoc
     */
    protected $permissionAdd = 'user.poi.canAddComment';

    protected $permissionAddWithoutModeration = 'user.poi.canAddCommentWithoutModeration';

    /**
     * @inheritdoc
     */
    protected $permissionCanModerate = 'mod.poi.canModerateComment';

    protected $permissionDelete = 'user.poi.canDeleteComment';

    protected $permissionEdit = 'user.poi.canEditComment';

    protected $permissionModDelete = 'mod.poi.canDeleteComment';

    protected $permissionModEdit = 'mod.poi.canEditComment';

    /**
     * @inheritdoc
     */
    public function isAccessible($objectID, $validateWritePermission = false)
    {
        // check object id
        $poi = new Poi($objectID);
        if (!$poi->poiID || !$poi->canRead()) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getLink($objectTypeID, $objectID)
    {
        return LinkHandler::getInstance()->getLink('Poi', [
            'application' => 'poi',
            'id' => $objectID,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getCommentLink(Comment $comment)
    {
        return $this->getLink($comment->objectTypeID, $comment->objectID) . '#comments/comment' . $comment->commentID;
    }

    /**
     * @inheritDoc
     */
    public function getResponseLink(CommentResponse $response)
    {
        return $this->getLink($response->getComment()->objectTypeID, $response->getComment()->objectID) . '#comments/comment' . $response->commentID . '/response' . $response->responseID;
    }

    /**
     * @inheritdoc
     */
    public function getTitle($objectTypeID, $objectID, $isResponse = false)
    {
        if ($isResponse) {
            return WCF::getLanguage()->get('poi.poi.commentResponse');
        }

        return WCF::getLanguage()->getDynamicVariable('poi.poi.comment');
    }

    /**
     * @inheritdoc
     */
    public function updateCounter($objectID, $value)
    {
        $poi = new Poi($objectID);
        $editor = new PoiEditor($poi);
        $editor->updateCounters([
            'comments' => $value,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function prepare(array $likes)
    {
        if (!WCF::getSession()->getPermission('user.poi.canViewPoi')) {
            return;
        }

        $commentLikeObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.like.likeableObject', 'com.woltlab.wcf.comment');

        $commentIDs = $responseIDs = [];
        foreach ($likes as $like) {
            if ($like->objectTypeID == $commentLikeObjectType->objectTypeID) {
                $commentIDs[] = $like->objectID;
            } else {
                $responseIDs[] = $like->objectID;
            }
        }

        // fetch response
        $userIDs = $responses = [];
        if (!empty($responseIDs)) {
            $responseList = new CommentResponseList();
            $responseList->setObjectIDs($responseIDs);
            $responseList->readObjects();
            $responses = $responseList->getObjects();

            foreach ($responses as $response) {
                $commentIDs[] = $response->commentID;
                if ($response->userID) {
                    $userIDs[] = $response->userID;
                }
            }
        }

        // fetch comments
        $commentList = new CommentList();
        $commentList->setObjectIDs($commentIDs);
        $commentList->readObjects();
        $comments = $commentList->getObjects();

        // fetch users
        $users = [];
        $poiIDs = [];
        foreach ($comments as $comment) {
            $poiIDs[] = $comment->objectID;
            if ($comment->userID) {
                $userIDs[] = $comment->userID;
            }
        }
        if (!empty($userIDs)) {
            $users = UserProfileRuntimeCache::getInstance()->getObjects(\array_unique($userIDs));
        }

        $pois = [];
        if (!empty($poiIDs)) {
            $poiList = new ViewablePoiList();
            $poiList->setObjectIDs($poiIDs);
            $poiList->readObjects();
            $pois = $poiList->getObjects();
        }

        // set message
        foreach ($likes as $like) {
            if ($like->objectTypeID == $commentLikeObjectType->objectTypeID) {
                // comment like
                if (isset($comments[$like->objectID])) {
                    $comment = $comments[$like->objectID];

                    if (isset($pois[$comment->objectID]) && $pois[$comment->objectID]->canRead()) {
                        $like->setIsAccessible();

                        // short output
                        $text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.uz.poi.poiComment', [
                            'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
                            'comment' => $comment,
                            'poi' => $pois[$comment->objectID],
                            'like' => $like,
                        ]);
                        $like->setTitle($text);

                        // output
                        $like->setDescription($comment->getExcerpt());
                    }
                }
            } else {
                // response like
                if (isset($responses[$like->objectID])) {
                    $response = $responses[$like->objectID];
                    $comment = $comments[$response->commentID];

                    if (isset($pois[$comment->objectID]) && $pois[$comment->objectID]->canRead()) {
                        $like->setIsAccessible();

                        // short output
                        $text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.uz.poi.poiComment.response', [
                            'responseAuthor' => $comment->userID ? $users[$response->userID] : null,
                            'response' => $response,
                            'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
                            'poi' => $pois[$comment->objectID],
                            'like' => $like,
                        ]);
                        $like->setTitle($text);

                        // output
                        $like->setDescription($response->getExcerpt());
                    }
                }
            }
        }
    }
}
