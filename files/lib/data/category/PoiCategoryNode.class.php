<?php
namespace poi\data\category;
use wcf\data\category\CategoryNode;

/**
 * Represents a poi category node.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiCategoryNode extends CategoryNode {
	/**
	 * poi data
	 */
	protected $unreadPois;
	protected $pois;
	protected $poisMap;
	
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = PoiCategory::class;
	
	/**
	 * Returns the number of pois in the category and children.
	 */
	public function getPois() {
		if ($this->pois === null) {
			$this->pois = PoiCategoryCache::getInstance()->getPois($this->categoryID);
		}
		
		return $this->pois;
	}
	
	/**
	 * Returns the number of unread pois in the category and childre.
	 */
	public function getUnreadPois() {
		if ($this->unreadPois === null) {
			$this->unreadPois = PoiCategoryCache::getInstance()->getUnreadPois($this->categoryID);
		}
		
		return $this->unreadPois;
	}
	
	/**
	 * Returns the number of pois in the category for map excl. children.
	 */
	public function getPoisMap() {
		if ($this->poisMap === null) {
			$this->poisMap = PoiCategoryCache::getInstance()->getPoisMap($this->categoryID);
		}
		
		return $this->poisMap;
	}
}
