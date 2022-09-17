<?php
namespace poi\system\box;
use poi\data\category\PoiCategoryNodeTree;
use poi\page\CategoryPoiListPage;
use poi\page\PoiPage;
use wcf\system\box\AbstractBoxController;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Box for poi categories.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class CategoriesBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected static $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		// get categories
		$categoryTree = new PoiCategoryNodeTree('com.uz.poi.category');
		$categoryList = $categoryTree->getIterator();
		$categoryList->setMaxDepth(0);
		
		if (iterator_count($categoryList)) {
			// get active category
			$activeCategory = null;
			if (RequestHandler::getInstance()->getActiveRequest() !== null) {
				if (RequestHandler::getInstance()->getActiveRequest()->getRequestObject() instanceof CategoryPoiListPage || RequestHandler::getInstance()->getActiveRequest()->getRequestObject() instanceof PoiPage) {
					if (RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->category !== null) {
						$activeCategory = RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->category;
					}
				}
			}
			
			$this->content = WCF::getTPL()->fetch('boxCategories', 'poi', ['categoryList' => $categoryList, 'activeCategory' => $activeCategory], true);
		}
	}
}
