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
namespace poi\system\poi;

use poi\data\poi\Poi;
use poi\data\poi\PoiList;
use wcf\system\SingletonFactory;

/**
 * Caches poi objects for poi-related user notifications.
 */
class PoiDataHandler extends SingletonFactory
{
    /**
     * list of cached poi ids
     */
    protected $poiIDs = [];

    /**
     * list of cached poi objects
     */
    protected $pois = [];

    /**
     * Caches an poi id.
     */
    public function cachePoiID($poiID)
    {
        if (!\in_array($poiID, $this->poiIDs)) {
            $this->poiIDs[] = $poiID;
        }
    }

    /**
     * Returns the poi with the given id.
     */
    public function getPoi($poiID)
    {
        if (!empty($this->poiIDs)) {
            $this->poiIDs = \array_diff($this->poiIDs, \array_keys($this->pois));

            if (!empty($this->poiIDs)) {
                $poiList = new PoiList();
                $poiList->setObjectIDs($this->poiIDs);
                $poiList->readObjects();
                $this->pois += $poiList->getObjects();
                $this->poiIDs = [];
            }
        }

        if (isset($this->pois[$poiID])) {
            return $this->pois[$poiID];
        }

        return null;
    }
}
