<?php
namespace poi\system\sitemap\object;
use poi\data\category\PoiCategory;
use wcf\data\category\CategoryList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObject;
use wcf\system\sitemap\object\AbstractSitemapObjectObjectType;

/**
 * Category sitemap implementation.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class CategorySitemapObject extends AbstractSitemapObjectObjectType {
	/**
	 * @inheritDoc
	 */
	public function getObjectClass() {
		return PoiCategory::class;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectList() {
		$categoryList = new CategoryList();
		$categoryList->decoratorClassName = $this->getObjectClass();
		$categoryList->getConditionBuilder()->add('objectTypeID = ?', [ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.category', 'com.uz.poi.category')]);
		
		return $categoryList;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canView(DatabaseObject $object) {
		return $object->isAccessible();
	}
}
