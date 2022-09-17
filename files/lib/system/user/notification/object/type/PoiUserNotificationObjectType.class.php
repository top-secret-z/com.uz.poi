<?php
namespace poi\system\user\notification\object\type;
use poi\data\poi\Poi;
use poi\data\poi\PoiList;
use poi\system\user\notification\object\PoiUserNotificationObject;
use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;

/**
 * Represents a poi as a notification object type.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = PoiUserNotificationObject::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = Poi::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = PoiList::class;
}
