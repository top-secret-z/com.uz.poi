<?php
namespace poi\page;
use poi\data\poi\AccessiblePoiList;
use poi\system\cache\builder\StatsCacheBuilder;
use poi\system\POICore;
use wcf\page\SortablePage;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\WCF;

/**
 * Shows a list of pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = POI_POIS_PER_PAGE;
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = AccessiblePoiList::class;
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'lastChangeTime';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['username', 'subject', 'lastChangeTime', 'cumulativeLikes', 'comments', 'views', 'visits'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.poi.canViewPoi'];
	
	/**
	 * statistics
	 */
	public $stats = [];
	
	/**
	 * controller name
	 */
	public $controllerName = 'PoiList';
	
	/**
	 * app parameters
	 */
	public $controllerParameters = ['application' => 'poi'];
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// stats
		if (POI_INDEX_ENABLE_STATS) {
			$this->stats = StatsCacheBuilder::getInstance()->getData();
		}
		
		// add breadcrumbs
		POICore::getInstance()->setLocation();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function readObjects() {
		$this->sqlOrderBy = 'poi.'.$this->sqlOrderBy;
		
		parent::readObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.uz.poi.poi')),
				'allowSpidersToIndexThisPage' => true,
				'feedControllerName' => 'PoiListFeed',
				'controllerName' => $this->controllerName,
				'controllerObject' => null,
				'stats' => $this->stats
		]);
	}
}
