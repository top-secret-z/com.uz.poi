<?php
namespace poi\system\box;
use wcf\data\user\UserProfileList;
use wcf\system\box\AbstractBoxController;
use wcf\system\WCF;

/**
 * Box for most active authors.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class MostActiveAuthorsBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected static $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		$userProfileList = new UserProfileList();
		$userProfileList->getConditionBuilder()->add('user_table.poiPois > ?', [0]);
		$userProfileList->sqlOrderBy = 'poiPois DESC';
		$userProfileList->sqlLimit = 5;
		$userProfileList->readObjects();
		
		if (count($userProfileList)) {
			$this->content = WCF::getTPL()->fetch('boxMostActiveAuthors', 'poi', ['mostActiveAuthors' => $userProfileList], true);
		}
	}
}
