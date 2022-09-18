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
namespace poi\system\worker;

use poi\data\poi\PoiEditor;
use poi\data\poi\PoiList;
use wcf\system\worker\AbstractRebuildDataWorker;

/**
 * Worker implementation for updating poi visits.
 */
class PoiVisitRebuildDataWorker extends AbstractRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $objectListClassName = PoiList::class;

    /**
     * @inheritDoc
     */
    protected $limit = 50;

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlOrderBy = 'poi.poiID';
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();

        if (!\count($this->objectList)) {
            return;
        }

        foreach ($this->objectList as $poi) {
            $editor = new PoiEditor($poi);
            $visits = $poi->getVisits();

            $editor->update(['visits' => $visits]);
        }
    }
}
