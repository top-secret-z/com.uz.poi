<?php
namespace poi\data\poi;
use poi\data\category\PoiCategory;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Represents a list of accessible pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class AccessiblePoiList extends ViewablePoiList {
	protected $applyCategoryFilter = true;
	
	/**
	 * Creates a new AccessiblePoiList object.
	 */
	public function __construct() {
		parent::__construct();
		
		// apply category filter
		if ($this->applyCategoryFilter) {
			$accessibleCategoryIDs = PoiCategory::getAccessibleCategoryIDs();
			if (!empty($accessibleCategoryIDs)) $this->getConditionBuilder()->add('poi.categoryID IN (?)', [$accessibleCategoryIDs]);
			else $this->getConditionBuilder()->add('1=0');
		}
		
		// apply permission
		if (!WCF::getSession()->getPermission('mod.poi.canModeratePoi')) {
			if (!WCF::getUser()->userID) {
				$this->getConditionBuilder()->add('poi.isDisabled = 0');
			}
			else {
				$this->getConditionBuilder()->add('(poi.isDisabled = 0 OR poi.userID = ?)', [WCF::getUser()->userID]);
			}
		}
		if (!WCF::getSession()->getPermission('mod.poi.canViewDeletedPoi')) $this->getConditionBuilder()->add('poi.isDeleted = 0');
		
		// apply language filter
		if (POI_ENABLE_MULTILINGUALISM && LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
			$this->getConditionBuilder()->add('(poi.languageID IN (?) OR poi.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		if ($this->objectIDs === null) $this->readObjectIDs();
		
		parent::readObjects();
	}
}
