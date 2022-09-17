<?php
namespace poi\data\category;
use wcf\data\category\CategoryEditor;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Executes poi category-related actions.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiCategoryAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = CategoryEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['markAllAsRead'];
	
	/**
	 * Validates the mark all as read action.
	 */
	public function validateMarkAllAsRead() {
		// nothing so far
	}
	
	/**
	 * Marks all categories as read.
	 */
	public function markAllAsRead() {
		VisitTracker::getInstance()->trackTypeVisit('com.uz.poi.poi');
		
		// reset storage and notifications
		if (WCF::getUser()->userID) {
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'poiUnreadPois');
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'poiUnreadWatchedPois');
		}
	}
}
