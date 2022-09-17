<?php
namespace poi\data\poi;
use poi\system\POICore;
use wcf\data\edit\history\entry\EditHistoryEntry;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\TUserContent;
use wcf\system\edit\IHistorySavingObject;
use wcf\system\WCF;

/**
 * History saving point implementation for pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class HistorySavingPoi extends DatabaseObjectDecorator implements IHistorySavingObject {
	use TUserContent;
	
	/**
	 * last edit
	 */
	public $reason = '';
	public $time = 0;
	public $userID = 0;
	public $username = '';
	
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Poi::class;
	
	/**
	 * @inheritDoc
	 */
	public function __construct(DatabaseObject $object) {
		parent::__construct($object);
		
		// fetch latest edit
		$objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.modifiableContent', 'com.uz.poi.poi');
		
		$sql = "SELECT		*
				FROM		wcf".WCF_N."_modification_log
				WHERE		objectTypeID = ? AND objectID = ? AND action = ?
				ORDER BY	time DESC";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute([$objectTypeID, $this->getDecoratedObject()->poiID, 'edit']);
		$row = $statement->fetchSingleRow();
		
		if ($row) {
			$this->userID = $row['userID'];
			$this->username = $row['username'];
			$this->time = $row['time'];
			$additionalData = @unserialize($row['additionalData']);
			if (isset($additionalData['reason'])) {
				$this->reason = $additionalData['reason'];
			}
			else {
				$this->reason = '';
			}
		}
		else {
			$this->userID = $this->getDecoratedObject()->getUserID();
			$this->username = $this->getDecoratedObject()->getUsername();
			$this->time = $this->getDecoratedObject()->getTime();
			$this->reason = '';
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEditReason() {
		return $this->reason;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return $this->getDecoratedObject()->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		return $this->getDecoratedObject()->getMessage();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getDecoratedObject()->getTitle();
	}
	
	/**
	 * @inheritDoc
	 */
	public function revertVersion(EditHistoryEntry $edit) {
		$poiAction = new PoiAction([$this->getDecoratedObject()], 'update', [
				'isEdit' => true,
				'data' => [
						'message' => $edit->message
				],
				'editReason' => WCF::getLanguage()->getDynamicVariable('wcf.edit.reverted', ['edit' => $edit])
		]);
		$poiAction->executeAction();
	}
	
	/**
	 * @inheritDoc
	 */
	public function setLocation() {
		POICore::getInstance()->setLocation($this->getCategory()->getParentCategories(), $this->getCategory(), $this->getDecoratedObject());
	}
}
