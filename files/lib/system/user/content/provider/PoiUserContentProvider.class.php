<?php
namespace poi\system\user\content\provider;
use poi\data\poi\Poi;
use wcf\system\user\content\provider\AbstractDatabaseUserContentProvider;

/**
 * User content provider for POIs.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiUserContentProvider extends AbstractDatabaseUserContentProvider {
	/**
	 * @inheritdoc
	 */
	public static function getDatabaseObjectClass() {
		return Poi::class;
	}
}
