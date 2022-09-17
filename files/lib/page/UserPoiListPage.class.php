<?php
namespace poi\page;
use wcf\data\user\User;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of pois by a certain user.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class UserPoiListPage extends PoiListPage {
	/**
	 * @inheritDoc
	 */
	public $templateName = 'poiList';
	
	/**
	 * poi user
	 */
	public $user;
	public $userID = 0;
	
	/**
	 * @inheritDoc
	 */
	public $controllerName = 'UserPoiList';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		if (isset($_REQUEST['id'])) $this->userID = intval($_REQUEST['id']);
		$this->user = new User($this->userID);
		if (!$this->user->userID) {
			throw new IllegalLinkException();
		}
		$this->controllerParameters['object'] = $this->user;
		parent::readParameters();
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('UserPoiList', [
				'application' => 'poi',
				'object' => $this->user
		], ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : ''));
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add('poi.userID = ?', [$this->userID]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'userID' => $this->userID,
				'user' => $this->user,
				'feedControllerName' => '',
				'controllerObject' => $this->user
		]);
	}
}
