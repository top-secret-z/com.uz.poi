<?php
namespace poi\page;
use poi\data\category\PoiCategory;
use poi\data\poi\CategoryPoiList;
use poi\system\POICore;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of pois in a certain category.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class CategoryPoiListPage extends PoiListPage {
	/**
	 * @inheritDoc
	 */
	public $templateName = 'poiList';
	
	/**
	 * category of pois
	 */
	public $category;
	public $categoryID = 0;
	
	/**
	 * @inheritDoc
	 */
	public $controllerName = 'CategoryPoiList';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		if (isset($_REQUEST['id'])) $this->categoryID = intval($_REQUEST['id']);
		$this->category = PoiCategory::getCategory($this->categoryID);
		if ($this->category === null) {
			throw new IllegalLinkException();
		}
		$this->controllerParameters['object'] = $this->category;
		parent::readParameters();
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('CategoryPoiList', [
				'application' => 'poi',
				'object' => $this->category
		], ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : ''));
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkPermissions() {
		parent::checkPermissions();
		
		if (!$this->category->isAccessible()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		$this->objectList = new CategoryPoiList($this->categoryID, true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		POICore::getInstance()->setLocation($this->category->getParentCategories());
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'categoryID' => $this->categoryID,
				'category' => $this->category,
				'feedControllerName' => 'CategoryPoiListFeed',
				'controllerObject' => $this->category
		]);
	}
}
