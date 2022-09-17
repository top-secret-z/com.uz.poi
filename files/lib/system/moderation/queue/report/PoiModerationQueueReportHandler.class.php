<?php
namespace poi\system\moderation\queue\report;
use poi\data\poi\ViewablePoi;
use poi\system\moderation\queue\AbstractPoiModerationQueueHandler;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\report\IModerationQueueReportHandler;
use wcf\system\WCF;

/**
 * An implementation of IModerationQueueReportHandler for pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiModerationQueueReportHandler extends AbstractPoiModerationQueueHandler implements IModerationQueueReportHandler {
	/**
	 * @inheritDoc
	 */
	protected $definitionName = 'com.woltlab.wcf.moderation.report';
	
	/**
	 * @inheritDoc
	 */
	protected $objectType = 'com.uz.poi.poi';
	
	/**
	 * @inheritDoc
	 */
	public function canReport($objectID) {
		if (!$this->isValid($objectID)) {
			return false;
		}
		
		if (!$this->getPoi($objectID)->canRead()) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getReportedContent(ViewableModerationQueue $queue) {
		return WCF::getTPL()->fetch('moderationPoi', 'poi', [
				'poi' => new ViewablePoi($queue->getAffectedObject())
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getReportedObject($objectID) {
		if ($this->isValid($objectID)) {
			return $this->getPoi($objectID);
		}
		
		return null;
	}
}
