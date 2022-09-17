<?php
namespace poi\data\category;
use wcf\data\category\Category;
use wcf\system\category\CategoryHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the poi category cache.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiCategoryCache extends SingletonFactory {
	/**
	 * poi data
	 */
	protected $unreadPois;
	protected $pois;
	protected $poisMap;
	
	/**
	 * Returns the number of pois in the category.
	 */
	public function getPois($categoryID) {
		if ($this->pois === null) {
			$this->initPois();
		}
		
		if (isset($this->pois[$categoryID])) {
			return $this->pois[$categoryID];
		}
		
		return 0;
	}
	
	/**
	 * Calculates the number of pois.
	 */
	protected function initPois() {
		$this->pois = [];
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('poi.isDisabled = ?', [0]);
		$conditionBuilder->add('poi.isDeleted = ?', [0]);
		
		// apply language filter
		if (POI_ENABLE_MULTILINGUALISM && LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
			$conditionBuilder->add('(poi.languageID IN (?) OR poi.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
		}
		
		$sql = "SELECT		COUNT(*) AS count, categoryID
				FROM		poi".WCF_N."_poi poi
				".$conditionBuilder."
				GROUP BY	poi.categoryID";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		$pois = $statement->fetchMap('categoryID', 'count');
		
		$categoryToParent = [];
		foreach (CategoryHandler::getInstance()->getCategories(PoiCategory::OBJECT_TYPE_NAME) as $category) {
			if (!isset($categoryToParent[$category->parentCategoryID])) {
				$categoryToParent[$category->parentCategoryID] = [];
			}
			$categoryToParent[$category->parentCategoryID][] = $category->categoryID;
		}
		
		$result = [];
		$this->countPois($categoryToParent, $pois, 0, $result);
		$this->pois = $result;
	}
	
	/**
	 * Returns the pois in this category and its children.
	 */
	protected function countPois(array &$categoryToParent, array &$pois, $categoryID, array &$result) {
		$count = (isset($pois[$categoryID])) ? $pois[$categoryID] : 0;
		
		if (isset($categoryToParent[$categoryID])) {
			foreach ($categoryToParent[$categoryID] as $childCategoryID) {
				if (PoiCategory::getCategory($childCategoryID)->getPermission('canViewCategory')) {
					$count += $this->countPois($categoryToParent, $pois, $childCategoryID, $result);
				}
			}
		}
		
		if ($categoryID) {
			$result[$categoryID] = $count;
		}
		
		return $count;
	}
	
	/**
	 * Returns the number of unread pois in the category with the given id.
	 */
	public function getUnreadPois($categoryID) {
		if ($this->unreadPois === null) {
			$this->initUnreadPois();
		}
		
		if (isset($this->unreadPois[$categoryID])) {
			return $this->unreadPois[$categoryID];
		}
		
		return 0;
	}
	
	/**
	 * Gets the number of unread pois.
	 */
	protected function initUnreadPois() {
		$this->unreadPois = [];
		
		if (WCF::getUser()->userID) {
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('poi.lastChangeTime > ?', [VisitTracker::getInstance()->getVisitTime('com.uz.poi.poi')]);
			$conditionBuilder->add('poi.isDisabled = ?', [0]);
			$conditionBuilder->add('poi.isDeleted = ?', [0]);
			
			// apply language filter
			if (POI_ENABLE_MULTILINGUALISM && LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
				$conditionBuilder->add('(poi.languageID IN (?) OR poi.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
			}
			
			$conditionBuilder->add('tracked_visit.visitTime IS NULL');
			
			$sql = "SELECT		COUNT(*) AS count, poi.categoryID
					FROM		poi".WCF_N."_poi poi
					LEFT JOIN	wcf".WCF_N."_tracked_visit tracked_visit
					ON		(tracked_visit.objectTypeID = ".VisitTracker::getInstance()->getObjectTypeID('com.uz.poi.poi')." AND tracked_visit.objectID = poi.poiID AND tracked_visit.userID = ".WCF::getUser()->userID.")
					".$conditionBuilder."
					GROUP BY	poi.categoryID";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
			$unreadPois = $statement->fetchMap('categoryID', 'count');
			
			$categoryToParent = [];
			foreach (CategoryHandler::getInstance()->getCategories(PoiCategory::OBJECT_TYPE_NAME) as $category) {
				if (!isset($categoryToParent[$category->parentCategoryID])) {
					$categoryToParent[$category->parentCategoryID] = [];
				}
				$categoryToParent[$category->parentCategoryID][] = $category->categoryID;
			}
			
			$result = [];
			$this->countPois($categoryToParent, $unreadPois, 0, $result);
			$this->unreadPois = $result;
		}
	}
	
	/**
	 * Returns the number of pois in the category without children for map.
	 */
	public function getPoisMap($categoryID) {
		if ($this->poisMap === null) {
			$this->initPoisMap();
		}
		
		if (isset($this->poisMap[$categoryID])) {
			return $this->poisMap[$categoryID];
		}
		
		return 0;
	}
	
	/**
	 * Calculates the number of pois for map.
	 */
	protected function initPoisMap() {
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('poi.isDisabled = ?', [0]);
		$conditionBuilder->add('poi.isDeleted = ?', [0]);
		
		// apply language filter
		if (POI_ENABLE_MULTILINGUALISM && LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
			$conditionBuilder->add('(poi.languageID IN (?) OR poi.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
		}
		
		$sql = "SELECT		COUNT(*) AS count, categoryID
				FROM		poi".WCF_N."_poi poi
				".$conditionBuilder."
				GROUP BY	poi.categoryID";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		$pois = $statement->fetchMap('categoryID', 'count');
		
		$this->poisMap = $pois;
	}
}
