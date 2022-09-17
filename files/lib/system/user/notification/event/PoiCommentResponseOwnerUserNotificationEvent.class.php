<?php
namespace poi\system\user\notification\event;
use poi\system\poi\PoiDataHandler;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\email\Email;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;

/**
 * User notification event for poi owner for commment responses.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiCommentResponseOwnerUserNotificationEvent extends AbstractSharedUserNotificationEvent {
	/**
	 * @inheritDoc
	 */
	protected $stackable = true;
	
	/**
	 * @inheritDoc
	 */
	protected function prepare() {
		PoiDataHandler::getInstance()->cachePoiID($this->additionalData['objectID']);
		CommentRuntimeCache::getInstance()->cacheObjectID($this->getUserNotificationObject()->commentID);
		UserProfileRuntimeCache::getInstance()->cacheObjectID($this->additionalData['userID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('poi.poi.commentResponseOwner.notification.title.stacked', [
					'count' => $count,
					'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->get('poi.poi.commentResponseOwner.notification.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$comment = CommentRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->commentID);
		if ($comment->userID) {
			$commentAuthor = UserProfileRuntimeCache::getInstance()->getObject($comment->userID);
		}
		else {
			$commentAuthor = UserProfile::getGuestUserProfile($comment->username);
		}
		$poi = PoiDataHandler::getInstance()->getPoi($comment->objectID);
		
		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);
			
			return $this->getLanguage()->getDynamicVariable('poi.poi.commentResponseOwner.notification.message.stacked', [
					'author' => $commentAuthor,
					'authors' => array_values($authors),
					'commentID' => $this->getUserNotificationObject()->commentID,
					'count' => $count,
					'poi' => $poi,
					'others' => $count - 1,
					'guestTimesTriggered' => $this->notification->guestTimesTriggered
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('poi.poi.commentResponseOwner.notification.message', [
				'poi' => $poi,
				'author' => $this->author,
				'commentAuthor' => $commentAuthor,
				'commentID' => $this->getUserNotificationObject()->commentID,
				'responseID' => $this->getUserNotificationObject()->responseID
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		$comment = CommentRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->commentID);
		if ($comment->userID) {
			$commentAuthor = UserProfileRuntimeCache::getInstance()->getObject($comment->userID);
		}
		else {
			$commentAuthor = UserProfile::getGuestUserProfile($comment->username);
		}
		$poi = PoiDataHandler::getInstance()->getPoi($comment->objectID);
		
		$messageID = '<com.uz.poi.poi.comment/'.$comment->commentID.'@'.Email::getHost().'>';
		
		return [
				'template' => 'email_notification_commentResponseOwner',
				'application' => 'wcf',
				'in-reply-to' => [$messageID],
				'references' => [$messageID],
				'variables' => [
						'commentAuthor' => $commentAuthor,
						'commentID' => $this->getUserNotificationObject()->commentID,
						'poi' => $poi,
						'responseID' => $this->getUserNotificationObject()->responseID,
						'languageVariablePrefix' => 'poi.poi.commentResponseOwner.notification'
				]
		];
		}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		$poi = PoiDataHandler::getInstance()->getPoi($this->additionalData['objectID']);
		
		return LinkHandler::getInstance()->getLink('Poi', [
				'application' => 'poi',
				'object' => $poi
		], '#comment' . $this->getUserNotificationObject()->commentID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->getUserNotificationObject()->commentID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkAccess() {
		return PoiDataHandler::getInstance()->getPoi($this->additionalData['objectID'])->canRead();
	}
}
