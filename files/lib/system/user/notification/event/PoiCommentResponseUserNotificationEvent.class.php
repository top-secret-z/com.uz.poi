<?php
namespace poi\system\user\notification\event;
use poi\system\poi\PoiDataHandler;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\email\Email;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;

/**
 * User notification event for poi commment responses.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiCommentResponseUserNotificationEvent extends AbstractSharedUserNotificationEvent {
	/**
	 * @inheritDoc
	 */
	protected $stackable = true;
	
	/**
	 * @inheritDoc
	 */
	protected function prepare() {
		PoiDataHandler::getInstance()->cachePoiID($this->additionalData['objectID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('poi.poi.commentResponse.notification.title.stacked', [
					'count' => $count,
					'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->get('poi.poi.commentResponse.notification.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$poi = PoiDataHandler::getInstance()->getPoi($this->additionalData['objectID']);
		
		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);
			
			return $this->getLanguage()->getDynamicVariable('poi.poi.commentResponse.notification.message.stacked', [
					'authors' => array_values($authors),
					'commentID' => $this->getUserNotificationObject()->commentID,
					'count' => $count,
					'poi' => $poi,
					'others' => $count - 1,
					'guestTimesTriggered' => $this->notification->guestTimesTriggered
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('poi.poi.commentResponse.notification.message', [
				'poi' => $poi,
				'author' => $this->author,
				'commentID' => $this->getUserNotificationObject()->commentID,
				'responseID' => $this->getUserNotificationObject()->responseID
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		$comment = CommentRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->commentID);
		$poi = PoiDataHandler::getInstance()->getPoi($this->additionalData['objectID']);
		
		$messageID = '<com.uz.poi.poi.comment/'.$comment->commentID.'@'.Email::getHost().'>';
		
		return [
				'template' => 'email_notification_commentResponse',
				'application' => 'wcf',
				'in-reply-to' => [$messageID],
				'references' => [$messageID],
				'variables' => [
						'commentID' => $this->getUserNotificationObject()->commentID,
						'poi' => $poi,
						'responseID' => $this->getUserNotificationObject()->responseID,
						'languageVariablePrefix' => 'poi.poi.commentResponse.notification'
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
