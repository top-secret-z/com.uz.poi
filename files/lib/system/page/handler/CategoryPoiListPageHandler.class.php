<?php
namespace poi\system\page\handler;
use poi\data\category\PoiCategory;
use poi\data\category\PoiCategoryCache;
use wcf\system\page\handler\AbstractLookupPageHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\page\handler\TDecoratedCategoryLookupPageHandler;
use wcf\system\page\handler\TDecoratedCategoryOnlineLocationLookupPageHandler;

/**
 * Menu page handler for the category poi list page.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class CategoryPoiListPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler {
	use TDecoratedCategoryOnlineLocationLookupPageHandler;
	
	/**
	 * @see	TDecoratedCategoryLookupPageHandler::getDecoratedCategoryClass()
	 */
	protected function getDecoratedCategoryClass() {
		return PoiCategory::class;
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getOutstandingItemCount($objectID = null) {
		return PoiCategoryCache::getInstance()->getUnreadPois($objectID);
	}
}
