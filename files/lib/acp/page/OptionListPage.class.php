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
namespace poi\acp\page;

use poi\data\poi\option\PoiOptionList;
use wcf\page\SortablePage;

/**
 * Shows the list of options.
 */
class OptionListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'poi.acp.menu.link.poi.option.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.poi.canManageOption'];

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'showOrder';

    /**
     * @inheritDoc
     */
    public $objectListClassName = PoiOptionList::class;

    /**
     * @inheritDoc
     */
    public $validSortFields = ['optionID', 'optionTitle', 'optionType', 'showOrder', 'required'];
}
