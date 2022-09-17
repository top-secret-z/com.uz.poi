<?php
namespace poi\data\poi;
use poi\data\category\PoiCategory;
use wcf\system\exception\SystemException;

/**
 * Represents a list of pois in specific categories.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class CategoryPoiList extends AccessiblePoiList {
	/**
	 * @inheritDoc
	 */
	protected $applyCategoryFilter = false;
	
	/**
	 * Creates a new CategoryPoiList object.
	 */
	public function __construct($categoryID, $includeChildCategories = false) {
		parent::__construct();
		
		$categoryIDs = [$categoryID];
		if ($includeChildCategories) {
			$category = PoiCategory::getCategory($categoryID);
			if ($category === null) {
				throw new SystemException("invalid category id '".$categoryID."' given");
			}
			foreach ($category->getAllChildCategories() as $category) {
				if ($category->isAccessible()) {
					$categoryIDs[] = $category->categoryID;
				}
			}
		}
		
		$this->getConditionBuilder()->add('poi.categoryID IN (?)', [$categoryIDs]);
	}
}
