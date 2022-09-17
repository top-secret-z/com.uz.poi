<?php
namespace poi\acp\form;
use wcf\acp\form\AbstractCategoryEditForm;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\StringUtil;

/**
 * Shows the category edit form.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class CategoryEditForm extends AbstractCategoryEditForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'poi.acp.menu.link.poi';
	
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
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->selectedMarker = $this->category->additionalData['marker'];
		}
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
		
		WCF::getTPL()->assign(array(
				'markers' => $this->markers,
				'selectedMarker' => $this->selectedMarker
		));
	}
}
