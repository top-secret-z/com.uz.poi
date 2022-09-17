<?php
namespace poi\data\cover\photo;
use poi\data\poi\Poi;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit cover photos.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class CoverPhotoEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = CoverPhoto::class;
	
	/**
	 * Assigns the cover photo to Poi.
	 */
	public function assignToPoi(Poi $poi) {
		$this->update(['poiID' => $poi->poiID, 'userID' => null]);
	}
}
