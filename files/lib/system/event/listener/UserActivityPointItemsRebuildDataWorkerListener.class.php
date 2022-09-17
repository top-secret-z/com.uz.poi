<?php
namespace poi\system\event\listener;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Updates the user activity point items counter for Pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class UserActivityPointItemsRebuildDataWorkerListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		$objectType = UserActivityPointHandler::getInstance()->getObjectTypeByName('com.uz.poi.activityPointEvent.poi');
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('user_activity_point.objectTypeID = ?', [$objectType->objectTypeID]);
		$conditionBuilder->add('user_activity_point.userID IN (?)', [$eventObj->getObjectList()->getObjectIDs()]);
		
		$sql = "UPDATE		wcf" . WCF_N . "_user_activity_point user_activity_point
				LEFT JOIN	wcf" . WCF_N . "_user user_table
				ON		(user_table.userID = user_activity_point.userID)
				SET		user_activity_point.items = user_table.poiPois,
						user_activity_point.activityPoints = user_activity_point.items * ?
				" . $conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge(
			[$objectType->points],
			$conditionBuilder->getParameters()
		));
	}
}
