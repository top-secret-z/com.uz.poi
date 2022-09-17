<?php
namespace poi\system\clipboard\action;
use poi\data\poi\Poi;
use poi\data\poi\PoiAction;
use wcf\data\clipboard\action\ClipboardAction;
use wcf\system\clipboard\action\AbstractClipboardAction;
use wcf\system\WCF;

/**
 * Prepares clipboard editor items for pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiClipboardAction extends AbstractClipboardAction {
	/**
	 * @inheritDoc
	 */
	protected $actionClassActions = ['delete', 'enable', 'disable', 'restore', 'trash'];
	
	/**
	 * @inheritDoc
	 */
	protected $supportedActions = ['delete', 'enable', 'disable', 'restore', 'trash'];
	
	/**
	 * list of active poi objects
	 */
	protected $pois = [];
	
	/**
	 * @inheritDoc
	 */
	public function execute(array $objects, ClipboardAction $action) {
		$this->pois = $objects;
		
		$item = parent::execute($objects, $action);
		if ($item === null) {
			return null;
		}
		
		// handle actions
		switch ($action->actionName) {
			case 'trash':
				$item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.com.uz.poi.poi.trash.confirmMessage', [
						'count' => $item->getCount()
				]));
				$item->addInternalData('template', WCF::getTPL()->fetch('poiDeleteReason', 'poi'));
			break;
			
			case 'delete':
				$item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.com.uz.poi.poi.delete.confirmMessage', [
						'count' => $item->getCount()
				]));
			break;
		}
		
		return $item;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getClassName() {
		return PoiAction::class;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTypeName() {
		return 'com.uz.poi.poi';
	}
	
	/**
	 * Validates pois valid for disabling / enabling and returns their ids.
	 */
	public function validateDisable() {
		$poiIDs = [];
		
		foreach ($this->pois as $poi) {
			if (!$poi->isDisabled && !$poi->isDeleted && WCF::getSession()->getPermission('mod.poi.canModeratePoi')) {
				$poiIDs[] = $poi->poiID;
			}
		}
		
		return $poiIDs;
	}
	
	public function validateEnable() {
		$poiIDs = [];
		
		foreach ($this->pois as $poi) {
			if ($poi->isDisabled && WCF::getSession()->getPermission('mod.poi.canModeratePoi')) {
				$poiIDs[] = $poi->poiID;
			}
		}
		
		return $poiIDs;
	}
	
	/**
	 * Validates pois valid for deleting / trashing / restoring and returns their ids.
	 */
	
	public function validateDelete() {
		$poiIDs = [];
		
		foreach ($this->pois as $poi) {
			if ($poi->isDeleted && WCF::getSession()->getPermission('mod.poi.canDeletePoiCompletely')) {
				$poiIDs[] = $poi->poiID;
			}
		}
		
		return $poiIDs;
	}
	
	public function validateTrash() {
		$poiIDs = [];
		
		foreach ($this->pois as $poi) {
			if (!$poi->isDeleted && WCF::getSession()->getPermission('mod.poi.canDeletePoi')) {
				$poiIDs[] = $poi->poiID;
			}
		}
		
		return $poiIDs;
	}
	
	public function validateRestore() {
		$poiIDs = [];
		
		foreach ($this->pois as $poi) {
			if ($poi->isDeleted && WCF::getSession()->getPermission('mod.poi.canRestorePoi')) {
				$poiIDs[] = $poi->poiID;
			}
		}
		
		return $poiIDs;
	}
}
