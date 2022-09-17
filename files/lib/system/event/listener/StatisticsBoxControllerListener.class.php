<?php
namespace poi\system\event\listener;
use poi\system\cache\builder\StatsCacheBuilder;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\WCF;

/**
 * Adds the Poi stats in the statistics box.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class StatisticsBoxControllerListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		WCF::getTPL()->assign([
				'poiStatistics' => StatsCacheBuilder::getInstance()->getData()
		]);
	}
}
