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
namespace poi\data\category;

use wcf\data\category\Category;
use wcf\system\category\CategoryHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\SingletonFactory;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Manages the poi category cache.
 */
class PoiCategoryCache extends SingletonFactory
{
    /**
     * poi data
     */
    protected $unreadPois;

    protected $pois;

    protected $poisMap;

    /**
     * Returns the number of pois in the category.
     */
    public function getPois($categoryID)
    {
        if ($this->pois === null) {
            $this->initPois();
        }

        if (isset($this->pois[$categoryID])) {
            return $this->pois[$categoryID];
        }

        return 0;
    }

    /**
     * Calculates the number of pois.
     */
    protected function initPois()
    {
        $this->pois = [];

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('poi.isDisabled = ?', [0]);
        $conditionBuilder->add('poi.isDeleted = ?', [0]);

        // apply language filter
        if (POI_ENABLE_MULTILINGUALISM && LanguageFactory::getInstance()->multilingualismEnabled() && \count(WCF::getUser()->getLanguageIDs())) {
            $conditionBuilder->add('(poi.languageID IN (?) OR poi.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
        }

        $sql = "SELECT        COUNT(*) AS count, categoryID
                FROM        poi" . WCF_N . "_poi poi
                " . $conditionBuilder . "
                GROUP BY    poi.categoryID";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditionBuilder->getParameters());
        $pois = $statement->fetchMap('categoryID', 'count');

        $categoryToParent = [];
        foreach (CategoryHandler::getInstance()->getCategories(PoiCategory::OBJECT_TYPE_NAME) as $category) {
            if (!isset($categoryToParent[$category->parentCategoryID])) {
                $categoryToParent[$category->parentCategoryID] = [];
            }
            $categoryToParent[$category->parentCategoryID][] = $category->categoryID;
        }

        $result = [];
        $this->countPois($categoryToParent, $pois, 0, $result);
        $this->pois = $result;
    }

    /**
     * Returns the pois in this category and its children.
     */
    protected function countPois(array &$categoryToParent, array &$pois, $categoryID, array &$result)
    {
        $count = (isset($pois[$categoryID])) ? $pois[$categoryID] : 0;

        if (isset($categoryToParent[$categoryID])) {
            foreach ($categoryToParent[$categoryID] as $childCategoryID) {
                if (PoiCategory::getCategory($childCategoryID)->getPermission('canViewCategory')) {
                    $count += $this->countPois($categoryToParent, $pois, $childCategoryID, $result);
                }
            }
        }

        if ($categoryID) {
            $result[$categoryID] = $count;
        }

        return $count;
    }

    /**
     * Returns the number of unread pois in the category with the given id.
     */
    public function getUnreadPois($categoryID)
    {
        if ($this->unreadPois === null) {
            $this->initUnreadPois();
        }

        if (isset($this->unreadPois[$categoryID])) {
            return $this->unreadPois[$categoryID];
        }

        return 0;
    }

    /**
     * Gets the number of unread pois.
     */
    protected function initUnreadPois()
    {
        $this->unreadPois = [];

        if (WCF::getUser()->userID) {
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('poi.lastChangeTime > ?', [VisitTracker::getInstance()->getVisitTime('com.uz.poi.poi')]);
            $conditionBuilder->add('poi.isDisabled = ?', [0]);
            $conditionBuilder->add('poi.isDeleted = ?', [0]);

            // apply language filter
            if (POI_ENABLE_MULTILINGUALISM && LanguageFactory::getInstance()->multilingualismEnabled() && \count(WCF::getUser()->getLanguageIDs())) {
                $conditionBuilder->add('(poi.languageID IN (?) OR poi.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
            }

            $conditionBuilder->add('tracked_visit.visitTime IS NULL');

            $sql = "SELECT        COUNT(*) AS count, poi.categoryID
                    FROM        poi" . WCF_N . "_poi poi
                    LEFT JOIN    wcf" . WCF_N . "_tracked_visit tracked_visit
                    ON        (tracked_visit.objectTypeID = " . VisitTracker::getInstance()->getObjectTypeID('com.uz.poi.poi') . " AND tracked_visit.objectID = poi.poiID AND tracked_visit.userID = " . WCF::getUser()->userID . ")
                    " . $conditionBuilder . "
                    GROUP BY    poi.categoryID";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditionBuilder->getParameters());
            $unreadPois = $statement->fetchMap('categoryID', 'count');

            $categoryToParent = [];
            foreach (CategoryHandler::getInstance()->getCategories(PoiCategory::OBJECT_TYPE_NAME) as $category) {
                if (!isset($categoryToParent[$category->parentCategoryID])) {
                    $categoryToParent[$category->parentCategoryID] = [];
                }
                $categoryToParent[$category->parentCategoryID][] = $category->categoryID;
            }

            $result = [];
            $this->countPois($categoryToParent, $unreadPois, 0, $result);
            $this->unreadPois = $result;
        }
    }

    /**
     * Returns the number of pois in the category without children for map.
     */
    public function getPoisMap($categoryID)
    {
        if ($this->poisMap === null) {
            $this->initPoisMap();
        }

        if (isset($this->poisMap[$categoryID])) {
            return $this->poisMap[$categoryID];
        }

        return 0;
    }

    /**
     * Calculates the number of pois for map.
     */
    protected function initPoisMap()
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('poi.isDisabled = ?', [0]);
        $conditionBuilder->add('poi.isDeleted = ?', [0]);

        // apply language filter
        if (POI_ENABLE_MULTILINGUALISM && LanguageFactory::getInstance()->multilingualismEnabled() && \count(WCF::getUser()->getLanguageIDs())) {
            $conditionBuilder->add('(poi.languageID IN (?) OR poi.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
        }

        $sql = "SELECT        COUNT(*) AS count, categoryID
                FROM        poi" . WCF_N . "_poi poi
                " . $conditionBuilder . "
                GROUP BY    poi.categoryID";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditionBuilder->getParameters());
        $pois = $statement->fetchMap('categoryID', 'count');

        $this->poisMap = $pois;
    }
}
