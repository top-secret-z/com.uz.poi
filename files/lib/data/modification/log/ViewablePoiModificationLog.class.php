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
namespace poi\data\modification\log;

use poi\data\poi\Poi;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\modification\log\IViewableModificationLog;
use wcf\data\modification\log\ModificationLog;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\WCF;

/**
 * Provides a viewable poi modification log.
 */
class ViewablePoiModificationLog extends DatabaseObjectDecorator implements IViewableModificationLog
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = ModificationLog::class;

    /**
     * Poi this modification log belongs to
     */
    protected $poi;

    /**
     * user profile object
     */
    protected $userProfile;

    /**
     * Returns readable representation of current log entry.
     */
    public function __toString()
    {
        return WCF::getLanguage()->getDynamicVariable('poi.poi.log.poi.' . $this->action, ['additionalData' => $this->additionalData]);
    }

    /**
     * Returns the user profile object.
     */
    public function getUserProfile()
    {
        if ($this->userProfile === null) {
            $this->userProfile = new UserProfile(new User(null, $this->getDecoratedObject()->data));
        }

        return $this->userProfile;
    }

    /**
     * Sets the Poi this modification log belongs to.
     */
    public function setPoi(Poi $poi)
    {
        if ($poi->poiID == $this->objectID) {
            $this->poi = $poi;
        }
    }

    /**
     * @inheritDoc
     */
    public function getAffectedObject()
    {
        return $this->poi;
    }
}
