<?php
namespace poi\system\user\activity\event;
use poi\data\poi\ViewablePoiList;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\CommentList;
use wcf\data\user\UserList;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for poi comment responses.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiCommentResponseUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @inheritDoc
	 */
	public function prepare(array $events) {
		$responseIDs = [];
		foreach ($events as $event) {
			$responseIDs[] = $event->objectID;
		}
		
		// fetch responses
		$responseList = new CommentResponseList();
		$responseList->setObjectIDs($responseIDs);
		$responseList->readObjects();
		$responses = $responseList->getObjects();
		
		// fetch comments
		$commentIDs = $comments = [];
		foreach ($responses as $response) {
			$commentIDs[] = $response->commentID;
		}
		if (!empty($commentIDs)) {
			$commentList = new CommentList();
			$commentList->setObjectIDs($commentIDs);
			$commentList->readObjects();
			$comments = $commentList->getObjects();
		}
		
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
		
		// fetch users
		$userIDs = $user = [];
		foreach ($comments as $comment) {
			$userIDs[] = $comment->userID;
		}
		if (!empty($userIDs)) {
			$userList = new UserList();
			$userList->setObjectIDs($userIDs);
			$userList->readObjects();
			$users = $userList->getObjects();
		}
		
		// set message
		foreach ($events as $event) {
			if (isset($responses[$event->objectID])) {
				$response = $responses[$event->objectID];
				$comment = $comments[$response->commentID];
				if (isset($pois[$comment->objectID]) && isset($users[$comment->userID])) {
					$poi = $pois[$comment->objectID];
					
					// check permissions
					if (!$poi->canRead()) {
						continue;
					}
					$event->setIsAccessible();
					
					// title
					$text = WCF::getLanguage()->getDynamicVariable('poi.poi.recentActivity.poiCommentResponse', [
							'commentAuthor' => $users[$comment->userID],
							'commentID' => $comment->commentID,
							'responseID' => $response->responseID,
							'poi' => $poi
					]);
					$event->setTitle($text);
					
					// description
					$event->setDescription($response->getExcerpt());
					continue;
				}
			}
			
			$event->setIsOrphaned();
		}
	}
}
