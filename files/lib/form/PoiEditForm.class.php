<?php
namespace poi\form;
use poi\data\cover\photo\CoverPhoto;
use poi\data\cover\photo\CoverPhotoAction;
use poi\data\poi\Poi;
use poi\data\poi\PoiAction;
use poi\system\POICore;
use wcf\form\MessageForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\tagging\TagEngine;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the poi edit form.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiEditForm extends PoiAddForm {
	/**
	 * poi id
	 */
	public $poiID = 0;
	
	/**
	 * poi object
	 */
	public $poi;
	
	/**
	 * edit reason
	 */
	public $editReason = '';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		if (isset($_REQUEST['id'])) $this->poiID = intval($_REQUEST['id']);
		$this->poi = new Poi($this->poiID);
		if (!$this->poi->poiID) {
			throw new IllegalLinkException();
		}
		
		parent::readParameters();
		
		// set attachment object id
		$this->attachmentObjectID = $this->poi->poiID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['editReason'])) $this->editReason = StringUtil::trim($_POST['editReason']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkPermissions() {
		parent::checkPermissions();
		
		if (!$this->poi->canEdit()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		MessageForm::save();
		
		// get options
		$options = $this->optionHandler->save();
		
		// save poi
		$data = array_merge($this->additionalFields, [
				'languageID' => $this->languageID,
				'subject' => $this->subject,
				'teaser' => $this->teaser,
				'message' => $this->text,
				'categoryID' => $this->categoryID,
				'location' => $this->geocode,
				'latitude' => $this->latitude,
				'longitude' => $this->longitude,
				'elevation' => $this->elevation,
				'coverPhotoID' => $this->coverPhotoID ?: null
		]);
		
		if (WCF::getSession()->getPermission('user.poi.canDisableCommentFunction')) {
			$data['enableComments'] = $this->enableComments;
		}
		
		$poiData = [
				'isEdit' => true,
				'data' => $data,
				'htmlInputProcessor' => $this->htmlInputProcessor,
				'attachmentHandler' => $this->attachmentHandler,
				'editReason' => $this->editReason,
				'options' => $options
		];
		
		if (MODULE_TAGGING && WCF::getSession()->getPermission('user.tag.canViewTag')) {
			$poiData['tags'] = $this->tags;
		}
		
		$this->objectAction = new PoiAction([$this->poi], 'update', $poiData);
		$this->objectAction->executeAction();
		$this->saved();
		
		// cover photo
		if ($this->poi->coverPhotoID !== $this->coverPhotoID) {
			$action = new CoverPhotoAction([$this->poi->coverPhotoID], 'delete');
			$action->executeAction();
		}
		
		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('Poi', [
				'application' => 'poi',
				'object' => $this->poi
		]));
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			$this->subject = $this->poi->subject;
			$this->teaser = $this->poi->teaser;
			$this->text = $this->poi->message;
			if ($this->poi->languageID) $this->languageID = $this->poi->languageID;
			$this->categoryID = $this->poi->categoryID;
			$this->geocode = $this->poi->location;
			$this->latitude = $this->poi->latitude;
			$this->longitude = $this->poi->longitude;
			$this->elevation = $this->poi->elevation;
			
			// tagging
			if (MODULE_TAGGING && WCF::getSession()->getPermission('user.tag.canViewTag')) {
				$tags = TagEngine::getInstance()->getObjectTags('com.uz.poi.poi', $this->poi->poiID, [$this->poi->languageID]);
				foreach ($tags as $tag) {
					$this->tags[] = $tag->name;
				}
			}
			
			// cover photo
			$this->coverPhotoID = $this->poi->coverPhotoID;
			if ($this->coverPhotoID) {
				$this->coverPhoto = new CoverPhoto($this->coverPhotoID);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function setLocation() {
		POICore::getInstance()->setLocation($this->poi->getCategory()->getParentCategories(), $this->poi->getCategory(), $this->poi);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'action' => 'edit',
				'editReason' => $this->editReason,
				'poiID' => $this->poiID,
				'poi' => $this->poi
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initOptionHandler() {
		$this->optionHandler->setPoi($this->poi);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateCoverPhoto() {
		if ($this->coverPhoto->coverPhotoID != $this->poi->coverPhotoID) {
			parent::validateCoverPhoto();
		}
	}
}
