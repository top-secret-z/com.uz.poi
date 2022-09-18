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
namespace poi\system\clipboard\action;

use poi\data\poi\Poi;
use poi\data\poi\PoiAction;
use wcf\data\clipboard\action\ClipboardAction;
use wcf\system\clipboard\action\AbstractClipboardAction;
use wcf\system\WCF;

/**
 * Prepares clipboard editor items for pois.
 */
class PoiClipboardAction extends AbstractClipboardAction
{
    /**
     * @inheritDoc
     */
    protected $actionClassActions = ['delete', 'enable', 'disable', 'restore', 'trash'];

    /**
     * @inheritDoc
     */
    protected $supportedActions = ['delete', 'enable', 'disable', 'restore', 'trash'];

    /**
     * list of active poi objects
     */
    protected $pois = [];

    /**
     * @inheritDoc
     */
    public function execute(array $objects, ClipboardAction $action)
    {
        $this->pois = $objects;

        $item = parent::execute($objects, $action);
        if ($item === null) {
            return null;
        }

        // handle actions
        switch ($action->actionName) {
            case 'trash':
                $item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.com.uz.poi.poi.trash.confirmMessage', [
                    'count' => $item->getCount(),
                ]));
                $item->addInternalData('template', WCF::getTPL()->fetch('poiDeleteReason', 'poi'));
                break;

            case 'delete':
                $item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.com.uz.poi.poi.delete.confirmMessage', [
                    'count' => $item->getCount(),
                ]));
                break;
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function getClassName()
    {
        return PoiAction::class;
    }

    /**
     * @inheritDoc
     */
    public function getTypeName()
    {
        return 'com.uz.poi.poi';
    }

    /**
     * Validates pois valid for disabling / enabling and returns their ids.
     */
    public function validateDisable()
    {
        $poiIDs = [];

        foreach ($this->pois as $poi) {
            if (!$poi->isDisabled && !$poi->isDeleted && WCF::getSession()->getPermission('mod.poi.canModeratePoi')) {
                $poiIDs[] = $poi->poiID;
            }
        }

        return $poiIDs;
    }

    public function validateEnable()
    {
        $poiIDs = [];

        foreach ($this->pois as $poi) {
            if ($poi->isDisabled && WCF::getSession()->getPermission('mod.poi.canModeratePoi')) {
                $poiIDs[] = $poi->poiID;
            }
        }

        return $poiIDs;
    }

    /**
     * Validates pois valid for deleting / trashing / restoring and returns their ids.
     */
    public function validateDelete()
    {
        $poiIDs = [];

        foreach ($this->pois as $poi) {
            if ($poi->isDeleted && WCF::getSession()->getPermission('mod.poi.canDeletePoiCompletely')) {
                $poiIDs[] = $poi->poiID;
            }
        }

        return $poiIDs;
    }

    public function validateTrash()
    {
        $poiIDs = [];

        foreach ($this->pois as $poi) {
            if (!$poi->isDeleted && WCF::getSession()->getPermission('mod.poi.canDeletePoi')) {
                $poiIDs[] = $poi->poiID;
            }
        }

        return $poiIDs;
    }

    public function validateRestore()
    {
        $poiIDs = [];

        foreach ($this->pois as $poi) {
            if ($poi->isDeleted && WCF::getSession()->getPermission('mod.poi.canRestorePoi')) {
                $poiIDs[] = $poi->poiID;
            }
        }

        return $poiIDs;
    }
}
