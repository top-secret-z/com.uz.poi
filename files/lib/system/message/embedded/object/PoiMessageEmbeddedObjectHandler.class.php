<?php
namespace poi\system\message\embedded\object;
use poi\data\poi\AccessiblePoiList;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\AbstractMessageEmbeddedObjectHandler;
use wcf\util\ArrayUtil;

/**
 * Message embedded object handler implementation for pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiMessageEmbeddedObjectHandler extends AbstractMessageEmbeddedObjectHandler {
	/**
	 * @inheritDoc
	 */
	public function loadObjects(array $objectIDs) {
		$poiList = new AccessiblePoiList();
		$poiList->getConditionBuilder()->add('poi.poiID IN (?)', [$objectIDs]);
		$poiList->readObjects();
		return $poiList->getObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData) {
		if (!empty($embeddedData['poi'])) {
			$parsedPoiIDs = [];
			foreach ($embeddedData['poi'] as $attributes) {
				if (!empty($attributes[0])) {
					$parsedPoiIDs = array_merge($parsedPoiIDs, ArrayUtil::toIntegerArray(explode(',', $attributes[0])));
				}
			}
			
			$poiIDs = array_unique(array_filter($parsedPoiIDs));
			if (!empty($poiIDs)) {
				$poiList = new AccessiblePoiList();
				$poiList->getConditionBuilder()->add('poi.poiID IN (?)', [$poiIDs]);
				$poiList->readObjectIDs();
				
				return $poiList->getObjectIDs();
			}
		}
		
		return [];
	}
}
