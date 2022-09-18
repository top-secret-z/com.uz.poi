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
namespace poi\system\user\activity\event;

use poi\data\poi\ViewablePoiList;
use wcf\data\comment\CommentList;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\user\UserList;
use wcf\system\SingletonFactory;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\WCF;

/**
 * User activity event implementation for poi comment responses.
 */
class PoiCommentResponseUserActivityEvent extends SingletonFactory implements IUserActivityEvent
{
    /**
     * @inheritDoc
     */
    public function prepare(array $events)
    {
        $responseIDs = [];
        foreach ($events as $event) {
            $responseIDs[] = $event->objectID;
        }

        // fetch responses
        $responseList = new CommentResponseList();
        $responseList->setObjectIDs($responseIDs);
        $responseList->readObjects();
        $responses = $responseList->getObjects();

        // fetch comments
        $commentIDs = $comments = [];
        foreach ($responses as $response) {
            $commentIDs[] = $response->commentID;
        }
        if (!empty($commentIDs)) {
            $commentList = new CommentList();
            $commentList->setObjectIDs($commentIDs);
            $commentList->readObjects();
            $comments = $commentList->getObjects();
        }

        // fetch pois
        $poiIDs = $pois = [];
        foreach ($comments as $comment) {
            $poiIDs[] = $comment->objectID;
        }
        if (!empty($poiIDs)) {
            $poiList = new ViewablePoiList();
            $poiList->setObjectIDs($poiIDs);
            $poiList->readObjects();
            $pois = $poiList->getObjects();
        }

        // fetch users
        $userIDs = $user = [];
        foreach ($comments as $comment) {
            $userIDs[] = $comment->userID;
        }
        if (!empty($userIDs)) {
            $userList = new UserList();
            $userList->setObjectIDs($userIDs);
            $userList->readObjects();
            $users = $userList->getObjects();
        }

        // set message
        foreach ($events as $event) {
            if (isset($responses[$event->objectID])) {
                $response = $responses[$event->objectID];
                $comment = $comments[$response->commentID];
                if (isset($pois[$comment->objectID]) && isset($users[$comment->userID])) {
                    $poi = $pois[$comment->objectID];

                    // check permissions
                    if (!$poi->canRead()) {
                        continue;
                    }
                    $event->setIsAccessible();

                    // title
                    $text = WCF::getLanguage()->getDynamicVariable('poi.poi.recentActivity.poiCommentResponse', [
                        'commentAuthor' => $users[$comment->userID],
                        'commentID' => $comment->commentID,
                        'responseID' => $response->responseID,
                        'poi' => $poi,
                    ]);
                    $event->setTitle($text);

                    // description
                    $event->setDescription($response->getExcerpt());
                    continue;
                }
            }

            $event->setIsOrphaned();
        }
    }
}
