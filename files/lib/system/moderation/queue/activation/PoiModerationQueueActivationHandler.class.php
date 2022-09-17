<?php
namespace poi\system\moderation\queue\activation;
use poi\data\poi\PoiAction;
use poi\data\poi\ViewablePoi;
use poi\system\moderation\queue\AbstractPoiModerationQueueHandler;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\activation\IModerationQueueActivationHandler;
use wcf\system\WCF;

/**
 * Implementation of IModerationQueueHandler for pois.
  * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiModerationQueueActivationHandler extends AbstractPoiModerationQueueHandler implements IModerationQueueActivationHandler {
	/**
	 * @inheritDoc
	 */
	public function enableContent(ModerationQueue $queue) {
		if ($this->isValid($queue->objectID) && $this->getPoi($queue->objectID)->isDisabled) {
			$objectAction = new PoiAction([$this->getPoi($queue->objectID)], 'enable');
			$objectAction->executeAction();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDisabledContent(ViewableModerationQueue $queue) {
		return WCF::getTPL()->fetch('moderationPoi', 'poi', [
				'poi' => new ViewablePoi($queue->getAffectedObject())
		]);
	}
}
