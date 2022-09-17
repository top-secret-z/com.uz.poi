<?php
namespace poi\data\poi;
use poi\data\category\PoiCategory;
use poi\data\modification\log\ViewablePoiListPoiModificationLog;
use wcf\data\language\Language;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Represents a viewable poi.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class ViewablePoi extends DatabaseObjectDecorator {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Poi::class;
	
	/**
	 * effective visit time
	 */
	protected $effectiveVisitTime;
	
	/**
	 * modification log object
	 */
	protected $logEntry;
	
	/**
	 * number of unread pois
	 */
	protected static $unreadPois;
	
	/**
	 * user profile object
	 */
	protected $userProfile;
	
	/**
	 * Returns the number of poi comments.
	 */
	public function getComments() {
		return $this->comments;
	}
	
	/**
	 * Returns delete note if applicable.
	 */
	public function getDeleteNote() {
		if ($this->logEntry === null || $this->logEntry->action != 'trash') {
			return '';
		}
		
		return $this->logEntry->__toString();
	}
	
	/**
	 * Returns the language of this poi.
	 */
	public function getLanguage() {
		if ($this->languageID) return LanguageFactory::getInstance()->getLanguage($this->languageID);
		
		return null;
	}
	
	/**
	 * Returns the number of cumulative likes of the poi.
	 */
	public function getCumulativeLikes() {
		return $this->cumulativeLikes;
	}
	
	/**
	 * Returns the user profile object.
	 */
	public function getUserProfile() {
		if ($this->userProfile === null) {
			$this->userProfile = new UserProfile(new User(null, $this->getDecoratedObject()->data));
		}
		
		return $this->userProfile;
	}
	
	/**
	 * Returns the effective visit time.
	 */
	public function getVisitTime() {
		if ($this->effectiveVisitTime === null) {
			if (WCF::getUser()->userID) {
				$this->effectiveVisitTime = max($this->visitTime, VisitTracker::getInstance()->getVisitTime('com.uz.poi.poi'));
			}
			else {
				$this->effectiveVisitTime = max(VisitTracker::getInstance()->getObjectVisitTime('com.uz.poi.poi', $this->poiID), VisitTracker::getInstance()->getVisitTime('com.uz.poi.poi'));
			}
			if ($this->effectiveVisitTime === null) {
				$this->effectiveVisitTime = 0;
			}
		}
		
		return $this->effectiveVisitTime;
	}
	
	/**
	 * Returns true if this poi is new for the active user.
	 */
	public function isNew() {
		if ($this->lastChangeTime > $this->getVisitTime()) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns 1 if the active user has subscribed this poi.
	 */
	public function isSubscribed() {
		return ($this->watchID ? 1 : 0);
	}
	
	/**
	 * Sets modification log entry.
	 */
	public function setLogEntry(ViewablePoiListPoiModificationLog $logEntry) {
		$this->logEntry = $logEntry;
	}
	
	/**
	 * Returns the viewable poi object with the given id.
	 */
	public static function getPoi($poiID) {
		$list = new ViewablePoiList();
		$list->setObjectIDs([$poiID]);
		$list->readObjects();
		
		return $list->search($poiID);
	}
	
	/**
	 * Returns the number of unread pois.
	 */
	public static function getUnreadPois() {
		if (self::$unreadPois === null) {
			self::$unreadPois = 0;
			
			if (WCF::getUser()->userID) {
				$data = UserStorageHandler::getInstance()->getField('poiUnreadPois');
				
				// cache does not exist or is outdated
				if ($data === null) {
					$categoryIDs = PoiCategory::getAccessibleCategoryIDs();
					if (!empty($categoryIDs)) {
						$conditionBuilder = new PreparedStatementConditionBuilder();
						$conditionBuilder->add("poi.categoryID IN (?)", [$categoryIDs]);
						$conditionBuilder->add("poi.lastChangeTime > ?", [VisitTracker::getInstance()->getVisitTime('com.uz.poi.poi')]);
						$conditionBuilder->add("poi.isDisabled = 0 AND poi.isDeleted = 0");
						$conditionBuilder->add("(poi.lastChangeTime > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)");
						
						// apply language filter
						if (POI_ENABLE_MULTILINGUALISM && LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
							$conditionBuilder->add('(poi.languageID IN (?) OR poi.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
						}
						
						$sql = "SELECT		COUNT(*)
								FROM		poi".WCF_N."_poi poi
								LEFT JOIN	wcf".WCF_N."_tracked_visit tracked_visit
								ON		(tracked_visit.objectTypeID = ".VisitTracker::getInstance()->getObjectTypeID('com.uz.poi.poi')." AND tracked_visit.objectID = poi.poiID AND tracked_visit.userID = ".WCF::getUser()->userID.")
								".$conditionBuilder;
						$statement = WCF::getDB()->prepareStatement($sql);
						$statement->execute($conditionBuilder->getParameters());
						self::$unreadPois = $statement->fetchSingleColumn();
					}
					
					// update storage data
					UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'poiUnreadPois', self::$unreadPois);
				}
				else {
					self::$unreadPois = $data;
				}
			}
		}
		
		return self::$unreadPois;
	}
	
	/**
	 * Returns the icon of the poi. UFN -> user avatar.
	 */
	public function getIconTag($size = 64) {
		return $this->getUserProfile()->getAvatar()->getImageTag($size);
	}
	
	/**
	 * Returns the formatted latitude
	 */
	public function getLatitude() {
		return $this->latitude;
	}
	
	/**
	 * Returns the formatted longitude.
	 */
	public function getFormattedLongitude() {
		if ($this->longitude < 0) $reference = 'W';
		else $reference = 'E';
		
		$longitude = abs($this->longitude);
		
		$degrees = floor($longitude);
		$longitude -= $degrees;
		
		$longitude *= 60.0;
		$minutes = floor($longitude);
		$longitude -= $minutes;
		
		$longitude *= 60.0;
		$seconds = round($longitude, 0);
		
		return WCF::getLanguage()->getDynamicVariable('poi.poi.coordinates.longitude', [
				'degrees' => $degrees,
				'minutes' => $minutes,
				'seconds' => $seconds,
				'reference' => $reference
		]);
		
		return '';
	}
	
	/**
	 * Returns the formatted longitude
	 */
	public function getLongitude() {
		return $this->longitude;
	}
	
	/**
	 * Returns the formatted latitude.
	 */
	public function getFormattedLatitude() {
		if ($this->latitude < 0) $reference = 'S';
		else $reference = 'N';
		
		$latitude = abs($this->latitude);
		
		$degrees = floor($latitude);
		$latitude -= $degrees;
		
		$latitude *= 60.0;
		$minutes = floor($latitude);
		$latitude -= $minutes;
		
		$latitude *= 60.0;
		$seconds = round($latitude, 0);
		
		// return result
		return WCF::getLanguage()->getDynamicVariable('poi.poi.coordinates.latitude', [
				'degrees' => $degrees,
				'minutes' => $minutes,
				'seconds' => $seconds,
				'reference' => $reference
		]);
		
		return '';
	}
}
