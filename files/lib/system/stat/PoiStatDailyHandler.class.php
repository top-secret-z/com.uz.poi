<?php
namespace poi\system\stat;
use wcf\system\stat\AbstractStatDailyHandler;

/**
 * Stat handler implementation for pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiStatDailyHandler extends AbstractStatDailyHandler {
	/**
	 * @inheritDoc
	 */
	public function getData($date) {
		return [
				'counter' => $this->getCounter($date, 'poi'.WCF_N.'_poi', 'time'),
				'total' => $this->getTotal($date, 'poi'.WCF_N.'_poi', 'time')
		];
	}
}
