<?php
namespace poi\system\event\listener;
use wcf\system\event\listener\IParameterizedEventListener;

/**
 * Adds support for sorting by Poi count to the user list box controller.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class UserListBoxControllerListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		switch ($eventName) {
			case '__construct':
				$eventObj->validSortFields[] = 'poiPois';
				break;
			
			case 'readObjects':
				if ($eventObj->sortField === 'poiPois') {
					$eventObj->objectList->getConditionBuilder()->add('user_table.poiPois > 0');
				}
				break;
			
			default:
				throw new \InvalidArgumentException("Cannot handle event '{$eventName}'");
		}
	}
}
