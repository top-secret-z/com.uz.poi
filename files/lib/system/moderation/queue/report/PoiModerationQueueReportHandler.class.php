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
namespace poi\system\moderation\queue\report;

use poi\data\poi\ViewablePoi;
use poi\system\moderation\queue\AbstractPoiModerationQueueHandler;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\report\IModerationQueueReportHandler;
use wcf\system\WCF;

/**
 * An implementation of IModerationQueueReportHandler for pois.
 */
class PoiModerationQueueReportHandler extends AbstractPoiModerationQueueHandler implements IModerationQueueReportHandler
{
    /**
     * @inheritDoc
     */
    protected $definitionName = 'com.woltlab.wcf.moderation.report';

    /**
     * @inheritDoc
     */
    protected $objectType = 'com.uz.poi.poi';

    /**
     * @inheritDoc
     */
    public function canReport($objectID)
    {
        if (!$this->isValid($objectID)) {
            return false;
        }

        if (!$this->getPoi($objectID)->canRead()) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getReportedContent(ViewableModerationQueue $queue)
    {
        return WCF::getTPL()->fetch('moderationPoi', 'poi', [
            'poi' => new ViewablePoi($queue->getAffectedObject()),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getReportedObject($objectID)
    {
        if ($this->isValid($objectID)) {
            return $this->getPoi($objectID);
        }

        return null;
    }
}
