<?php
namespace poi\data\poi\geocache;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit geocache entries.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class GeocacheEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Geocache::class;
}
