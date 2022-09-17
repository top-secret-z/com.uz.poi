<?php
namespace poi\data\modification\log;
use poi\system\log\modification\PoiModificationLogHandler;
use wcf\data\modification\log\ModificationLogList;

/**
 * Represents a list of modification logs for poi list page.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiListModificationLogList extends ModificationLogList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = ViewablePoiListPoiModificationLog::class;
	
	/**
	 * Initializes the poi list modification log list.
	 */
	public function setPoiData(array $poiIDs, $action = '') {
		$this->getConditionBuilder()->add("objectTypeID = ?", [PoiModificationLogHandler::getInstance()->getObjectType()->objectTypeID]);
		$this->getConditionBuilder()->add("objectID IN (?)", [$poiIDs]);
		if (!empty($action)) {
			$this->getConditionBuilder()->add("action = ?", [$action]);
		}
	}
}
