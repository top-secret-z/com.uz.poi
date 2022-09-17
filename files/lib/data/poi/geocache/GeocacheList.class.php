<?php
namespace poi\data\poi\geocache;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of geocache entries.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class GeocacheList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Geocache::class;
}
