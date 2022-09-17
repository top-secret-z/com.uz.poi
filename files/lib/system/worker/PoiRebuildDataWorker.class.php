<?php
namespace poi\system\worker;
use poi\data\poi\PoiEditor;
use poi\data\poi\PoiList;
use poi\system\option\PoiOptionHandler;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\search\SearchIndexManager;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\worker\AbstractRebuildDataWorker;
use wcf\system\WCF;

/**
 * Worker implementation for updating pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @inheritDoc
	 */
	protected $objectListClassName = PoiList::class;
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 100;
	
	/**
	 * @var HtmlInputProcessor
	 */
	protected $htmlInputProcessor;
	
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
		
		if (!$this->loopCount) {
			// reset activity points
			UserActivityPointHandler::getInstance()->reset('com.uz.poi.activityPointEvent.poi');
			
			// reset search index
			SearchIndexManager::getInstance()->reset('com.uz.poi.poi');
		}
		
		if (!count($this->objectList)) {
			return;
		}
		
		// fetch cumulative likes
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectTypeID = ?", [ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.like.likeableObject', 'com.uz.poi.likeablePoi')]);
		$conditions->add("objectID IN (?)", [$this->objectList->getObjectIDs()]);
		
		$sql = "SELECT	objectID, cumulativeLikes
				FROM	wcf".WCF_N."_like_object
				".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$cumulativeLikes = $statement->fetchMap('objectID', 'cumulativeLikes');
		
		// prepare statements
		$attachmentObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.attachment.objectType', 'com.uz.poi.poi');
		$sql = "SELECT		COUNT(*) AS attachments
				FROM		wcf".WCF_N."_attachment
				WHERE		objectTypeID = ? AND objectID = ?";
		$attachmentStatement = WCF::getDB()->prepareStatement($sql);
		
		$commentObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.comment.commentableContent', 'com.uz.poi.poiComment');
		$sql = "SELECT	COUNT(*) AS comments, SUM(responses) AS responses
				FROM	wcf".WCF_N."_comment
				WHERE	objectTypeID = ? AND objectID = ?";
		$commentStatement = WCF::getDB()->prepareStatement($sql);
		
		$poiIDs = $itemsToUser = [];
		WCF::getDB()->beginTransaction();
		foreach ($this->objectList as $poi) {
			$editor = new PoiEditor($poi);
			$data = [];
			
			// count attachments
			$attachmentStatement->execute([$attachmentObjectType->objectTypeID, $poi->poiID]);
			$row = $attachmentStatement->fetchSingleRow();
			$data['attachments'] = $row['attachments'];
			
			// count comments
			$commentStatement->execute([$commentObjectType->objectTypeID, $poi->poiID]);
			$row = $commentStatement->fetchSingleRow();
			$data['comments'] = $row['comments'] + $row['responses'];
			
			// update cumulative likes
			$data['cumulativeLikes'] = isset($cumulativeLikes[$poi->poiID]) ? $cumulativeLikes[$poi->poiID] : 0;
			
			// update message
			if (!$poi->enableHtml) {
				$this->getHtmlInputProcessor()->process($poi->message, 'com.uz.poi.poi', $poi->poiID, true);
				$data['message'] = $this->getHtmlInputProcessor()->getHtml();
				$data['enableHtml'] = 1;
			}
			else {
				$this->getHtmlInputProcessor()->processEmbeddedContent($poi->message, 'com.uz.poi.poi', $poi->poiID);
			}
			
			if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->getHtmlInputProcessor())) {
				$data['hasEmbeddedObjects'] = 1;
			}
			else {
				$data['hasEmbeddedObjects'] = 0;
			}
			
			$editor->update($data);
			
			if ($poi->userID && !$poi->isDisabled) {
				if (!isset($itemsToUser[$poi->userID])) {
					$itemsToUser[$poi->userID] = 0;
				}
				$itemsToUser[$poi->userID]++;
			}
			
			// update search index
			$optionHandler = new PoiOptionHandler(false);
			$optionHandler->setPoi($poi);
			$optionHandler->enableEditMode(false);
			$options = $optionHandler->getOptions();
			$optionSearch = '';
			if (!empty($options)) {
				foreach($options as $option) {
					$optionSearch .= ' ' . $option['value'];
				}
			}
			
			$message = $poi->message;
			if (!empty($poi->location)) $message .= ' ' . str_replace (',', ' ', $poi->location);
			if (!empty($poi->teaser)) $message .= ' ' . $poi->teaser;
			if (!empty($optionSearch)) $message .= ' ' . $optionSearch;
			
			if (mb_strlen($message) > 10000000) $message = substr($message, 0, 10000000);
			
			SearchIndexManager::getInstance()->set(
				'com.uz.poi.poi',
				$poi->poiID,
				$message,
				$poi->subject,
				$poi->time,
				$poi->userID,
				$poi->username,
				$poi->languageID
			);
			
			$poiIDs[] = $poi->poiID;
		}
		
		WCF::getDB()->commitTransaction();
		
		// update activity points
		UserActivityPointHandler::getInstance()->fireEvents('com.uz.poi.activityPointEvent.poi', $itemsToUser, false);
	}
	
	/**
	 * @return HtmlInputProcessor
	 */
	protected function getHtmlInputProcessor() {
		if ($this->htmlInputProcessor === null) {
			$this->htmlInputProcessor = new HtmlInputProcessor();
		}
		
		return $this->htmlInputProcessor;
	}
}
