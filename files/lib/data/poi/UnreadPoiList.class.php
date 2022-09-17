<?php
namespace poi\data\poi;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Represents a list of unread pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class UnreadPoiList extends AccessiblePoiList {
	/**
	 * Creates a new UnreadPoiList object.
	 */
	public function __construct() {
		parent::__construct();
		
		$this->sqlConditionJoins .= " LEFT JOIN wcf".WCF_N."_tracked_visit tracked_visit ON (tracked_visit.objectTypeID = ".VisitTracker::getInstance()->getObjectTypeID('com.uz.poi.poi')." AND tracked_visit.objectID = poi.poiID AND tracked_visit.userID = ".WCF::getUser()->userID.")";
		$this->getConditionBuilder()->add('poi.lastChangeTime > ?', [VisitTracker::getInstance()->getVisitTime('com.uz.poi.poi')]);
		$this->getConditionBuilder()->add('(poi.lastChangeTime > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)');
	}
}
