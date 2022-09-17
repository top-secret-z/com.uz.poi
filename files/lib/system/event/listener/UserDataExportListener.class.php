<?php
namespace poi\system\event\listener;
use wcf\system\event\listener\IParameterizedEventListener;

/**
 * Exports user data iaw Gdpr.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class UserDataExportListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		// only IP addresses
		$eventObj->data['com.uz.poi'] = [
				'ipAddresses' => $eventObj->exportIpAddresses('poi'.WCF_N.'_poi', 'ipAddress', 'time', 'userID')
		];
	}
}
