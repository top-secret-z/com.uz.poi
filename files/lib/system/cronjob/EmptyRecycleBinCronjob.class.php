<?php
namespace poi\system\cronjob;
use poi\data\poi\PoiAction;
use wcf\data\cronjob\Cronjob;
use wcf\system\cronjob\AbstractCronjob;
use wcf\system\WCF;

/**
 * Deletes thrashed pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class EmptyRecycleBinCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		if (POI_EMPTY_RECYCLE_BIN_CYCLE) {
			$sql = "SELECT	poiID
					FROM	poi".WCF_N."_poi
					WHERE	isDeleted = ? AND deleteTime < ?";
			$statement = WCF::getDB()->prepareStatement($sql, 1000);
			$statement->execute([1, TIME_NOW - POI_EMPTY_RECYCLE_BIN_CYCLE * 86400]);
			$poiIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
			
			if (!empty($poiIDs)) {
				$action = new PoiAction($poiIDs, 'delete');
				$action->executeAction();
			}
		}
	}
}
