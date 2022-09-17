<?php
namespace poi\data\poi;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Poi::class;
}
