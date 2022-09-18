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
namespace poi\data\category;

use wcf\data\category\CategoryNode;

/**
 * Represents a poi category node.
 */
class PoiCategoryNode extends CategoryNode
{
    /**
     * poi data
     */
    protected $unreadPois;

    protected $pois;

    protected $poisMap;

    /**
     * @inheritDoc
     */
    protected static $baseClass = PoiCategory::class;

    /**
     * Returns the number of pois in the category and children.
     */
    public function getPois()
    {
        if ($this->pois === null) {
            $this->pois = PoiCategoryCache::getInstance()->getPois($this->categoryID);
        }

        return $this->pois;
    }

    /**
     * Returns the number of unread pois in the category and childre.
     */
    public function getUnreadPois()
    {
        if ($this->unreadPois === null) {
            $this->unreadPois = PoiCategoryCache::getInstance()->getUnreadPois($this->categoryID);
        }

        return $this->unreadPois;
    }

    /**
     * Returns the number of pois in the category for map excl. children.
     */
    public function getPoisMap()
    {
        if ($this->poisMap === null) {
            $this->poisMap = PoiCategoryCache::getInstance()->getPoisMap($this->categoryID);
        }

        return $this->poisMap;
    }
}
