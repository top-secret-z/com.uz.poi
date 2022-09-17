<?php
namespace poi\system\user\notification\event;
use poi\system\poi\PoiDataHandler;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;

/**
 * User notification event for poi commments.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiCommentUserNotificationEvent extends AbstractSharedUserNotificationEvent {
	/**
	 * @inheritDoc
	 */
	protected $stackable = true;
	
	/**
	 * @inheritDoc
	 */
	protected function prepare() {
		PoiDataHandler::getInstance()->cachePoiID($this->getUserNotificationObject()->objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('poi.poi.comment.notification.title.stacked', [
					'count' => $count,
					'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->get('poi.poi.comment.notification.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$poi = PoiDataHandler::getInstance()->getPoi($this->getUserNotificationObject()->objectID);
		
		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);
			
			return $this->getLanguage()->getDynamicVariable('poi.poi.comment.notification.message.stacked', [
					'author' => $this->author,
					'authors' => array_values($authors),
					'commentID' => $this->getUserNotificationObject()->commentID,
					'count' => $count,
					'poi' => $poi,
					'others' => $count - 1,
					'guestTimesTriggered' => $this->notification->guestTimesTriggered
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('poi.poi.comment.notification.message', [
				'poi' => $poi,
				'author' => $this->author,
				'commentID' => $this->getUserNotificationObject()->commentID
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		return [
				'message-id' => 'com.uz.poi.poi.comment/'.$this->getUserNotificationObject()->commentID,
				'template' => 'email_notification_comment',
				'application' => 'wcf',
				'variables' => [
						'commentID' => $this->getUserNotificationObject()->commentID,
						'poi' => PoiDataHandler::getInstance()->getPoi($this->getUserNotificationObject()->objectID),
						'languageVariablePrefix' => 'poi.poi.comment.notification'
				]
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		$poi = PoiDataHandler::getInstance()->getPoi($this->getUserNotificationObject()->objectID);
		
		return LinkHandler::getInstance()->getLink('Poi', [
				'application' => 'poi',
				'object' => $poi
		], '#comments');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->getUserNotificationObject()->objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkAccess() {
		return PoiDataHandler::getInstance()->getPoi($this->getUserNotificationObject()->objectID)->canRead();
	}
}
