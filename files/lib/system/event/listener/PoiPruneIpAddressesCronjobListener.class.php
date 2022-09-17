<?php
namespace poi\system\event\listener;
use wcf\system\event\listener\IParameterizedEventListener;

/**
 * Prunes the stored ip addresses.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiPruneIpAddressesCronjobListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		$eventObj->columns['poi'.WCF_N.'_poi']['ipAddress'] = 'time';
	}
}
