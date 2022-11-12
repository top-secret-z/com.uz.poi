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
namespace poi\system\search;

use poi\data\category\PoiCategory;
use poi\data\category\PoiCategoryNodeTree;
use poi\data\poi\SearchResultPoiList;
use wcf\data\search\ISearchResultObject;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\search\AbstractSearchProvider;
use wcf\system\WCF;

/**
 * An implementation of ISearchableObjectType for searching in pois.
 */
final class PoiSearch extends AbstractSearchProvider
{
    /**
     * data
     */
    private $poiCategoryID = 0;

    private $messageCache = [];

    /**
     * @inheritDoc
     */
    public function cacheObjects(array $objectIDs, ?array $additionalData = null): void
    {
        $poiList = new SearchResultPoiList();
        $poiList->setObjectIDs($objectIDs);
        $poiList->readObjects();
        foreach ($poiList->getObjects() as $poi) {
            $this->messageCache[$poi->poiID] = $poi;
        }
    }

    /**
     * @inheritDoc
     */
    public function getObject(int $objectID): ?ISearchResultObject
    {
        return $this->messageCache[$objectID] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getTableName(): string
    {
        return 'poi' . WCF_N . '_poi';
    }

    /**
     * @inheritDoc
     */
    public function getIDFieldName(): string
    {
        return $this->getTableName() . '.poiID';
    }

    /**
     * @inheritDoc
     */
    public function getConditionBuilder(array $parameters): ?PreparedStatementConditionBuilder
    {
        $this->readParameters($parameters);

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $this->initCategoryCondition($conditionBuilder);
        $this->initMiscConditions($conditionBuilder);
        $this->initLanguageCondition($conditionBuilder);

        return $conditionBuilder;
    }

    private function initCategoryCondition(PreparedStatementConditionBuilder $conditionBuilder): void
    {
        $selectedCategoryIDs = $this->getPoiCategoryIDs($this->poiCategoryID);
        $accessibleCategoryIDs = PoiCategory::getAccessibleCategoryIDs();
        if (!empty($selectedCategoryIDs)) {
            $selectedCategoryIDs = \array_intersect($selectedCategoryIDs, $accessibleCategoryIDs);
        } else {
            $selectedCategoryIDs = $accessibleCategoryIDs;
        }

        if (empty($selectedCategoryIDs)) {
            $conditionBuilder->add('1=0');
        } else {
            $conditionBuilder->add($this->getTableName() . '.categoryID IN (?)', [$selectedCategoryIDs]);
        }
    }

    private function getPoiCategoryIDs(int $categoryID): array
    {
        $categoryIDs = [];

        if ($categoryID) {
            if (($category = PoiCategory::getCategory($categoryID)) !== null) {
                $categoryIDs[] = $categoryID;
                foreach ($category->getAllChildCategories() as $childCategory) {
                    $categoryIDs[] = $childCategory->categoryID;
                }
            }
        }

        return $categoryIDs;
    }

    private function initMiscConditions(PreparedStatementConditionBuilder $conditionBuilder): void
    {
        $conditionBuilder->add($this->getTableName() . '.isDisabled = 0');
        $conditionBuilder->add($this->getTableName() . '.isDeleted = 0');
    }

    private function initLanguageCondition(PreparedStatementConditionBuilder $conditionBuilder): void
    {
        if (LanguageFactory::getInstance()->multilingualismEnabled() && \count(WCF::getUser()->getLanguageIDs())) {
            $conditionBuilder->add(
                '(' . $this->getTableName() . '.languageID IN (?) OR ' . $this->getTableName() . '.languageID IS NULL)',
                [WCF::getUser()->getLanguageIDs()]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getFormTemplateName(): string
    {
        return 'searchPoi';
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalData(): ?array
    {
        return ['poiCategoryID' => $this->poiCategoryID];
    }

    /**
     * @inheritDoc
     */
    public function assignVariables(): void
    {
        WCF::getTPL()->assign([
            'poiCategoryList' => (new PoiCategoryNodeTree('com.uz.poi.category'))->getIterator(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('user.poi.canViewPoi');
    }

    private function readParameters(array $parameters): void
    {
        if (!empty($parameters['poiCategoryID'])) {
            $this->poiCategoryID = \intval($parameters['poiCategoryID']);
        }
    }
}
