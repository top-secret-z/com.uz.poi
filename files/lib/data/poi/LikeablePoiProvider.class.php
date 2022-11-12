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
namespace poi\data\poi;

use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\like\object\ILikeObject;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\WCF;

/**
 * Object type provider for pois.
 */
class LikeablePoiProvider extends PoiProvider implements ILikeObjectTypeProvider, IViewableLikeProvider
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = LikeablePoi::class;

    /**
     * @inheritDoc
     */
    public function checkPermissions(ILikeObject $object)
    {
        return $object->poiID && $object->canRead();
    }

    /**
     * @inheritDoc
     */
    public function prepare(array $likes)
    {
        $poiIDs = [];
        foreach ($likes as $like) {
            $poiIDs[] = $like->objectID;
        }

        // fetch pois
        $poiList = new ViewablePoiList();
        $poiList->setObjectIDs($poiIDs);
        $poiList->readObjects();
        $pois = $poiList->getObjects();

        // set message
        foreach ($likes as $like) {
            if (isset($pois[$like->objectID])) {
                $poi = $pois[$like->objectID];

                // check permissions
                if (!$poi->canRead()) {
                    continue;
                }
                $like->setIsAccessible();

                // short output
                $text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.uz.poi.likeablePoi', [
                    'poi' => $poi,
                    'like' => $like,
                ]);
                $like->setTitle($text);

                $like->setDescription($poi->getExcerpt());
            }
        }
    }
}
