<?php
namespace poi\page;
use poi\data\poi\UnreadPoiList;
use wcf\system\WCF;

/**
 * Shows the list of unread pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class UnreadPoiListPage extends PoiListPage {
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = UnreadPoiList::class;
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'poiList';
	
	/**
	 * @inheritDoc
	 */
	public $controllerName = 'UnreadPoiList';
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'feedControllerName' => ''
		]);
	}
}
