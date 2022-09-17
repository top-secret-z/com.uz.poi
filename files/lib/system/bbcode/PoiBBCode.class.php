<?php
namespace poi\system\bbcode;
use wcf\system\bbcode\AbstractBBCode;
use wcf\system\bbcode\BBCodeParser;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Parses the [poi] bbcode tag.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiBBCode extends AbstractBBCode {
	/**
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		$poiIDs = [];
		if (isset($openingTag['attributes'][0])) {
			$poiIDs = array_unique(ArrayUtil::toIntegerArray(explode(',', $openingTag['attributes'][0])));
		}
		
		$pois = [];
		foreach ($poiIDs as $poiID) {
			$poi = MessageEmbeddedObjectManager::getInstance()->getObject('com.uz.poi.poi', $poiID);
			if ($poi !== null && $poi->canRead()) {
				$pois[] = $poi;
			}
		}
		
		if (!empty($pois)) {
			if ($parser->getOutputType() == 'text/html') {
				return WCF::getTPL()->fetch('poiBBCode', 'poi', [
						'pois' => $pois,
						'titleHash' => substr(StringUtil::getRandomID(), 0, 8)
				], true);
			}
			
			$result = '';
			foreach ($pois as $poi) {
				if (!empty($result)) $result .= ' ';
				$result .= StringUtil::getAnchorTag(LinkHandler::getInstance()->getLink('Poi', [
						'application' => 'poi',
						'object' => $poi
				]));
			}
				
			return $result;
		}
		
		if (!empty($poiIDs)) {
			$result = '';
			foreach ($poiIDs as $poiID) {
				if ($poiID) {
					if (!empty($result)) $result .= ' ';
					$result .= StringUtil::getAnchorTag(LinkHandler::getInstance()->getLink('Poi', [
							'application' => 'poi',
							'id' => $poiID
					]));
				}
			}
			
			return $result;
		}
	}
}
