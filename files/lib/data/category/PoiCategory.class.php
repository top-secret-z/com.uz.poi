<?php
namespace poi\data\category;
use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\user\User;
use wcf\data\IAccessibleObject;
use wcf\data\ITitledLinkObject;
use wcf\data\user\UserProfile;
use wcf\system\category\CategoryHandler;
use wcf\system\category\CategoryPermissionHandler;
use wcf\system\request\LinkHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Represents a poi category.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiCategory extends AbstractDecoratedCategory implements IAccessibleObject, ITitledLinkObject {
	/**
	 * acl permissions of this category
	 */
	protected $userPermissions = [];
	
	/**
	 * object type name of the categories
	 */
	const OBJECT_TYPE_NAME = 'com.uz.poi.category';
	/**
	 * subscribed categories
	 */
	protected static $subscribedCategories;
	
	/**
	 * Returns ids of accessible categories.
	 */
	public static function getAccessibleCategoryIDs($permissions = ['canViewCategory']) {
		$categoryIDs = [];
		foreach (CategoryHandler::getInstance()->getCategories(self::OBJECT_TYPE_NAME) as $category) {
			$result = true;
			
			$category = new PoiCategory($category);
			foreach ($permissions as $permission) {
				$result = $result && $category->getPermission($permission);
			}
			
			if ($result) $categoryIDs[] = $category->categoryID;
		}
		
		return $categoryIDs;
	}
	
	/**
	 * Returns the subscribed categories.
	 */
	public static function getSubscribedCategoryIDs() {
		if (self::$subscribedCategories === null) {
			self::$subscribedCategories = [];
			
			if (WCF::getUser()->userID) {
				$data = UserStorageHandler::getInstance()->getField('poiSubscribedCategories');
				
				// cache does not exist or is outdated
				if ($data === null) {
					$objectTypeID = UserObjectWatchHandler::getInstance()->getObjectTypeID('com.uz.poi.category');
					
					$sql = "SELECT	objectID
							FROM	wcf".WCF_N."_user_object_watch
							WHERE	objectTypeID = ? AND userID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute([$objectTypeID, WCF::getUser()->userID]);
					self::$subscribedCategories = $statement->fetchAll(\PDO::FETCH_COLUMN);
					
					// update storage data
					UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'poiSubscribedCategories', serialize(self::$subscribedCategories));
				}
				else {
					self::$subscribedCategories = unserialize($data);
				}
			}
		}
		
		return self::$subscribedCategories;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('CategoryPoiList', [
				'application' => 'poi',
				'forceFrontend' => true,
				'object' => $this->getDecoratedObject()
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getPermission($permission, User $user = null) {
		if ($user === null) {
			$user = WCF::getUser();
		}
		
		if (!isset($this->userPermissions[$user->userID])) {
			$this->userPermissions[$user->userID] = CategoryPermissionHandler::getInstance()->getPermissions($this->getDecoratedObject(), $user);
		}
		
		if (isset($this->userPermissions[$user->userID][$permission])) {
			return $this->userPermissions[$user->userID][$permission];
		}
		
		if ($this->getParentCategory()) {
			return $this->getParentCategory()->getPermission($permission, $user);
		}
		
		if ($permission == 'canViewCategory') $permission = 'canViewPoi';
		if ($permission == 'canUseCategory') $permission = 'canAddPoi';
		
		if ($user->userID === WCF::getSession()->getUser()->userID) {
			return WCF::getSession()->getPermission('user.poi.'.$permission);
		}
		else {
			$userProfile = new UserProfile($user);
			return $userProfile->getPermission('user.poi.'.$permission);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->title);
	}
	
	/**
	 * @inheritDoc
	 */
	public function isAccessible(User $user = null) {
		if ($this->getObjectType()->objectType != self::OBJECT_TYPE_NAME) return false;
		
		// check permissions
		return $this->getPermission('canViewCategory', $user);
	}
	
	/**
	 * Returns true if the active user has subscribed to this category.
	 */
	public function isSubscribed() {
		return in_array($this->categoryID, self::getSubscribedCategoryIDs());
	}
}
