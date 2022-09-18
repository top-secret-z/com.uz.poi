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
namespace poi\system\user\notification\event;

use poi\system\poi\PoiDataHandler;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;

/**
 * User notification event for poi commments.
 */
class PoiCommentUserNotificationEvent extends AbstractSharedUserNotificationEvent
{
    /**
     * @inheritDoc
     */
    protected $stackable = true;

    /**
     * @inheritDoc
     */
    protected function prepare()
    {
        PoiDataHandler::getInstance()->cachePoiID($this->getUserNotificationObject()->objectID);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        $count = \count($this->getAuthors());
        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable('poi.poi.comment.notification.title.stacked', [
                'count' => $count,
                'timesTriggered' => $this->notification->timesTriggered,
            ]);
        }

        return $this->getLanguage()->get('poi.poi.comment.notification.title');
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        $poi = PoiDataHandler::getInstance()->getPoi($this->getUserNotificationObject()->objectID);

        $authors = $this->getAuthors();
        if (\count($authors) > 1) {
            if (isset($authors[0])) {
                unset($authors[0]);
            }
            $count = \count($authors);

            return $this->getLanguage()->getDynamicVariable('poi.poi.comment.notification.message.stacked', [
                'author' => $this->author,
                'authors' => \array_values($authors),
                'commentID' => $this->getUserNotificationObject()->commentID,
                'count' => $count,
                'poi' => $poi,
                'others' => $count - 1,
                'guestTimesTriggered' => $this->notification->guestTimesTriggered,
            ]);
        }

        return $this->getLanguage()->getDynamicVariable('poi.poi.comment.notification.message', [
            'poi' => $poi,
            'author' => $this->author,
            'commentID' => $this->getUserNotificationObject()->commentID,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getEmailMessage($notificationType = 'instant')
    {
        return [
            'message-id' => 'com.uz.poi.poi.comment/' . $this->getUserNotificationObject()->commentID,
            'template' => 'email_notification_comment',
            'application' => 'wcf',
            'variables' => [
                'commentID' => $this->getUserNotificationObject()->commentID,
                'poi' => PoiDataHandler::getInstance()->getPoi($this->getUserNotificationObject()->objectID),
                'languageVariablePrefix' => 'poi.poi.comment.notification',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        $poi = PoiDataHandler::getInstance()->getPoi($this->getUserNotificationObject()->objectID);

        return LinkHandler::getInstance()->getLink('Poi', [
            'application' => 'poi',
            'object' => $poi,
        ], '#comments');
    }

    /**
     * @inheritDoc
     */
    public function getEventHash()
    {
        return \sha1($this->eventID . '-' . $this->getUserNotificationObject()->objectID);
    }

    /**
     * @inheritDoc
     */
    public function checkAccess()
    {
        return PoiDataHandler::getInstance()->getPoi($this->getUserNotificationObject()->objectID)->canRead();
    }
}
