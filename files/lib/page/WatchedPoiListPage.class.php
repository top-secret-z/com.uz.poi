<?php
namespace poi\page;
use poi\data\poi\WatchedPoiList;
use wcf\system\WCF;

/**
 * Shows the list of watched pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class WatchedPoiListPage extends PoiListPage {
	/**
	 * @inheritDoc
	 */
	public $templateName = 'poiList';
	
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = WatchedPoiList::class;
	
	/**
	 * @inheritDoc
	 */
	public $controllerName = 'WatchedPoiList';
	
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
