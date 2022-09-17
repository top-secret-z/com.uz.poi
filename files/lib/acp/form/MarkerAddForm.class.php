<?php
namespace poi\acp\form;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the marker add form.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class MarkerAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'poi.acp.menu.link.poi.marker.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.poi.canManageCategory'];
	
	/**
	 * data of the uploaded marker file
	 */
	public $fileUpload = [];
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_FILES['fileUpload'])) $this->fileUpload = $_FILES['fileUpload'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// uploaded?
		if (empty($this->fileUpload['name'])) {
			throw new UserInputException('fileUpload');
		}
		
		// ASCII, no space
		if (!StringUtil::isASCII($this->fileUpload['name']) || strpos($this->fileUpload['name'], ' ') !== false) {
			throw new UserInputException('fileUpload', 'name');
		}
		
		// basic marker check
		$size = getimagesize($this->fileUpload['tmp_name']);
		
		if (!$size) {
			throw new UserInputException('fileUpload', 'noImage');
		}
		
		// size check; max 100 / 200 (0 = width, 1 = height)
		if ($size[0] > 100 || $size[1] > 200) {
			throw new UserInputException('fileUpload', 'tooBig');
		}
		
		// exists?
		if (file_exists(POI_DIR.'images/marker/'.$this->fileUpload['name'])) {
			throw new UserInputException('fileUpload', 'exists');
		}
		
		// move
		if (!@move_uploaded_file($this->fileUpload['tmp_name'], POI_DIR.'images/marker/'.$this->fileUpload['name'])) {
			throw new UserInputException('fileUpload', 'uploadFailed');
		}
	}
}