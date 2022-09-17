<?php
namespace poi\system\sitemap\object;
use poi\data\poi\Poi;
use wcf\data\DatabaseObject;
use wcf\system\sitemap\object\AbstractSitemapObjectObjectType;

/**
 * Poi sitemap implementation.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiSitemapObject extends AbstractSitemapObjectObjectType {
	/**
	 * @inheritDoc
	 */
	public function getObjectClass() {
		return Poi::class;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLastModifiedColumn() {
		return 'time';
	}
	
	/**
	 * @inheritDoc
	 */
	public function canView(DatabaseObject $object) {
		return $object->canRead();
	}
}
