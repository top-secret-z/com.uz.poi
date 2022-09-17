<?php
namespace poi\system\option;
use poi\data\category\PoiCategoryNodeTree;
use wcf\system\option\AbstractCategoryMultiSelectOptionType;

/**
 * Option type implementation for selecting multiple poi categories.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiCategoryMultiSelectOptionType extends AbstractCategoryMultiSelectOptionType {
	/**
	 * @inheritDoc
	 */
	public $objectType = 'com.uz.poi.category';
	
	/**
	 * @inheritDoc
	 */
	public $nodeTreeClassname = PoiCategoryNodeTree::class;
}
