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

use wcf\data\category\CategoryList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\page\AbstractPage;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;

use const SORT_ASC;

/**
 * Shows the marker list page.
 */
class MarkerListPage extends AbstractPage
{
    /**
     * marker data
     */
    protected $markers = [];

    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'poi.acp.menu.link.poi.marker.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.poi.canManageCategory'];

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // assigned markers
        $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.category', 'com.uz.poi.category');
        $categoryList = new CategoryList();
        $categoryList->getConditionBuilder()->add('category.objectTypeID = ?', [$objectTypeID]);
        $categoryList->readObjects();

        $categoryToMarker = [];
        foreach ($categoryList->getObjects() as $category) {
            if (isset($category->additionalData['marker']) && !empty($category->additionalData['marker'])) {
                if (isset($categoryToMarker[$category->additionalData['marker']])) {
                    $categoryToMarker[$category->additionalData['marker']] = $categoryToMarker[$category->additionalData['marker']] . ', ' . $category->getTitle();
                } else {
                    $categoryToMarker[$category->additionalData['marker']] = $category->getTitle();
                }
            }
        }

        // available markers
        $files = DirectoryUtil::getInstance(POI_DIR . 'images/marker/')->getFiles(SORT_ASC);
        $path = WCF::getPath('poi') . 'images/marker/';
        foreach ($files as $file) {
            if (\is_dir($file)) {
                continue;
            }
            if (\strpos($file, '/marker/search/')) {
                continue;
            }

            $name = \basename($file);
            $link = '<img src="' . $path . $name . '" alt="' . $name . '">';
            $temp = \getimagesize($file);
            $size = $temp[0] . ' x ' . $temp[1];

            $used = 0;
            $categories = '';

            if (\array_key_exists($name, $categoryToMarker)) {
                $used = 1;
                $categories = $categoryToMarker[$name];
            }

            $this->markers[] = [
                'name' => $name,
                'link' => $link,
                'used' => $used,
                'size' => $size,
                'categories' => $categories,
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'markers' => $this->markers,
        ]);
    }
}
