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
 */use wcf\data\category\CategoryEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\box\BoxHandler;
use wcf\system\WCF;

/**
 * @author        2017-2022 Zaydowicz
 */

// add default category
$sql = "SELECT    objectTypeID
        FROM    wcf" . WCF_N . "_object_type
        WHERE    definitionID = ? AND objectType = ?";
$statement = WCF::getDB()->prepareStatement($sql, 1);
$statement->execute([
    ObjectTypeCache::getInstance()->getDefinitionByName('com.woltlab.wcf.category')->definitionID,
    'com.uz.poi.category',
]);

CategoryEditor::create([
    'objectTypeID' => $statement->fetchColumn(),
    'title' => 'Default Category',
    'time' => TIME_NOW,
    'additionalData' => \serialize(['marker' => 'marker_red.png']),
]);

// assign box 'com.woltlab.wcf.UsersOnline' to PoiListPage and MapPage
BoxHandler::getInstance()->addBoxToPageAssignments('com.woltlab.wcf.UsersOnline', ['com.uz.poi.PoiList']);
BoxHandler::getInstance()->addBoxToPageAssignments('com.woltlab.wcf.UsersOnline', ['com.uz.poi.Map']);
