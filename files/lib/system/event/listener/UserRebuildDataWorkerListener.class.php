<?php
namespace poi\system\event\listener;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\WCF;

/**
 * Updates users' poi counter.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class UserRebuildDataWorkerListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		$userIDs = [];
		foreach ($eventObj->getObjectList() as $user) {
			$userIDs[] = $user->userID;
		}
		
		if (!empty($userIDs)) {
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('user_table.userID IN (?)', [$userIDs]);
			$sql = "UPDATE	wcf".WCF_N."_user user_table
					SET	poiPois = (
						SELECT	COUNT(*)
						FROM	poi".WCF_N."_poi poi
						WHERE	poi.userID = user_table.userID AND poi.isDisabled = 0
					)
					".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
		}
	}
}
