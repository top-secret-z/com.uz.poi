<?php
namespace poi\system\page\handler;
use poi\data\category\PoiCategory;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\page\handler\AbstractMenuPageHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Menu page handler for the watched pois page.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class WatchedPoiListPageHandler extends AbstractMenuPageHandler {
	/**
	 * number of unread pois
	 */
	protected $notifications;
	
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		$count = 0;
		if (WCF::getUser()->userID) {
			$data = UserStorageHandler::getInstance()->getField('poiWatchedPois');
			
			// cache does not exist or is outdated
			if ($data === null) {
				$categoryIDs = PoiCategory::getAccessibleCategoryIDs();
				if (!empty($categoryIDs)) {
					$objectTypeID = UserObjectWatchHandler::getInstance()->getObjectTypeID('com.uz.poi.poi');
					
					$conditionBuilder = new PreparedStatementConditionBuilder();
					$conditionBuilder->add('user_object_watch.objectTypeID = ?', [$objectTypeID]);
					$conditionBuilder->add('user_object_watch.userID = ?', [WCF::getUser()->userID]);
					$conditionBuilder->add('poi.categoryID IN (?)', [$categoryIDs]);
					$conditionBuilder->add('poi.isDeleted = 0 AND poi.isDisabled = 0');
					
					$sql = "SELECT		COUNT(*)
							FROM		wcf".WCF_N."_user_object_watch user_object_watch
							LEFT JOIN	poi".WCF_N."_poi poi
							ON		(poi.poiID = user_object_watch.objectID)
							".$conditionBuilder;
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute($conditionBuilder->getParameters());
					$count = $statement->fetchSingleColumn();
				}
				
				// update storage data
				UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'poiWatchedPois', $count);
			}
			else {
				$count = $data;
			}
		}
		
		return ($count != 0);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getOutstandingItemCount($objectID = null) {
		if ($this->notifications === null) {
			$this->notifications = 0;
			
			if (WCF::getUser()->userID) {
				$data = UserStorageHandler::getInstance()->getField('poiUnreadWatchedPois');
				
				// cache does not exist or is outdated
				if ($data === null) {
					$categoryIDs = PoiCategory::getAccessibleCategoryIDs();
					if (!empty($categoryIDs)) {
						$objectTypeID = UserObjectWatchHandler::getInstance()->getObjectTypeID('com.uz.poi.poi');
						
						$conditionBuilder = new PreparedStatementConditionBuilder();
						$conditionBuilder->add('user_object_watch.objectTypeID = ?', [$objectTypeID]);
						$conditionBuilder->add('user_object_watch.userID = ?', [WCF::getUser()->userID]);
						$conditionBuilder->add('poi.lastChangeTime > ?', [VisitTracker::getInstance()->getVisitTime('com.uz.poi.poi')]);
						$conditionBuilder->add('poi.categoryID IN (?)', [$categoryIDs]);
						$conditionBuilder->add('poi.isDeleted = 0 AND poi.isDisabled = 0');
						$conditionBuilder->add('(poi.lastChangeTime > tracked_poi_visit.visitTime OR tracked_poi_visit.visitTime IS NULL)');
						
						$sql = "SELECT		COUNT(*)
								FROM		wcf".WCF_N."_user_object_watch user_object_watch
								LEFT JOIN	poi".WCF_N."_poi poi
								ON		(poi.poiID = user_object_watch.objectID)
								LEFT JOIN	wcf".WCF_N."_tracked_visit tracked_poi_visit
								ON		(tracked_poi_visit.objectTypeID = ".VisitTracker::getInstance()->getObjectTypeID('com.uz.poi.poi')." AND tracked_poi_visit.objectID = poi.poiID AND tracked_poi_visit.userID = ".WCF::getUser()->userID.")
								".$conditionBuilder;
						$statement = WCF::getDB()->prepareStatement($sql);
						$statement->execute($conditionBuilder->getParameters());
						$this->notifications = $statement->fetchSingleColumn();
					}
					
					// update storage data
					UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'poiUnreadWatchedPois', $this->notifications);
				}
				else {
					$this->notifications = $data;
				}
			}
		}
		
		return $this->notifications;
	}
}
