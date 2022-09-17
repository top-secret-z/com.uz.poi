<?php
namespace poi\data\poi\option;
use poi\system\cache\builder\PoiOptionCacheBuilder;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;

/**
 * Provides functions to edit poi options.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiOptionEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = PoiOption::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		PoiOptionCacheBuilder::getInstance()->reset();
	}
}
