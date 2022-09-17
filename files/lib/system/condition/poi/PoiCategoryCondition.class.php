<?php
namespace poi\system\condition\poi;
use poi\data\poi\PoiList;
use wcf\data\DatabaseObjectList;
use wcf\system\condition\AbstractMultiCategoryCondition;
use wcf\system\condition\IObjectListCondition;

/**
 * Condition implementation for the category a poi belongs to.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiCategoryCondition extends AbstractMultiCategoryCondition implements IObjectListCondition {
	/**
	 * @inheritDoc
	 */
	public $objectType = 'com.uz.poi.category';
	
	/**
	 * @inheritDoc
	 */
	protected $fieldName = 'poiCategoryIDs';
	
	/**
	 * @inheritDoc
	 */
	protected $label = 'poi.poi.category';
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof PoiList)) {
			throw new \InvalidArgumentException("Object list is no instance of '".PoiList::class."', instance of '".get_class($objectList)."' given.");
		}
		
		$objectList->getConditionBuilder()->add('poi.categoryID IN (?)', [$conditionData[$this->fieldName]]);
	}
}
