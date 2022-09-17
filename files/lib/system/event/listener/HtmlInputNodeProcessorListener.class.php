<?php
namespace poi\system\event\listener;
use poi\data\poi\AccessiblePoiList;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\event\listener\AbstractHtmlInputNodeProcessorListener;
use wcf\system\request\LinkHandler;

/**
 * Parses URLs of poi entries.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class HtmlInputNodeProcessorListener extends AbstractHtmlInputNodeProcessorListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		// replace links
		if (BBCodeHandler::getInstance()->isAvailableBBCode('poi')) {
			$regex = $this->getRegexFromLink(LinkHandler::getInstance()->getLink('Poi', [
					'application' => 'poi',
					'forceFrontend' => true
			]));
			$poiIDs = $this->getObjectIDs($eventObj, $regex);
			
			if (!empty($poiIDs)) {
				$poiList = new AccessiblePoiList();
				$poiList->getConditionBuilder()->add('poi.poiID IN (?)', [array_unique($poiIDs)]);
				$poiList->readObjects();
				
				$this->replaceLinksWithBBCode($eventObj, $regex, $poiList->getObjects(), 'poi');
			}
		}
	}
}
