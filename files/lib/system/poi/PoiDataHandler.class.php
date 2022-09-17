<?php
namespace poi\system\poi;
use poi\data\poi\Poi;
use poi\data\poi\PoiList;
use wcf\system\SingletonFactory;

/**
 * Caches poi objects for poi-related user notifications.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiDataHandler extends SingletonFactory {
	/**
	 * list of cached poi ids
	 */
	protected $poiIDs = [];
	
	/**
	 * list of cached poi objects
	 */
	protected $pois = [];
	
	/**
	 * Caches an poi id.
	 */
	public function cachePoiID($poiID) {
		if (!in_array($poiID, $this->poiIDs)) {
			$this->poiIDs[] = $poiID;
		}
	}
	
	/**
	 * Returns the poi with the given id.
	 */
	public function getPoi($poiID) {
		if (!empty($this->poiIDs)) {
			$this->poiIDs = array_diff($this->poiIDs, array_keys($this->pois));
			
			if (!empty($this->poiIDs)) {
				$poiList = new PoiList();
				$poiList->setObjectIDs($this->poiIDs);
				$poiList->readObjects();
				$this->pois += $poiList->getObjects();
				$this->poiIDs = [];
			}
		}
		
		if (isset($this->pois[$poiID])) {
			return $this->pois[$poiID];
		}
		
		return null;
	}
}
