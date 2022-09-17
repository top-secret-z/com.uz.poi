<?php
namespace poi\acp\form;
use wcf\acp\form\AbstractCategoryAddForm;
use wcf\system\exception\NamedUserException;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\StringUtil;

/**
 * Shows the category add form.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class CategoryAddForm extends AbstractCategoryAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'poi.acp.menu.link.poi.category.add';
	
	/**
	 * @inheritDoc
	 */
	public $objectTypeName = 'com.uz.poi.category';
	
	/**
	 * marker
	 */
	public $markers = [];
	public $selectedMarker = '';
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['selectedMarker'])) $this->selectedMarker = StringUtil::trim($_POST['selectedMarker']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		//not ufn
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		$this->additionalData['marker'] = $this->selectedMarker;
		
		parent::save();
		
		$this->selectedMarker = '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$files = DirectoryUtil::getInstance(POI_DIR.'images/marker/')->getFiles(SORT_ASC);
		if (empty($files)) {
			throw new NamedUserException(WCF::getLanguage()->get('poi.acp.marker.error.noIcons'));
		}
		
		$path = WCF::getPath('poi').'images/marker/';
		$first = '';
		foreach ($files as $file) {
			if (is_dir($file)) continue;
			if (strpos($file, '/marker/search/')) continue;
			
			$name = basename($file);
			if (empty($first)) $first = $name;
			$link = '<img src="'. $path . $name . '" height="30" alt="' . $name . '">';
			$this->markers[$name] = $link;
		}
		
		$this->selectedMarker = $first;
		
		WCF::getTPL()->assign([
				'markers' => $this->markers,
				'selectedMarker' => $this->selectedMarker
		]);
	}
}
