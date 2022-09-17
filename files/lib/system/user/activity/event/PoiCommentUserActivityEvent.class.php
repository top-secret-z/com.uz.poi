<?php
namespace poi\system\user\activity\event;
use poi\data\poi\ViewablePoiList;
use wcf\data\comment\CommentList;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for poi comments.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiCommentUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @inheritDoc
	 */
	public function prepare(array $events) {
		$comentIDs = [];
		foreach ($events as $event) {
			$comentIDs[] = $event->objectID;
		}
		
		// fetch comments
		$commentList = new CommentList();
		$commentList->setObjectIDs($comentIDs);
		$commentList->readObjects();
		$comments = $commentList->getObjects();
		
		// fetch pois
		$poiIDs = $pois = [];
		foreach ($comments as $comment) {
			$poiIDs[] = $comment->objectID;
		}
		if (!empty($poiIDs)) {
			$poiList = new ViewablePoiList();
			$poiList->setObjectIDs($poiIDs);
			$poiList->readObjects();
			$pois = $poiList->getObjects();
		}
		
		// set message
		foreach ($events as $event) {
			if (isset($comments[$event->objectID])) {
				$comment = $comments[$event->objectID];
				if (isset($pois[$comment->objectID])) {
					$poi = $pois[$comment->objectID];
					
					// check permissions
					if (!$poi->canRead()) {
						continue;
					}
					$event->setIsAccessible();
					
					// add title
					$text = WCF::getLanguage()->getDynamicVariable('poi.poi.recentActivity.poiComment', [
							'commentID' => $comment->commentID,
							'poi' => $poi
					]);
					$event->setTitle($text);
					
					// add text
					$event->setDescription($comment->getExcerpt());
					continue;
				}
			}
			
			$event->setIsOrphaned();
		}
	}
}
