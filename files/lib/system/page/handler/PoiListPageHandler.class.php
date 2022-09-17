<?php
namespace poi\system\page\handler;
use poi\data\poi\ViewablePoi;
use wcf\system\page\handler\AbstractMenuPageHandler;

/**
 * Provides the number of unread pois for menu display.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiListPageHandler extends AbstractMenuPageHandler {
	/**
	 * @inheritDoc
	 */
	public function getOutstandingItemCount($objectID = null) {
		return ViewablePoi::getUnreadPois();
	}
}
