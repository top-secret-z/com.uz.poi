<?php
namespace poi\data\poi;
use poi\data\category\PoiCategory;
use wcf\system\clipboard\ClipboardHandler;

/**
 * Represents a list of deleted pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class DeletedPoiList extends ViewablePoiList {
	/**
	 * Creates a new DeletedPoiList object.
	 */
	public function __construct() {
		parent::__construct();
		
		// categories
		$accessibleCategoryIDs = PoiCategory::getAccessibleCategoryIDs();
		if (!empty($accessibleCategoryIDs)) $this->getConditionBuilder()->add('poi.categoryID IN (?)', [$accessibleCategoryIDs]);
		else $this->getConditionBuilder()->add('1=0');
		
		$this->getConditionBuilder()->add('poi.isDeleted = ?', [1]);
	}
	
	/**
	 * Returns the number of marked items.
	 */
	public function getMarkedItems() {
		return ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.uz.poi.poi'));
	}
}
