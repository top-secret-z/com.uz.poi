<?php
namespace poi\data\poi\option;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;

/**
 * Executes poi option-related actions.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiOptionAction extends AbstractDatabaseObjectAction implements IToggleAction {
	/**
	 * @inheritDoc
	 */
	protected $className = PoiOptionEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.poi.canManageOption'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.poi.canManageOption'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.poi.canManageOption'];
	
	/**
	 * @inheritDoc
	 */
	public function toggle() {
		foreach ($this->getObjects() as $optionEditor) {
			$optionEditor->update([
				'isDisabled' => 1 - $optionEditor->isDisabled
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateToggle() {
		$this->validateUpdate();
	}
}
