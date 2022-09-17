<?php
namespace poi\system\option;
use poi\data\poi\Poi;
use poi\system\cache\builder\PoiOptionCacheBuilder;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\option\OptionHandler;

/**
 * Handles poi options.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiOptionHandler extends OptionHandler {
	/**
	 * true if within edit mode
	 */
	public $editMode = true;
	
	/**
	 * current poi
	 */
	public $poi;
	
	/**
	 * Gets cached options.
	 */
	protected function readCache() {
		$this->cachedOptions = PoiOptionCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Initializes options.
	 */
	public function init() {
		if (!$this->didInit) {
			foreach ($this->cachedOptions as $option) {
				if ($this->checkOption($option)) {
					$this->options[$option->optionName] = $option;
				}
			}
			
			// mark as initialized
			$this->didInit = true;
		}
	}
	
	/**
	 * Returns the parsed options.
	 */
	public function getOptions() {
		$parsedOptions = [];
		foreach ($this->options as $option) {
			$parsedOptions[] = $this->getOption($option->optionName);
		}
		
		return $parsedOptions;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		foreach ($this->options as $option) {
			if (!isset($this->optionValues[$option->optionName])) {
				$this->optionValues[$option->optionName] = $option->defaultValue;
			}
		}
	}
	
	/**
	 * Enables edit mode.
	 */
	public function enableEditMode($enable = true) {
		$this->editMode = $enable;
	}
	
	/**
	 * Sets option values for a certain poi.
	 */
	public function setPoi(Poi $poi) {
		$this->optionValues = [];
		$this->poi = $poi;
		
		$this->init();
		foreach ($this->options as $option) {
			$this->optionValues[$option->optionName] = $this->poi->getOptionValue($option->optionID);
		}
	}
	
	/**
	 * Gets the option values.
	 */
	public function getOptionValues() {
		return $this->optionValues;
	}
	
	/**
	 * Resets the option values.
	 */
	public function resetOptionValues() {
		$this->optionValues = [];
	}
	
	/**
	 * Sets the option values.
	 */
	public function setOptionValues(array $values) {
		$this->optionValues = $values;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getOption($optionName) {
		$optionData = parent::getOption($optionName);
		
		if (!$this->editMode && isset($this->optionValues[$optionName])) {
			$optionData['object']->setOptionValue($this->optionValues[$optionName]);
		}
		
		return $optionData;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateOption(Option $option) {
		parent::validateOption($option);
		
		if ($option->required && $option->optionType != 'boolean' && empty($this->optionValues[$option->optionName])) {
			throw new UserInputException($option->optionName);
		}
	}
}
