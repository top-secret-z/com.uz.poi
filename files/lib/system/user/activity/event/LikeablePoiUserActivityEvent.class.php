<?php
namespace poi\system\user\activity\event;
use poi\data\poi\ViewablePoiList;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for liked pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class LikeablePoiUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
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
				$text = WCF::getLanguage()->getDynamicVariable('poi.poi.recentActivity.likedPoi', [
						'poi' => $pois[$event->objectID],
						'reactionType' => $event->reactionType
				]);
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
