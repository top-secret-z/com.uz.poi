<?php
namespace poi\data\poi;

/**
 * Object type provider for poi pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class WarnablePoiProvider extends PoiProvider {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = WarnablePoi::class;
}
