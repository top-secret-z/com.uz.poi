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
namespace poi\system\box;

use wcf\data\user\UserProfileList;
use wcf\system\box\AbstractBoxController;
use wcf\system\WCF;

/**
 * Box for most active authors.
 */
class MostActiveAuthorsBoxController extends AbstractBoxController
{
    /**
     * @inheritDoc
     */
    protected static $supportedPositions = ['sidebarLeft', 'sidebarRight'];

    /**
     * @inheritDoc
     */
    protected function loadContent()
    {
        $userProfileList = new UserProfileList();
        $userProfileList->getConditionBuilder()->add('user_table.poiPois > ?', [0]);
        $userProfileList->sqlOrderBy = 'poiPois DESC';
        $userProfileList->sqlLimit = 5;
        $userProfileList->readObjects();

        if (\count($userProfileList)) {
            $this->content = WCF::getTPL()->fetch('boxMostActiveAuthors', 'poi', ['mostActiveAuthors' => $userProfileList], true);
        }
    }
}
