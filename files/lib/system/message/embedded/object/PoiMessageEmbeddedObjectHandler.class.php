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
namespace poi\system\message\embedded\object;

use poi\data\poi\AccessiblePoiList;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\AbstractMessageEmbeddedObjectHandler;
use wcf\util\ArrayUtil;

/**
 * Message embedded object handler implementation for pois.
 */
class PoiMessageEmbeddedObjectHandler extends AbstractMessageEmbeddedObjectHandler
{
    /**
     * @inheritDoc
     */
    public function loadObjects(array $objectIDs)
    {
        $poiList = new AccessiblePoiList();
        $poiList->getConditionBuilder()->add('poi.poiID IN (?)', [$objectIDs]);
        $poiList->readObjects();

        return $poiList->getObjects();
    }

    /**
     * @inheritDoc
     */
    public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData)
    {
        if (!empty($embeddedData['poi'])) {
            $parsedPoiIDs = [];
            foreach ($embeddedData['poi'] as $attributes) {
                if (!empty($attributes[0])) {
                    $parsedPoiIDs = \array_merge($parsedPoiIDs, ArrayUtil::toIntegerArray(\explode(',', $attributes[0])));
                }
            }

            $poiIDs = \array_unique(\array_filter($parsedPoiIDs));
            if (!empty($poiIDs)) {
                $poiList = new AccessiblePoiList();
                $poiList->getConditionBuilder()->add('poi.poiID IN (?)', [$poiIDs]);
                $poiList->readObjectIDs();

                return $poiList->getObjectIDs();
            }
        }

        return [];
    }
}
