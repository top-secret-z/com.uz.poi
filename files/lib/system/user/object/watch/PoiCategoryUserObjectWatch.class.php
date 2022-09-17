<?php
namespace poi\system\user\object\watch;
use poi\data\category\PoiCategory;
use wcf\data\object\type\AbstractObjectTypeProcessor;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\user\object\watch\IUserObjectWatch;

/**
 * Implementation of IUserObjectWatch for watched categories.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiCategoryUserObjectWatch extends AbstractObjectTypeProcessor implements IUserObjectWatch {
	/**
	 * @inheritDoc
	 */
	public function validateObjectID($objectID) {
		$category = PoiCategory::getCategory($objectID);
		if ($category === null) {
			throw new IllegalLinkException();
		}
		if (!$category->isAccessible()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function resetUserStorage(array $userIDs) {
		UserStorageHandler::getInstance()->reset($userIDs, 'poiUnreadWatchedPois');
		UserStorageHandler::getInstance()->reset($userIDs, 'poiSubscribedCategories');
	}
}
