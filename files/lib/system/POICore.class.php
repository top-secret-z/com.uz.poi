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
namespace poi\system;

use poi\data\category\PoiCategory;
use poi\data\poi\Poi;
use poi\page\PoiListPage;
use wcf\system\application\AbstractApplication;
use wcf\system\page\PageLocationManager;

/**
 * Extends the main WCF class by poi specific functions.
 */
class POICore extends AbstractApplication
{
    /**
     * @inheritDoc
     */
    protected $primaryController = PoiListPage::class;

    /**
     * Sets location data.
     */
    public function setLocation(array $parentCategories = [], ?PoiCategory $category = null, ?Poi $poi = null)
    {
        // add poi
        if ($poi !== null) {
            PageLocationManager::getInstance()->addParentLocation('com.uz.poi.Poi', $poi->poiID, $poi);
        }

        // add category
        if ($category !== null) {
            PageLocationManager::getInstance()->addParentLocation('com.uz.poi.CategoryPoiList', $category->categoryID, $category, true);
        }

        // add parent categories
        $parentCategories = \array_reverse($parentCategories);
        foreach ($parentCategories as $parentCategory) {
            PageLocationManager::getInstance()->addParentLocation('com.uz.poi.CategoryPoiList', $parentCategory->categoryID, $parentCategory);
        }
    }
}
