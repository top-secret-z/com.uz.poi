<?php
namespace poi\acp\form;
use poi\data\poi\option\PoiOption;
use poi\data\poi\option\PoiOptionAction;
use poi\data\poi\option\PoiOptionEditor;
use wcf\data\package\PackageCache;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the option add form.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class OptionAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'poi.acp.menu.link.poi';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.poi.canManageOption'];
	
	/**
	 * option data
	 */
	public $optionTitle = '';
	public $optionDescription = '';
	public $optionType = 'text';
	public $defaultValue = '';
	public $validationPattern = '';
	public $selectOptions = '';
	public $required = 0;
	public $showOrder = 0;
	
	/**
	 * available option types
	 */
	public static $availableOptionTypes = [
			'boolean',
			'checkboxes',
			'date',
			'integer',
			'float',
			'multiSelect',
			'radioButton',
			'select',
			'text',
			'textarea',
			'URL'
	];
	
	/**
	 * list of option type that require select options
	 */
	public static $optionTypesUsingSelectOptions = [
			'checkboxes',
			'multiSelect',
			'radioButton',
			'select'
	];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('optionTitle');
		I18nHandler::getInstance()->register('optionDescription');
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (I18nHandler::getInstance()->isPlainValue('optionTitle')) $this->optionTitle = I18nHandler::getInstance()->getValue('optionTitle');
		if (I18nHandler::getInstance()->isPlainValue('optionDescription')) $this->optionDescription = I18nHandler::getInstance()->getValue('optionDescription');
		
		if (isset($_POST['optionType'])) $this->optionType = $_POST['optionType'];
		if (isset($_POST['defaultValue'])) $this->defaultValue = $_POST['defaultValue'];
		if (isset($_POST['validationPattern'])) $this->validationPattern = $_POST['validationPattern'];
		if (isset($_POST['selectOptions'])) $this->selectOptions = $_POST['selectOptions'];
		if (isset($_POST['required'])) $this->required = intval($_POST['required']);
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
		
		if ($this->optionType == 'boolean' || $this->optionType == 'integer') $this->defaultValue = intval($this->defaultValue);
		if ($this->optionType == 'float') $this->defaultValue = floatval($this->defaultValue);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// title
		if (!I18nHandler::getInstance()->validateValue('optionTitle')) {
			if (I18nHandler::getInstance()->isPlainValue('optionTitle')) {
				throw new UserInputException('optionTitle');
			}
			else {
				throw new UserInputException('optionTitle', 'multilingual');
			}
		}
		
		// description
		if (!I18nHandler::getInstance()->validateValue('optionDescription', false, true)) {
			throw new UserInputException('optionDescription');
		}
		
		// type
		if (!in_array($this->optionType, self::$availableOptionTypes)) {
			throw new UserInputException('optionType');
		}
		
		// select options
		if (in_array($this->optionType, self::$optionTypesUsingSelectOptions) && empty($this->selectOptions)) {
			throw new UserInputException('selectOptions');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new PoiOptionAction([], 'create', [
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
		$returnValues = $this->objectAction->executeAction();
		
		// save i18n values
		$this->saveI18nValue($returnValues['returnValues'], 'optionTitle');
		$this->saveI18nValue($returnValues['returnValues'], 'optionDescription');
		$this->saved();
		
		// reset values
		$this->optionTitle = $this->optionDescription = $this->optionType = $this->defaultValue = $this->validationPattern = $this->selectOptions = '';
		$this->optionType = 'text';
		$this->required = $this->showOrder = 0;
		I18nHandler::getInstance()->reset();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * Saves i18n values.
	 */
	public function saveI18nValue(PoiOption $poiOption, $columnName) {
		if (!I18nHandler::getInstance()->isPlainValue($columnName)) {
			I18nHandler::getInstance()->save($columnName, 'poi.poi.option'.$poiOption->optionID.($columnName == 'description' ? '.description' : ''), 'poi.poi', PackageCache::getInstance()->getPackageID('com.uz.poi'));
			
			$editor = new PoiOptionEditor($poiOption);
			$editor->update([
				$columnName => 'poi.poi.option'.$poiOption->optionID.($columnName == 'description' ? '.description' : '')
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign([
				'action' => 'add',
				'availableOptionTypes' => self::$availableOptionTypes,
				'defaultValue' => $this->defaultValue,
				'optionType' => $this->optionType,
				'optionTypesUsingSelectOptions' => self::$optionTypesUsingSelectOptions,
				'required' => $this->required,
				'selectOptions' => $this->selectOptions,
				'showOrder' => $this->showOrder,
				'validationPattern' => $this->validationPattern
		]);
	}
}
