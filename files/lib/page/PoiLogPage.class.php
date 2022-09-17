<?php
namespace poi\page;
use poi\data\poi\ViewablePoi;
use poi\data\modification\log\PoiLogModificationLogList;
use poi\system\POICore;
use wcf\page\SortablePage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the poi log page.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiLogPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'time';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = PoiLogModificationLogList::class;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['mod.poi.canEditPoi'];
	
	/**
	 * poi id
	 */
	public $poiID = 0;
	
	/**
	 * poi object
	 */
	public $poi;
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['logID', 'time', 'username'];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->poiID = intval($_REQUEST['id']);
		$this->poi = ViewablePoi::getPoi($this->poiID);
		if ($this->poi === null) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->setPoi($this->poi->getDecoratedObject());
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// add breadcrumbs
		POICore::getInstance()->setLocation($this->poi->getCategory()->getParentCategories(), $this->poi->getCategory(), $this->poi->getDecoratedObject());
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'poi' => $this->poi
		]);
	}
}
