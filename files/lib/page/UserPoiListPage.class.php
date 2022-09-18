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

use wcf\data\user\User;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of pois by a certain user.
 */
class UserPoiListPage extends PoiListPage
{
    /**
     * @inheritDoc
     */
    public $templateName = 'poiList';

    /**
     * poi user
     */
    public $user;

    public $userID = 0;

    /**
     * @inheritDoc
     */
    public $controllerName = 'UserPoiList';

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        if (isset($_REQUEST['id'])) {
            $this->userID = \intval($_REQUEST['id']);
        }
        $this->user = new User($this->userID);
        if (!$this->user->userID) {
            throw new IllegalLinkException();
        }
        $this->controllerParameters['object'] = $this->user;
        parent::readParameters();

        $this->canonicalURL = LinkHandler::getInstance()->getLink('UserPoiList', [
            'application' => 'poi',
            'object' => $this->user,
        ], ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : ''));
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->getConditionBuilder()->add('poi.userID = ?', [$this->userID]);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'userID' => $this->userID,
            'user' => $this->user,
            'feedControllerName' => '',
            'controllerObject' => $this->user,
        ]);
    }
}
