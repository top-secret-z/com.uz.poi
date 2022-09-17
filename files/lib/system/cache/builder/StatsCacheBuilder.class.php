<?php
namespace poi\system\cache\builder;
use wcf\system\cache\builder\AbstractCacheBuilder;
use wcf\system\WCF;

/**
 * Caches the poi statistics.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class StatsCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected $maxLifetime = 1200;
	
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$data = [];
		
		// number of pois
		$sql = "SELECT	COUNT(*) AS count, SUM(views) AS views
				FROM	poi".WCF_N."_poi";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$row = $statement->fetchSingleRow();
		$data['pois'] = $row['count'];
		$data['views'] = $row['views'];
		
		// views per day
		$days = ceil((TIME_NOW - POI_INSTALL_DATE) / 86400);
		if ($days <= 0) $days = 1;
		$data['viewsPerDay'] = $data['views'] / $days;
		
		// number of comments
		$sql = "SELECT	SUM(comments)
				FROM	poi".WCF_N."_poi
				WHERE	comments > 0";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$data['comments'] = $statement->fetchSingleColumn();
		
		// number of authors
		$sql = "SELECT	COUNT(DISTINCT userID)
				FROM	poi".WCF_N."_poi";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$data['authors'] = $statement->fetchSingleColumn();
		
		return $data;
	}
}
