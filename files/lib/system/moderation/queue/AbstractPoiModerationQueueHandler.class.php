<?php
namespace poi\system\moderation\queue;
use poi\data\poi\Poi;
use poi\data\poi\PoiAction;
use poi\data\poi\PoiList;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\system\moderation\queue\AbstractModerationQueueHandler;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\WCF;

/**
 * An abstract implementation of IModerationQueueHandler for pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
abstract class AbstractPoiModerationQueueHandler extends AbstractModerationQueueHandler {
	/**
	 * @inheritDoc
	 */
	protected $className = Poi::class;
	
	/**
	 * list of poi objects
	 */
	protected static $pois = [];
	
	/**
	 * @inheritDoc
	 */
	protected $requiredPermission = 'mod.poi.canModeratePoi';
	
	/**
	 * @inheritDoc
	 */
	public function assignQueues(array $queues) {
		$assignments = [];
		foreach ($queues as $queue) {
			$assignUser = false;
			if (WCF::getSession()->getPermission('mod.poi.canModeratePoi')) {
				$assignUser = true;
			}
			
			$assignments[$queue->queueID] = $assignUser;
		}
		
		ModerationQueueManager::getInstance()->setAssignment($assignments);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContainerID($objectID) {
		return 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isValid($objectID) {
		if ($this->getPoi($objectID) === null) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns a poi object by poiID or null if poiID is invalid.
	 */
	protected function getPoi($objectID) {
		if (!array_key_exists($objectID, self::$pois)) {
			self::$pois[$objectID] = new Poi($objectID);
			if (!self::$pois[$objectID]->poiID) {
				self::$pois[$objectID] = null;
			}
		}
		
		return self::$pois[$objectID];
	}
	
	/**
	 * @inheritDoc
	 */
	public function populate(array $queues) {
		$objectIDs = [];
		foreach ($queues as $object) {
			$objectIDs[] = $object->objectID;
		}
		
		// fetch pois
		$poiList = new PoiList();
		$poiList->setObjectIDs($objectIDs);
		$poiList->readObjects();
		$pois = $poiList->getObjects();
		
		foreach ($queues as $object) {
			if (isset($pois[$object->objectID])) {
				$object->setAffectedObject($pois[$object->objectID]);
			}
			else {
				$object->setIsOrphaned();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function removeContent(ModerationQueue $queue, $message) {
		if ($this->isValid($queue->objectID) && !$this->getPoi($queue->objectID)->isDeleted) {
			$action = new PoiAction([$this->getPoi($queue->objectID)], 'trash', ['data' => ['reason' => $message]]);
			$action->executeAction();
		}
	}
}
