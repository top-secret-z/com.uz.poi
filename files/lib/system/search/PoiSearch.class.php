<?php
namespace poi\system\search;
use poi\data\category\PoiCategory;
use poi\data\category\PoiCategoryNodeTree;
use poi\data\poi\SearchResultPoiList;
use wcf\data\search\ISearchResultObject;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\search\AbstractSearchProvider;
use wcf\system\WCF;

/**
 * An implementation of ISearchableObjectType for searching in pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
final class PoiSearch extends AbstractSearchProvider {
	/**
	 * data
	 */
	private $poiCategoryID = 0;
	private $messageCache = [];
	
	/**
	 * @inheritDoc
	 */
	public function cacheObjects(array $objectIDs, ?array $additionalData = null): void {
		$poiList = new SearchResultPoiList();
		$poiList->setObjectIDs($objectIDs);
		$poiList->readObjects();
		foreach ($poiList->getObjects() as $poi) {
			$this->messageCache[$poi->poiID] = $poi;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObject(int $objectID): ?ISearchResultObject {
		return $this->messageCache[$objectID] ?? null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTableName(): string {
		return 'poi' . WCF_N . '_poi';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getIDFieldName(): string {
		return $this->getTableName() . '.poiID';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getConditionBuilder(array $parameters): ?PreparedStatementConditionBuilder {
		$this->readParameters($parameters);
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$this->initCategoryCondition($conditionBuilder);
		$this->initMiscConditions($conditionBuilder);
		$this->initLanguageCondition($conditionBuilder);
		
		return $conditionBuilder;
	}
	
	private function initCategoryCondition(PreparedStatementConditionBuilder $conditionBuilder): void {
		$selectedCategoryIDs = $this->getPoiCategoryIDs($this->poiCategoryID);
		$accessibleCategoryIDs = PoiCategory::getAccessibleCategoryIDs();
		if (!empty($selectedCategoryIDs)) {
			$selectedCategoryIDs = array_intersect($selectedCategoryIDs, $accessibleCategoryIDs);
		} else {
			$selectedCategoryIDs = $accessibleCategoryIDs;
		}
		
		if (empty($selectedCategoryIDs)) {
			$conditionBuilder->add('1=0');
		} else {
			$conditionBuilder->add($this->getTableName() . '.categoryID IN (?)', [$selectedCategoryIDs]);
		}
	}
	
	private function getPoiCategoryIDs(int $categoryID): array {
		$categoryIDs = [];
		
		if ($categoryID) {
			if (($category = PoiCategory::getCategory($categoryID)) !== null) {
				$categoryIDs[] = $categoryID;
				foreach ($category->getAllChildCategories() as $childCategory) {
					$categoryIDs[] = $childCategory->categoryID;
				}
			}
		}
		
		return $categoryIDs;
	}
	
	private function initMiscConditions(PreparedStatementConditionBuilder $conditionBuilder): void {
		$conditionBuilder->add($this->getTableName() . '.isDisabled = 0');
		$conditionBuilder->add($this->getTableName() . '.isDeleted = 0');
	}
	
	private function initLanguageCondition(PreparedStatementConditionBuilder $conditionBuilder): void {
		if (LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
			$conditionBuilder->add(
					'(' . $this->getTableName() . '.languageID IN (?) OR ' . $this->getTableName() . '.languageID IS NULL)',
					[WCF::getUser()->getLanguageIDs()]
					);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFormTemplateName(): string {
		return 'searchPoi';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAdditionalData(): ?array {
		return ['poiCategoryID' => $this->poiCategoryID];
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables(): void {
		WCF::getTPL()->assign([
				'poiCategoryList' => (new PoiCategoryNodeTree('com.uz.poi.category'))->getIterator(),
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function isAccessible(): bool {
		return WCF::getSession()->getPermission('user.poi.canViewPoi');
	}
	
	private function readParameters(array $parameters): void {
		if (!empty($parameters['poiCategoryID'])) {
			$this->poiCategoryID = intval($parameters['poiCategoryID']);
		}
	}
}
