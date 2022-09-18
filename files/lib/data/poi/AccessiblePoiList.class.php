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

use poi\data\category\PoiCategory;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Represents a list of accessible pois.
 */
class AccessiblePoiList extends ViewablePoiList
{
    protected $applyCategoryFilter = true;

    /**
     * Creates a new AccessiblePoiList object.
     */
    public function __construct()
    {
        parent::__construct();

        // apply category filter
        if ($this->applyCategoryFilter) {
            $accessibleCategoryIDs = PoiCategory::getAccessibleCategoryIDs();
            if (!empty($accessibleCategoryIDs)) {
                $this->getConditionBuilder()->add('poi.categoryID IN (?)', [$accessibleCategoryIDs]);
            } else {
                $this->getConditionBuilder()->add('1=0');
            }
        }

        // apply permission
        if (!WCF::getSession()->getPermission('mod.poi.canModeratePoi')) {
            if (!WCF::getUser()->userID) {
                $this->getConditionBuilder()->add('poi.isDisabled = 0');
            } else {
                $this->getConditionBuilder()->add('(poi.isDisabled = 0 OR poi.userID = ?)', [WCF::getUser()->userID]);
            }
        }
        if (!WCF::getSession()->getPermission('mod.poi.canViewDeletedPoi')) {
            $this->getConditionBuilder()->add('poi.isDeleted = 0');
        }

        // apply language filter
        if (POI_ENABLE_MULTILINGUALISM && LanguageFactory::getInstance()->multilingualismEnabled() && \count(WCF::getUser()->getLanguageIDs())) {
            $this->getConditionBuilder()->add('(poi.languageID IN (?) OR poi.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
        }
    }

    /**
     * @inheritDoc
     */
    public function readObjects()
    {
        if ($this->objectIDs === null) {
            $this->readObjectIDs();
        }

        parent::readObjects();
    }
}
