<?php
namespace poi\page;
use poi\data\poi\FeedPoiList;
use wcf\page\AbstractFeedPage;
use wcf\system\WCF;

/**
 * Shows pois in feed.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiListFeedPage extends AbstractFeedPage {
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.poi.canViewPoi'];
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// read the pois
		$this->items = new FeedPoiList();
		$this->items->sqlLimit = 20;
		$this->items->readObjects();
		$this->title = WCF::getLanguage()->get('poi.poi.pois');
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'supportsEnclosure' => true
		]);
	}
}
