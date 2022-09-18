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
namespace poi\system\event\listener;

use poi\data\poi\AccessiblePoiList;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\event\listener\AbstractHtmlInputNodeProcessorListener;
use wcf\system\request\LinkHandler;

/**
 * Parses URLs of poi entries.
 */
class HtmlInputNodeProcessorListener extends AbstractHtmlInputNodeProcessorListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        // replace links
        if (BBCodeHandler::getInstance()->isAvailableBBCode('poi')) {
            $regex = $this->getRegexFromLink(LinkHandler::getInstance()->getLink('Poi', [
                'application' => 'poi',
                'forceFrontend' => true,
            ]));
            $poiIDs = $this->getObjectIDs($eventObj, $regex);

            if (!empty($poiIDs)) {
                $poiList = new AccessiblePoiList();
                $poiList->getConditionBuilder()->add('poi.poiID IN (?)', [\array_unique($poiIDs)]);
                $poiList->readObjects();

                $this->replaceLinksWithBBCode($eventObj, $regex, $poiList->getObjects(), 'poi');
            }
        }
    }
}
