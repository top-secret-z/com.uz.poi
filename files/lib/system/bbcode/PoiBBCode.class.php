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
namespace poi\system\bbcode;

use wcf\system\bbcode\AbstractBBCode;
use wcf\system\bbcode\BBCodeParser;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Parses the [poi] bbcode tag.
 */
class PoiBBCode extends AbstractBBCode
{
    /**
     * @inheritDoc
     */
    public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser)
    {
        $poiIDs = [];
        if (isset($openingTag['attributes'][0])) {
            $poiIDs = \array_unique(ArrayUtil::toIntegerArray(\explode(',', $openingTag['attributes'][0])));
        }

        $pois = [];
        foreach ($poiIDs as $poiID) {
            $poi = MessageEmbeddedObjectManager::getInstance()->getObject('com.uz.poi.poi', $poiID);
            if ($poi !== null && $poi->canRead()) {
                $pois[] = $poi;
            }
        }

        if (!empty($pois)) {
            if ($parser->getOutputType() == 'text/html') {
                return WCF::getTPL()->fetch('poiBBCode', 'poi', [
                    'pois' => $pois,
                    'titleHash' => \substr(StringUtil::getRandomID(), 0, 8),
                ], true);
            }

            $result = '';
            foreach ($pois as $poi) {
                if (!empty($result)) {
                    $result .= ' ';
                }
                $result .= StringUtil::getAnchorTag(LinkHandler::getInstance()->getLink('Poi', [
                    'application' => 'poi',
                    'object' => $poi,
                ]));
            }

            return $result;
        }

        if (!empty($poiIDs)) {
            $result = '';
            foreach ($poiIDs as $poiID) {
                if ($poiID) {
                    if (!empty($result)) {
                        $result .= ' ';
                    }
                    $result .= StringUtil::getAnchorTag(LinkHandler::getInstance()->getLink('Poi', [
                        'application' => 'poi',
                        'id' => $poiID,
                    ]));
                }
            }

            return $result;
        }
    }
}
