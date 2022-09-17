<?php
namespace poi\page;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the list of pois by the active user.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class MyPoiListPage extends PoiListPage {
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
	public $controllerName = 'MyPoiList';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('MyPoiList', ['application' => 'poi'], ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : ''));
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add('poi.userID = ?', [WCF::getUser()->userID]);
	}
	
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
