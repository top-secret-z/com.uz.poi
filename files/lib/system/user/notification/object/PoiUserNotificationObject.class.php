<?php
namespace poi\system\user\notification\object;
use poi\data\poi\Poi;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\user\notification\object\IUserNotificationObject;

/**
 * Represents a poi as a notification object.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Poi::class;
	
	/**
	 * @inheritDoc
	 */
	public function getObjectID() {
		return $this->poiID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getSubject();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getURL() {
		return $this->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAuthorID() {
		return $this->userID;
	}
}
