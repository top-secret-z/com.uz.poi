<?php
namespace poi\data\poi\geocache;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a geocache entry.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class Geocache extends DatabaseObject {
	/**
	 * Returns geocode cache by hash
	 */
	public static function getCacheLocation($hash) {
		$sql = "SELECT	*
				FROM		poi".WCF_N."_geocache
				WHERE		hash = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$hash]);
		$row = $statement->fetchArray();
		if (!$row) $row = [];
		
		return new Geocache(null, $row);
	}
	
	/**
	 * Store data in cache
	 */
	public static function setCacheLocation($data) {
		$sql = "INSERT INTO	poi".WCF_N."_geocache
							(hash, location, lat, lng, time, type)
				VALUES		(?, ?, ?, ?, ?, ?)
				ON DUPLICATE KEY
				UPDATE		lat = ?, lng = ?, time = ?, type = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
				$data['hash'],
				$data['location'],
				$data['lat'],
				$data['lng'],
				$data['time'],
				$data['type'],
				$data['lat'],
				$data['lng'],
				$data['time'],
				$data['type']
		]);
		
		return self::getCacheLocation($data['hash']);
	}
}
