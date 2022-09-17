<?php
namespace poi\system\page\handler;
use poi\data\poi\ViewablePoi;
use wcf\system\page\handler\AbstractMenuPageHandler;
use wcf\system\WCF;

/**
 * Menu page handler for list of unread pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class UnreadPoiListPagePageHandler extends AbstractMenuPageHandler {
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getOutstandingItemCount($objectID = null) {
		return ViewablePoi::getUnreadPois();
	}
	
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		return (WCF::getUser()->userID != 0 && ViewablePoi::getUnreadPois());
	}
}
