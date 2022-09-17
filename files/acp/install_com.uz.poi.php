<?php
use wcf\data\category\CategoryEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\box\BoxHandler;
use wcf\system\WCF;

/**
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */

// add default category
$sql = "SELECT	objectTypeID
		FROM	wcf".WCF_N."_object_type
		WHERE	definitionID = ? AND objectType = ?";
$statement = WCF::getDB()->prepareStatement($sql, 1);
$statement->execute([
		ObjectTypeCache::getInstance()->getDefinitionByName('com.woltlab.wcf.category')->definitionID,
		'com.uz.poi.category'
]);

CategoryEditor::create([
		'objectTypeID' => $statement->fetchColumn(),
		'title' => 'Default Category',
		'time' => TIME_NOW,
		'additionalData' => serialize(['marker' => 'marker_red.png'])
]);

// assign box 'com.woltlab.wcf.UsersOnline' to PoiListPage and MapPage
BoxHandler::getInstance()->addBoxToPageAssignments('com.woltlab.wcf.UsersOnline', ['com.uz.poi.PoiList']);
BoxHandler::getInstance()->addBoxToPageAssignments('com.woltlab.wcf.UsersOnline', ['com.uz.poi.Map']);