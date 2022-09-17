<?php
namespace poi\system\user\notification\event;
use poi\system\poi\PoiDataHandler;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;
use wcf\system\user\notification\event\TReactionUserNotificationEvent;

/**
 * User notification event for poi likes.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiLikeUserNotificationEvent extends AbstractSharedUserNotificationEvent {
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
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('poi.poi.like.notification.title.stacked', [
					'count' => $count,
					'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('poi.poi.like.notification.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$poi = PoiDataHandler::getInstance()->getPoi($this->additionalData['objectID']);
		$authors = array_values($this->getAuthors());
		$count = count($authors);
		
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('poi.poi.like.notification.message.stacked', [
					'author' => $this->author,
					'authors' => $authors,
					'count' => $count,
					'others' => $count - 1,
					'poi' => $poi,
					'reactions' => $this->getReactionsForAuthors()
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('poi.poi.like.notification.message', [
				'author' => $this->author,
				'poi' => $poi,
				'reactions' => $this->getReactionsForAuthors()
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		$poi = PoiDataHandler::getInstance()->getPoi($this->additionalData['objectID']);
		
		return LinkHandler::getInstance()->getLink('Poi', [
				'application' => 'poi',
				'object' => $poi
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->additionalData['objectID']);
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
