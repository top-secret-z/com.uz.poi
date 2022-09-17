<?php
namespace poi\page;
use poi\data\category\PoiCategory;
use poi\data\poi\CategoryFeedPoiList;
use wcf\page\AbstractFeedPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;

/**
 * Shows pois for the specified categories in feed.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class CategoryPoiListFeedPage extends PoiListFeedPage {
	/**
	 * category the listed pois belong to
	 */
	public $category;
	
	/**
	 * id of the category the listed pois belong to
	 */
	public $categoryID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->categoryID = intval($_REQUEST['id']);
		$this->category = PoiCategory::getCategory($this->categoryID);
		if ($this->category === null) {
			throw new IllegalLinkException();
		}
		if (!$this->category->isAccessible()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		AbstractFeedPage::readData();
		
		// read the pois
		$this->items = new CategoryFeedPoiList($this->categoryID, true);
		$this->items->sqlLimit = 20;
		$this->items->readObjects();
		$this->title = $this->category->getTitle();
	}
}
