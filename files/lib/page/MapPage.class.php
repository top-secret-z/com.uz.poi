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
use poi\data\category\PoiCategoryNodeTree;
use poi\system\cache\builder\StatsCacheBuilder;
use wcf\data\category\Category;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the pois map.
 */
class MapPage extends AbstractPage
{
    /**
     * categories
     */
    public $categoryList;

    public $category;

    public $categoryID = 0;

    public $categoryIDs = [];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.poi.canViewPoi'];

    /**
     * @inheritDoc
     */
    public $enableTracking = true;

    /**
     * poi statistics
     */
    public $stats = [];

    /**
     * poi search
     */
    public $poiSearch = '';

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // categories
        $categoryTree = new PoiCategoryNodeTree('com.uz.poi.category');
        $this->categoryList = $categoryTree->getIterator();
        $this->categoryList->setMaxDepth(0);

        // stats
        if (POI_INDEX_ENABLE_STATS) {
            $this->stats = StatsCacheBuilder::getInstance()->getData();
        }
    }

    /**
     * @inheritdoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['poiSearch'])) {
            $this->poiSearch = StringUtil::trim($_REQUEST['poiSearch']);
        }
        if (!empty($_REQUEST['id'])) {
            $this->categoryID = \intval($_REQUEST['id']);

            $this->category = new PoiCategory(new Category($this->categoryID));
            if ($this->category === null) {
                throw new IllegalLinkException();
            }
            if (!$this->category->isAccessible()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        // categories to be set checked in map filter, only if $this->categoryID
        $this->categoryIDs = [];

        if ($this->categoryID) {
            $this->categoryIDs[] = $this->categoryID;

            // get children
            $children = $this->category->getAllChildCategories();
            if (\count($children)) {
                $accessibleIDs = PoiCategory::getAccessibleCategoryIDs();

                foreach ($children as $category) {
                    if (\in_array($category->categoryID, $accessibleIDs)) {
                        $this->categoryIDs[] = $category->categoryID;
                    }
                }
            }
        }

        WCF::getTPL()->assign([
            'categoryList' => $this->categoryList,
            'stats' => $this->stats,
            'allowSpidersToIndexThisPage' => true,
            'poiSearch' => $this->poiSearch,
            'category' => $this->category,
            'categoryID' => $this->categoryID,
            'categoryIDs' => $this->categoryIDs,
            'hideGooglePois' => POI_MAP_HIDE_GOOGLE_POIS,
        ]);
    }
}
