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
namespace poi\system\moderation\queue\activation;

use poi\data\poi\PoiAction;
use poi\data\poi\ViewablePoi;
use poi\system\moderation\queue\AbstractPoiModerationQueueHandler;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\activation\IModerationQueueActivationHandler;
use wcf\system\WCF;

/**
 * Implementation of IModerationQueueHandler for pois.
 */
class PoiModerationQueueActivationHandler extends AbstractPoiModerationQueueHandler implements IModerationQueueActivationHandler
{
    /**
     * @inheritDoc
     */
    public function enableContent(ModerationQueue $queue)
    {
        if ($this->isValid($queue->objectID) && $this->getPoi($queue->objectID)->isDisabled) {
            $objectAction = new PoiAction([$this->getPoi($queue->objectID)], 'enable');
            $objectAction->executeAction();
        }
    }

    /**
     * @inheritDoc
     */
    public function getDisabledContent(ViewableModerationQueue $queue)
    {
        return WCF::getTPL()->fetch('moderationPoi', 'poi', [
            'poi' => new ViewablePoi($queue->getAffectedObject()),
        ]);
    }
}
