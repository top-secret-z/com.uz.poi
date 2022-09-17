<?php
namespace poi\data\cover\photo;
use wcf\data\DatabaseObject;
use wcf\system\image\cover\photo\ICoverPhotoImage;
use wcf\system\WCF;

/**
 * Represents a cover photo.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class CoverPhoto extends DatabaseObject implements ICoverPhotoImage {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'coverPhotoID';
	
	const MAX_HEIGHT = 400;
	const MAX_WIDTH = 2000;
	const MIN_HEIGHT = 100;
	const MIN_WIDTH = 500;
	
	/**
	 * Returns the relative path to the cover photo.
	 */
	protected function getStorage() {
		$directory = substr($this->fileHash, 0, 2);
		return "images/coverPhotos/{$directory}/{$this->coverPhotoID}-{$this->fileHash}.{$this->fileExtension}";
	}
	
	/**
	 * @inheritDoc
	 */
	public function getCoverPhotoCaption() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getCoverPhotoLocation() {
		return POI_DIR . $this->getStorage();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getCoverPhotoUrl() {
		return WCF::getPath('poi') . $this->getStorage();
	}
}
