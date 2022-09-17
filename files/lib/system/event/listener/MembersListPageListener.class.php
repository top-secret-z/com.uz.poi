<?php
namespace poi\system\event\listener;
use wcf\system\event\listener\IParameterizedEventListener;

/**
 * Adds 'poiPois' sort field to members list.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class MembersListPageListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		$eventObj->validSortFields[] = 'poiPois';
	}
}
