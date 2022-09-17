<?php
namespace poi\acp\page;
use wcf\acp\page\AbstractCategoryListPage;

/**
 * Shows the category list.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class CategoryListPage extends AbstractCategoryListPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'poi.acp.menu.link.poi.category.list';
	
	/**
	 * @inheritDoc
	 */
	public $objectTypeName = 'com.uz.poi.category';
}
