<?php
namespace poi\data\modification\log;
use wcf\data\modification\log\ModificationLog;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\WCF;

/**
 * Provides a viewable poi modification log within poi log page.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class ViewablePoiListPoiModificationLog extends DatabaseObjectDecorator {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ModificationLog::class;
	
	/**
	 * Returns readable representation of current log entry.
	 */
	public function __toString() {
		return WCF::getLanguage()->getDynamicVariable('poi.poi.log.poi.'.$this->action.'.summary', [
				'additionalData' => $this->additionalData,
				'time' => $this->time,
				'userID' => $this->userID,
				'username' => $this->username
		]);
	}
}
