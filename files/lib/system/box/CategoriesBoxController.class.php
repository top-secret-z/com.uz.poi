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
namespace poi\system\box;

use poi\data\category\PoiCategoryNodeTree;
use poi\page\CategoryPoiListPage;
use poi\page\PoiPage;
use wcf\system\box\AbstractBoxController;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Box for poi categories.
 */
class CategoriesBoxController extends AbstractBoxController
{
    /**
     * @inheritDoc
     */
    protected static $supportedPositions = ['sidebarLeft', 'sidebarRight'];

    /**
     * @inheritDoc
     */
    protected function loadContent()
    {
        // get categories
        $categoryTree = new PoiCategoryNodeTree('com.uz.poi.category');
        $categoryList = $categoryTree->getIterator();
        $categoryList->setMaxDepth(0);

        if (\iterator_count($categoryList)) {
            // get active category
            $activeCategory = null;
            if (RequestHandler::getInstance()->getActiveRequest() !== null) {
                if (RequestHandler::getInstance()->getActiveRequest()->getRequestObject() instanceof CategoryPoiListPage || RequestHandler::getInstance()->getActiveRequest()->getRequestObject() instanceof PoiPage) {
                    if (RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->category !== null) {
                        $activeCategory = RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->category;
                    }
                }
            }

            $this->content = WCF::getTPL()->fetch('boxCategories', 'poi', ['categoryList' => $categoryList, 'activeCategory' => $activeCategory], true);
        }
    }
}
