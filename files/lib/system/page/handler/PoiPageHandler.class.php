<?php
namespace poi\system\page\handler;
use poi\data\poi\ViewablePoiList;
use poi\system\cache\runtime\ViewablePoiRuntimeCache;
use wcf\data\page\Page;
use wcf\data\user\online\UserOnline;
use wcf\system\page\handler\AbstractLookupPageHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\page\handler\TOnlineLocationPageHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Menu page handler for the poi page.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler {
	use TOnlineLocationPageHandler;
	
	/**
	 * @inheritDoc
	 */
	public function isValid($objectID) {
		return ViewablePoiRuntimeCache::getInstance()->getObject($objectID) !== null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		return ViewablePoiRuntimeCache::getInstance()->getObject($objectID)->canRead();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink($objectID) {
		return ViewablePoiRuntimeCache::getInstance()->getObject($objectID)->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function lookup($searchString) {
		$poiList = new ViewablePoiList();
		$poiList->getConditionBuilder()->add('poi.subject LIKE ?', ['%' . $searchString . '%']);
		$poiList->sqlLimit = 10;
		$poiList->sqlOrderBy = 'poi.subject';
		$poiList->readObjects();
		
		$results = [];
		foreach ($poiList->getObjects() as $poi) {
			$results[] = [
					'description' => StringUtil::encodeHTML($poi->getTeaser()),
					'link' => $poi->getLink(),
					'objectID' => $poi->poiID,
					'title' => $poi->getTitle()
			];
		}
		
		return $results;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getOnlineLocation(Page $page, UserOnline $user) {
		if ($user->pageObjectID === null) {
			return '';
		}
		
		$poi = ViewablePoiRuntimeCache::getInstance()->getObject($user->pageObjectID);
		if ($poi === null || !$poi->canRead()) {
			return '';
		}
		
		return WCF::getLanguage()->getDynamicVariable('wcf.page.onlineLocation.'.$page->identifier, ['poi' => $poi]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function prepareOnlineLocation(/** @noinspection PhpUnusedParameterInspection */Page $page, UserOnline $user) {
		if ($user->pageObjectID !== null) {
			ViewablePoiRuntimeCache::getInstance()->cacheObjectID($user->pageObjectID);
		}
	}
}
