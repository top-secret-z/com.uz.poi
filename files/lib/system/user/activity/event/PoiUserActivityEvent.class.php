<?php
namespace poi\system\user\activity\event;
use poi\data\poi\ViewablePoiList;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @inheritDoc
	 */
	public function prepare(array $events) {
		$objectIDs = [];
		foreach ($events as $event) {
			$objectIDs[] = $event->objectID;
		}
		
		// fetch pois
		$poiList = new ViewablePoiList();
		$poiList->setObjectIDs($objectIDs);
		$poiList->readObjects();
		$pois = $poiList->getObjects();
		
		// set message
		foreach ($events as $event) {
			if (isset($pois[$event->objectID])) {
				if (!$pois[$event->objectID]->canRead()) {
					continue;
				}
				$event->setIsAccessible();
				
				// title
				$text = WCF::getLanguage()->getDynamicVariable('poi.poi.recentActivity.poi', ['poi' => $pois[$event->objectID]]);
				$event->setTitle($text);
				
				// description
				$event->setDescription($pois[$event->objectID]->getExcerpt());
			}
			else {
				$event->setIsOrphaned();
			}
		}
	}
}
