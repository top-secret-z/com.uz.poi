<?php
namespace poi\system;
use poi\data\category\PoiCategory;
use poi\data\poi\Poi;
use poi\page\PoiListPage;
use wcf\system\application\AbstractApplication;
use wcf\system\page\PageLocationManager;

/**
 * Extends the main WCF class by poi specific functions.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class POICore extends AbstractApplication {
	/**
	 * @inheritDoc
	 */
	protected $primaryController = PoiListPage::class;
	
	/**
	 * Sets location data.
	 */
	public function setLocation(array $parentCategories = [], PoiCategory $category = null, Poi $poi = null) {
		// add poi
		if ($poi !== null) {
			PageLocationManager::getInstance()->addParentLocation('com.uz.poi.Poi', $poi->poiID, $poi);
		}
		
		// add category
		if ($category !== null) {
			PageLocationManager::getInstance()->addParentLocation('com.uz.poi.CategoryPoiList', $category->categoryID, $category, true);
		}
		
		// add parent categories
		$parentCategories = array_reverse($parentCategories);
		foreach ($parentCategories as $parentCategory) {
			PageLocationManager::getInstance()->addParentLocation('com.uz.poi.CategoryPoiList', $parentCategory->categoryID, $parentCategory);
		}
	}
}
