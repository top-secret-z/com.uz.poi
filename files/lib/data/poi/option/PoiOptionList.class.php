<?php
namespace poi\data\poi\option;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of poi options.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiOptionList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = PoiOption::class;
}
