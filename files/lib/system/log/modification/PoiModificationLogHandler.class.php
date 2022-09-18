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
namespace poi\system\log\modification;

use poi\data\modification\log\ViewablePoiModificationLog;
use poi\data\poi\Poi;
use poi\data\poi\PoiList;
use wcf\system\log\modification\AbstractExtendedModificationLogHandler;

/**
 * Handles poi modification logs.
 */
class PoiModificationLogHandler extends AbstractExtendedModificationLogHandler
{
    /**
     * @inheritDoc
     */
    protected $objectTypeName = 'com.uz.poi.poi';

    /**
     * Adds a log entry for poi delete.
     */
    public function delete(Poi $poi)
    {
        $this->add($poi, 'delete', [
            'time' => $poi->time,
            'subject' => $poi->getSubject(),
        ]);
    }

    /**
     * Adds a log entry for poi disable / enable.
     */
    public function disable(Poi $poi)
    {
        $this->add($poi, 'disable');
    }

    public function enable(Poi $poi)
    {
        $this->add($poi, 'enable');
    }

    /**
     * Adds a log entry for poi edit.
     */
    public function edit(Poi $poi, $reason = '')
    {
        $this->add($poi, 'edit', ['reason' => $reason]);
    }

    /**
     * Adds a log entry for poi trash / restore.
     */
    public function trash(Poi $poi, $reason = '')
    {
        $this->add($poi, 'trash', ['reason' => $reason]);
    }

    public function restore(Poi $poi)
    {
        $this->add($poi, 'restore');
    }

    /**
     * Adds a log entry for poi setAsFeatured / unsetAsFeatured.
     */
    public function setAsFeatured(Poi $poi)
    {
        $this->add($poi, 'setAsFeatured');
    }

    public function unsetAsFeatured(Poi $poi)
    {
        $this->add($poi, 'unsetAsFeatured');
    }

    /**
     * Adds the poi modification log entry.
     */
    public function add(Poi $poi, $action, array $additionalData = [])
    {
        $this->createLog($action, $poi->poiID, null, $additionalData);
    }

    /**
     * @inheritDoc
     */
    public function getAvailableActions()
    {
        return ['delete', 'disable', 'edit', 'enable', 'restore', 'setAsFeatured', 'trash', 'unsetAsFeatured'];
    }

    /**
     * @inheritDoc
     */
    public function processItems(array $items)
    {
        $poiIDs = [];
        foreach ($items as &$item) {
            $poiIDs[] = $item->objectID;

            $item = new ViewablePoiModificationLog($item);
        }
        unset($item);

        if (!empty($poiIDs)) {
            $poiList = new PoiList();
            $poiList->setObjectIDs($poiIDs);
            $poiList->readObjects();
            $pois = $poiList->getObjects();

            foreach ($items as $item) {
                if (isset($pois[$item->objectID])) {
                    $item->setPoi($pois[$item->objectID]);
                }
            }
        }

        return $items;
    }
}
