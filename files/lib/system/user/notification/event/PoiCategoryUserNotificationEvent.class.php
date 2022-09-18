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
 * Notification event for poi categoriess.
 */
class PoiCategoryUserNotificationEvent extends AbstractSharedUserNotificationEvent
{
    /**
     * @inheritDoc
     */
    protected function prepare()
    {
        PoiDataHandler::getInstance()->cachePoiID($this->getUserNotificationObject()->poiID);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getLanguage()->get('poi.poi.category.notification.title');
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->getLanguage()->getDynamicVariable('poi.poi.category.notification.message', [
            'poi' => $this->userNotificationObject,
            'author' => $this->author,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getEmailMessage($notificationType = 'instant')
    {
        return [
            'message-id' => 'com.uz.poi.poi/' . $this->getUserNotificationObject()->poiID,
            'template' => 'email_notification_category',
            'application' => 'poi',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        return LinkHandler::getInstance()->getLink('Poi', [
            'application' => 'poi',
            'object' => $this->getUserNotificationObject(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function checkAccess()
    {
        return $this->getUserNotificationObject()->canRead();
    }
}
