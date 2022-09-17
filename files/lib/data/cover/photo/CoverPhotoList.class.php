<?php
namespace poi\data\cover\photo;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of cover photos.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class CoverPhotoList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = CoverPhoto::class;
}
