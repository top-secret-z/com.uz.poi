<?php
namespace poi\data\poi;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\TUserContent;
use wcf\system\infraction\warning\IWarnableObject;

/**
 * Warnable object implementation for poi pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class WarnablePoi extends DatabaseObjectDecorator implements IWarnableObject {
	use TUserContent;
	
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Poi::class;
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return $this->getDecoratedObject()->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectID() {
		return $this->poiID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getDecoratedObject()->getTitle();
	}
}
