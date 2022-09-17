<?php
namespace poi\system\box;
use poi\data\poi\AccessiblePoiList;
use wcf\system\box\AbstractDatabaseObjectListBoxController;
use wcf\system\WCF;

/**
 * Box for poi list.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiListBoxController extends AbstractDatabaseObjectListBoxController {
	/**
	 * @inheritDoc
	 */
	protected static $supportedPositions = ['sidebarLeft', 'sidebarRight', 'contentTop', 'contentBottom', 'top', 'bottom', 'footerBoxes'];
	
	/**
	 * @inheritDoc
	 */
	protected $sortFieldLanguageItemPrefix = 'poi.poi';
	
	/**
	 * @var string[]
	 */
	protected static $limitedPoiStats = [
			'cumulativeLikes' => 'latestCumulativeLikes'
	];
	
	/**
	 * @inheritDoc
	 */
	public $defaultLimit = 6;
	
	/**
	 * @inheritDoc
	 */
	protected $conditionDefinition = 'com.uz.poi.box.poiList.condition';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = [
			'time',
			'comments',
			'cumulativeLikes',
			'random'
	];
	
	/**
	 * @inheritDoc
	 */
	protected function getObjectList() {
		$objectList = new AccessiblePoiList();
		
		switch ($this->sortField) {
			case 'comments':
				$objectList->getConditionBuilder()->add('poi.comments > ?', [0]);
				break;
		}
		
		if ($this->sortField == 'random') {
			$this->sortField = 'RAND()';
			$this->sortOrder = ' ';
		}
		
		return $objectList;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getTemplate() {
		return WCF::getTPL()->fetch('boxPoiList', 'poi', [
				'boxPoiList' => $this->objectList,
				'boxSortField' => $this->sortField,
				'boxPosition' => $this->box->position
		], true);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function readObjects() {
		$sortField = $this->box->sortField;
		
		if ($sortField === 'cumulativeLikes') {
			$this->objectList->sqlOrderBy = 'poi.' . $this->objectList->sqlOrderBy;
		}
		
		parent::readObjects();
	}
}
