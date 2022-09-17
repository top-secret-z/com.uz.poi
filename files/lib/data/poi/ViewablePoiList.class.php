<?php
namespace poi\data\poi;
use poi\data\modification\log\PoiListModificationLogList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\reaction\ReactionHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Represents a list of viewable pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class ViewablePoiList extends PoiList {
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'poi.lastChangeTime DESC';
	
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = ViewablePoi::class;
	
	/**
	 * load delete notes
	 */
	public $loadDeleteNote = true;
	
	/**
	 * list of modification log entries for pois
	 */
	public $logList;
	
	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct();
		
		if (WCF::getUser()->userID != 0) {
			// last visit time
			if (!empty($this->sqlSelects)) $this->sqlSelects .= ',';
			$this->sqlSelects .= 'tracked_visit.visitTime';
			$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_tracked_visit tracked_visit ON (tracked_visit.objectTypeID = ".VisitTracker::getInstance()->getObjectTypeID('com.uz.poi.poi')." AND tracked_visit.objectID = poi.poiID AND tracked_visit.userID = ".WCF::getUser()->userID.")";
			
			// subscriptions
			if (!empty($this->sqlSelects)) $this->sqlSelects .= ',';
			$this->sqlSelects .= 'user_object_watch.watchID, user_object_watch.notification';
			$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_object_watch user_object_watch ON (user_object_watch.objectTypeID = ".ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', 'com.uz.poi.poi')->objectTypeID." AND user_object_watch.userID = ".WCF::getUser()->userID." AND user_object_watch.objectID = poi.poiID)";
		}
		
		// reactions
		if (!empty($this->sqlSelects)) $this->sqlSelects .= ',';
		$this->sqlSelects .= "like_object.cachedReactions";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_like_object like_object ON (like_object.objectTypeID = ".ReactionHandler::getInstance()->getObjectType('com.uz.poi.likeablePoi')->objectTypeID." AND like_object.objectID = poi.poiID)";
		
		
		// avatars
		if (!empty($this->sqlSelects)) $this->sqlSelects .= ',';
		$this->sqlSelects .= "user_avatar.*, user_table.*";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = poi.userID)";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_avatar user_avatar ON (user_avatar.avatarID = user_table.avatarID)";
		
		if (!WCF::getSession()->getPermission('mod.poi.canViewDeletedPoi')) {
			$this->loadDeleteNote = false;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		parent::readObjects();
		
		if ($this->loadDeleteNote) {
			$objectIDs = [];
			foreach ($this->objects as $object) {
				if ($object->isDeleted) {
					$objectIDs[] = $object->poiID;
				}
			}
			
			// load deletion data
			if (!empty($objectIDs)) {
				$this->logList = new PoiListModificationLogList();
				$this->logList->setPoiData($objectIDs, 'trash');
				$this->logList->readObjects();
				
				foreach ($this->logList as $logEntry) {
					foreach ($this->objects as $object) {
						if ($object->poiID == $logEntry->objectID) {
							$object->setLogEntry($logEntry);
						}
					}
				}
			}
		}
	}
}
