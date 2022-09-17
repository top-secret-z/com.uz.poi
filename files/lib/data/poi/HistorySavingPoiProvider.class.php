<?php
namespace poi\data\poi;
use wcf\system\edit\IHistorySavingObject;
use wcf\system\edit\IHistorySavingObjectTypeProvider;
use wcf\system\exception\PermissionDeniedException;

/**
 * Object type provider for history saving point pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class HistorySavingPoiProvider extends PoiProvider implements IHistorySavingObjectTypeProvider {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = HistorySavingPoi::class;
	
	/**
	 * @inheritDoc
	 */
	public function checkPermissions(IHistorySavingObject $object) {
		if (!($object instanceof HistorySavingPoi)) {
			throw new \InvalidArgumentException("Object is no instance of '".self::class."', instance of '".get_class($object)."' given.");
		}
		
		if (!$object->canEdit()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getActivePageMenuItem() {
		return '';
	}
}
