<?php
namespace poi\data\poi;
use wcf\data\like\object\AbstractLikeObject;
use wcf\data\like\Like;
use wcf\data\reaction\object\IReactionObject;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\object\LikeUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Likeable object implementation for pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class LikeablePoi extends AbstractLikeObject implements IReactionObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Poi::class;
	
	/**
	 * @inheritDoc
	 */
	public function getLanguageID() {
		return $this->getDecoratedObject()->languageID;
	}
	
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
		return LinkHandler::getInstance()->getLink('Poi', [
				'application' => 'poi',
				'object' => $this->getDecoratedObject()
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getUserID() {
		return $this->userID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function sendNotification(Like $like) {
		if ($this->getDecoratedObject()->userID != WCF::getUser()->userID) {
			$notificationObject = new LikeUserNotificationObject($like);
			UserNotificationHandler::getInstance()->fireEvent(
				'like',
				'com.uz.poi.likeablePoi.notification',
				$notificationObject,
				[$this->getDecoratedObject()->userID],
				['objectID' => $this->getDecoratedObject()->poiID]
			);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function updateLikeCounter($cumulativeLikes) {
		// update cumulative likes
		$editor = new PoiEditor($this->getDecoratedObject());
		$editor->update([
				'cumulativeLikes' => $cumulativeLikes
		]);
	}
}
