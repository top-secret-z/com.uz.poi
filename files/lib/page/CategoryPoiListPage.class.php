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

use poi\data\category\PoiCategory;
use poi\data\poi\CategoryPoiList;
use poi\system\POICore;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of pois in a certain category.
 */
class CategoryPoiListPage extends PoiListPage
{
    /**
     * @inheritDoc
     */
    public $templateName = 'poiList';

    /**
     * category of pois
     */
    public $category;

    public $categoryID = 0;

    /**
     * @inheritDoc
     */
    public $controllerName = 'CategoryPoiList';

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        if (isset($_REQUEST['id'])) {
            $this->categoryID = \intval($_REQUEST['id']);
        }
        $this->category = PoiCategory::getCategory($this->categoryID);
        if ($this->category === null) {
            throw new IllegalLinkException();
        }
        $this->controllerParameters['object'] = $this->category;
        parent::readParameters();

        $this->canonicalURL = LinkHandler::getInstance()->getLink('CategoryPoiList', [
            'application' => 'poi',
            'object' => $this->category,
        ], ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : ''));
    }

    /**
     * @inheritDoc
     */
    public function checkPermissions()
    {
        parent::checkPermissions();

        if (!$this->category->isAccessible()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        $this->objectList = new CategoryPoiList($this->categoryID, true);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        POICore::getInstance()->setLocation($this->category->getParentCategories());
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'categoryID' => $this->categoryID,
            'category' => $this->category,
            'feedControllerName' => 'CategoryPoiListFeed',
            'controllerObject' => $this->category,
        ]);
    }
}
