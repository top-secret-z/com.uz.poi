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
namespace poi\system\user\activity\event;

use poi\data\poi\ViewablePoiList;
use wcf\system\SingletonFactory;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\WCF;

/**
 * User activity event implementation for liked pois.
 */
class LikeablePoiUserActivityEvent extends SingletonFactory implements IUserActivityEvent
{
    /**
     * @inheritDoc
     */
    public function prepare(array $events)
    {
        $objectIDs = [];
        foreach ($events as $event) {
            $objectIDs[] = $event->objectID;
        }

        // fetch pois
        $poiList = new ViewablePoiList();
        $poiList->setObjectIDs($objectIDs);
        $poiList->readObjects();
        $pois = $poiList->getObjects();

        // set message
        foreach ($events as $event) {
            if (isset($pois[$event->objectID])) {
                if (!$pois[$event->objectID]->canRead()) {
                    continue;
                }
                $event->setIsAccessible();

                // title
                $text = WCF::getLanguage()->getDynamicVariable('poi.poi.recentActivity.likedPoi', [
                    'poi' => $pois[$event->objectID],
                    'reactionType' => $event->reactionType,
                ]);
                $event->setTitle($text);

                // description
                $event->setDescription($pois[$event->objectID]->getExcerpt());
            } else {
                $event->setIsOrphaned();
            }
        }
    }
}
