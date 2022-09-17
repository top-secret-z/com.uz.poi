<?php
namespace poi\system\category;
use poi\data\poi\PoiAction;
use poi\data\poi\PoiList;
use wcf\data\category\CategoryEditor;
use wcf\system\category\AbstractCategoryType;
use wcf\system\WCF;

/**
 * Category type implementation for poi categories.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiCategoryType extends AbstractCategoryType {
	/**
	 * @inheritDoc
	 */
	protected $langVarPrefix = 'poi.category';
	
	/**
	 * @inheritDoc
	 */
	protected $forceDescription = false;
	
	/**
	 * @inheritDoc
	 */
	protected $maximumNestingLevel = 3;
	
	/**
	 * @inheritDoc
	 */
	protected $objectTypes = ['com.woltlab.wcf.acl' => 'com.uz.poi.category'];
	
	/**
	 * @inheritDoc
	 */
	public function afterDeletion(CategoryEditor $categoryEditor) {
		// delete pois with no categories
		$poiList = new PoiList();
		$poiList->getConditionBuilder()->add("poi.categoryID IS NULL");
		$poiList->readObjects();
		
		if (count($poiList)) {
			$poiAction = new PoiAction($poiList->getObjects(), 'delete');
			$poiAction->executeAction();
		}
		
		parent::afterDeletion($categoryEditor);
	}
	
	/**
	 * @inheritDoc
	 */
	public function canAddCategory() {
		return $this->canEditCategory();
	}
	
	/**
	 * @inheritDoc
	 */
	public function canDeleteCategory() {
		return $this->canEditCategory();
	}
	
	/**
	 * @inheritDoc
	 */
	public function canEditCategory() {
		return WCF::getSession()->getPermission('admin.poi.canManageCategory');
	}
}
