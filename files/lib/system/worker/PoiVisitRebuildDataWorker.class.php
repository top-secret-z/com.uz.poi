<?php
namespace poi\system\worker;
use poi\data\poi\PoiEditor;
use poi\data\poi\PoiList;
use wcf\system\worker\AbstractRebuildDataWorker;

/**
 * Worker implementation for updating poi visits.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiVisitRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @inheritDoc
	 */
	protected $objectListClassName = PoiList::class;
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 50;
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlOrderBy = 'poi.poiID';
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		if (!count($this->objectList)) {
			return;
		}
		
		foreach ($this->objectList as $poi) {
			$editor = new PoiEditor($poi);
			$visits = $poi->getVisits();
			
			$editor->update(['visits' => $visits]);
		}
	}
}
