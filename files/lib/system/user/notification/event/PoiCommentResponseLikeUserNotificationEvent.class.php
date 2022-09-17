<?php
namespace poi\system\user\notification\event;
use poi\system\poi\PoiDataHandler;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;
use wcf\system\user\notification\event\TReactionUserNotificationEvent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * User notification event for poi comment response likes.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiCommentResponseLikeUserNotificationEvent extends AbstractSharedUserNotificationEvent {
	use TReactionUserNotificationEvent;
	/**
	 * @inheritDoc
	 */
	protected $stackable = true;
	
	/**
	 * @inheritDoc
	 */
	protected function prepare() {
		PoiDataHandler::getInstance()->cachePoiID($this->additionalData['objectID']);
		UserProfileRuntimeCache::getInstance()->cacheObjectID($this->additionalData['commentUserID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('poi.poi.commentResponse.like.notification.title.stacked', [
					'count' => $count,
					'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->get('poi.poi.commentResponse.like.notification.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$poi = PoiDataHandler::getInstance()->getPoi($this->additionalData['objectID']);
		$authors = array_values($this->getAuthors());
		$count = count($authors);
		$commentUser = null;
		if ($this->additionalData['commentUserID'] != WCF::getUser()->userID) {
			$commentUser = UserProfileRuntimeCache::getInstance()->getObject($this->additionalData['commentUserID']);
		}
		
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('poi.poi.commentResponse.like.notification.message.stacked', [
					'author' => $this->author,
					'authors' => $authors,
					'commentID' => $this->additionalData['commentID'],
					'commentUser' => $commentUser,
					'count' => $count,
					'others' => $count - 1,
					'poi' => $poi,
					'responseID' => $this->getUserNotificationObject()->objectID,
					'reactions' => $this->getReactionsForAuthors()
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('poi.poi.commentResponse.like.notification.message', [
				'author' => $this->author,
				'commentID' => $this->additionalData['commentID'],
				'poi' => $poi,
				'responseID' => $this->getUserNotificationObject()->objectID,
				'reactions' => $this->getReactionsForAuthors()
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') { /* not supported */ }
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		$poi = PoiDataHandler::getInstance()->getPoi($this->additionalData['objectID']);
		
		return LinkHandler::getInstance()->getLink('Poi', [
				'application' => 'poi',
				'object' => $poi
		], '#comment' . $this->additionalData['commentID'] . '/response' . $this->getUserNotificationObject()->objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->additionalData['commentID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function supportsEmailNotification() {
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkAccess() {
		return PoiDataHandler::getInstance()->getPoi($this->additionalData['objectID'])->canRead();
	}
}
