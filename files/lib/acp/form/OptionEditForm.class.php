<?php
namespace poi\acp\form;
use poi\data\poi\option\PoiOption;
use poi\data\poi\option\PoiOptionAction;
use wcf\data\package\PackageCache;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the option edit form.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class OptionEditForm extends OptionAddForm {
	/**
	 * option
	 */
	public $optionID = 0;
	public $poiOption;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->optionID = intval($_REQUEST['id']);
		$this->poiOption = new PoiOption($this->optionID);
		if (!$this->poiOption->optionID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		// description
		$this->optionDescription = 'poi.poi.option'.$this->poiOption->optionID.'.description';
		if (I18nHandler::getInstance()->isPlainValue('optionDescription')) {
			I18nHandler::getInstance()->remove($this->optionDescription);
			$this->optionDescription = I18nHandler::getInstance()->getValue('optionDescription');
		}
		else {
			I18nHandler::getInstance()->save('optionDescription', $this->optionDescription, 'poi.poi', PackageCache::getInstance()->getPackageID('com.uz.poi'));
		}
		
		// update title
		$this->optionTitle = 'poi.poi.option'.$this->poiOption->optionID;
		if (I18nHandler::getInstance()->isPlainValue('optionTitle')) {
			I18nHandler::getInstance()->remove($this->optionTitle);
			$this->optionTitle = I18nHandler::getInstance()->getValue('optionTitle');
		}
		else {
			I18nHandler::getInstance()->save('optionTitle', $this->optionTitle, 'poi.poi', PackageCache::getInstance()->getPackageID('com.uz.poi'));
		}
		
		$this->objectAction = new PoiOptionAction([$this->poiOption], 'update', [
				'data' => array_merge($this->additionalFields, [
						'optionDescription' => $this->optionDescription,
						'optionTitle' => $this->optionTitle,
						'optionType' => $this->optionType,
						'defaultValue' => $this->defaultValue,
						'required' => $this->required,
						'selectOptions' => $this->selectOptions,
						'showOrder' => $this->showOrder,
						'validationPattern' => $this->validationPattern
				])
		]);
		$this->objectAction->executeAction();
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			I18nHandler::getInstance()->setOptions('optionDescription', PackageCache::getInstance()->getPackageID('com.uz.poi'), $this->poiOption->optionDescription, 'poi.poi.option\d+.description');
			I18nHandler::getInstance()->setOptions('optionTitle', PackageCache::getInstance()->getPackageID('com.uz.poi'), $this->poiOption->optionTitle, 'poi.poi.option\d+');
			
			$this->defaultValue = $this->poiOption->defaultValue;
			$this->optionType = $this->poiOption->optionType;
			$this->required = $this->poiOption->required;
			$this->selectOptions = $this->poiOption->selectOptions;
			$this->showOrder = $this->poiOption->showOrder;
			$this->validationPattern = $this->poiOption->validationPattern;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign([
				'action' => 'edit',
				'optionID' => $this->optionID,
				'poiOption' => $this->poiOption
		]);
	}
}
