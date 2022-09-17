<?php
namespace poi\data\poi;
use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Poi::class;
	
	/**
	 * Updates the poi counter of the given users.
	 */
	public static function updatePoiCounter(array $users) {
		$sql = "UPDATE	wcf".WCF_N."_user
				SET		poiPois = poiPois + ?
				WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($users as $userID => $pois) {
			$statement->execute([$pois, $userID]);
		}
	}
}
