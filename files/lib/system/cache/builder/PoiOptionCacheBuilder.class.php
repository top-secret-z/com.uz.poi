<?php
namespace poi\system\cache\builder;
use poi\data\poi\option\PoiOptionList;
use wcf\system\cache\builder\AbstractCacheBuilder;

/**
 * Caches poi options.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiOptionCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$list = new PoiOptionList();
		$list->sqlSelects = "CONCAT('poiOption', CAST(poi_option.optionID AS CHAR)) AS optionName";
		$list->sqlOrderBy = 'showOrder';
		$list->readObjects();
		return $list->getObjects();
	}
}
