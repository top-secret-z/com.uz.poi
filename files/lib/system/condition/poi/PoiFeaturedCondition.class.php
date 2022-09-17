<?php
namespace poi\system\condition\poi;
use poi\data\poi\PoiList;
use wcf\data\DatabaseObjectList;
use wcf\system\condition\AbstractCheckboxCondition;
use wcf\system\condition\IObjectListCondition;

/**
 * Condition implementation for pois to only include featured pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiFeaturedCondition extends AbstractCheckboxCondition implements IObjectListCondition {
	/**
	 * @inheritDoc
	 */
	protected $fieldName = 'poiPoiIsFeatured';
	
	/**
	 * @inheritDoc
	 */
	protected $label = 'poi.poi.condition.isFeatured';
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof PoiList)) {
			throw new \InvalidArgumentException("Object list is no instance of '".PoiList::class."', instance of '".get_class($objectList)."' given.");
		}
		
		$objectList->getConditionBuilder()->add('poi.isFeatured = ?', [1]);
	}
}
