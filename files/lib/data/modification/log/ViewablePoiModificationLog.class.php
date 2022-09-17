<?php
namespace poi\data\modification\log;
use poi\data\poi\Poi;
use wcf\data\modification\log\IViewableModificationLog;
use wcf\data\modification\log\ModificationLog;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\WCF;

/**
 * Provides a viewable poi modification log.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class ViewablePoiModificationLog extends DatabaseObjectDecorator implements IViewableModificationLog {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ModificationLog::class;
	
	/**
	 * Poi this modification log belongs to
	 */
	protected $poi;
	
	/**
	 * user profile object
	 */
	protected $userProfile;
	
	/**
	 * Returns readable representation of current log entry.
	 */
	public function __toString() {
		return WCF::getLanguage()->getDynamicVariable('poi.poi.log.poi.'.$this->action, ['additionalData' => $this->additionalData]);
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
	 * Sets the Poi this modification log belongs to.
	 */
	public function setPoi(Poi $poi) {
		if ($poi->poiID == $this->objectID) {
			$this->poi= $poi;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAffectedObject() {
		return $this->poi;
	}
}
