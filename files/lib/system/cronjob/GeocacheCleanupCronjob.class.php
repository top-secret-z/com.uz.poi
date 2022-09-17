<?php
namespace poi\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\cronjob\AbstractCronjob;
use wcf\system\WCF;

/**
 * Deletes expired geocache entries.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class GeocacheCleanupCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		$sql = "DELETE FROM poi".WCF_N."_geocache
				WHERE	time < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([TIME_NOW - POI_MAP_CACHE * 86400]);
	}
}
