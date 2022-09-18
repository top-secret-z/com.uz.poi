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
namespace poi\system\sitemap\object;

use poi\data\category\PoiCategory;
use wcf\data\category\CategoryList;
use wcf\data\DatabaseObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\sitemap\object\AbstractSitemapObjectObjectType;

/**
 * Category sitemap implementation.
 */
class CategorySitemapObject extends AbstractSitemapObjectObjectType
{
    /**
     * @inheritDoc
     */
    public function getObjectClass()
    {
        return PoiCategory::class;
    }

    /**
     * @inheritDoc
     */
    public function getObjectList()
    {
        $categoryList = new CategoryList();
        $categoryList->decoratorClassName = $this->getObjectClass();
        $categoryList->getConditionBuilder()->add('objectTypeID = ?', [ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.category', 'com.uz.poi.category')]);

        return $categoryList;
    }

    /**
     * @inheritDoc
     */
    public function canView(DatabaseObject $object)
    {
        return $object->isAccessible();
    }
}
