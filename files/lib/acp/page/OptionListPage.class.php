<?php
namespace poi\acp\page;
use poi\data\poi\option\PoiOptionList;
use wcf\page\SortablePage;

/**
 * Shows the list of options.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class OptionListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'poi.acp.menu.link.poi.option.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.poi.canManageOption'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'showOrder';
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = PoiOptionList::class;
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['optionID', 'optionTitle', 'optionType', 'showOrder', 'required'];
}
