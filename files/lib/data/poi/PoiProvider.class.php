<?php
namespace poi\data\poi;
use wcf\data\object\type\AbstractObjectTypeProvider;

/**
 * Object type provider implementation for pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiProvider extends AbstractObjectTypeProvider {
	/**
	 * @inheritDoc
	 */
	public $className = Poi::class;
	
	/**
	 * @inheritDoc
	 */
	public $listClassName = PoiList::class;
}
