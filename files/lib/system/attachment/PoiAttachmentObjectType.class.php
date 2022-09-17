<?php
namespace poi\system\attachment;
use poi\data\poi\Poi;
use poi\data\poi\PoiList;
use wcf\system\attachment\AbstractAttachmentObjectType;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Attachment object type implementation for pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class PoiAttachmentObjectType extends AbstractAttachmentObjectType {
	/**
	 * @inheritDoc
	 */
	public function canDelete($objectID) {
		if ($objectID) {
			$poi = new Poi($objectID);
			if ($poi->canEdit()) return true;
		}
		
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canDownload($objectID) {
		if ($objectID) {
			$poi = new Poi($objectID);
			if (!$poi->canRead()) return false;
			
			return WCF::getSession()->getPermission('user.poi.canDownloadAttachment');
		}
		
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canUpload($objectID, $parentObjectID = 0) {
		if (!WCF::getSession()->getPermission('user.poi.canUploadAttachment')) return false;
		
		if ($objectID) {
			$poi = new Poi($objectID);
			if ($poi->canEdit()) return true;
		}
		
		return WCF::getSession()->getPermission('user.poi.canAddPoi');
	}
	
	/**
	 * @inheritDoc
	 */
	public function canViewPreview($objectID) {
		return $this->canDownload($objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAllowedExtensions() {
		return ArrayUtil::trim(explode("\n", WCF::getSession()->getPermission('user.poi.allowedAttachmentExtensions')));
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMaxCount() {
		return WCF::getSession()->getPermission('user.poi.maxAttachmentCount');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMaxSize() {
		return WCF::getSession()->getPermission('user.poi.maxAttachmentSize');
	}
	
	/**
	 * @inheritDoc
	 */
	public function cacheObjects(array $objectIDs) {
		$poiList = new PoiList();
		$poiList->setObjectIDs(array_unique($objectIDs));
		$poiList->readObjects();
		
		foreach ($poiList->getObjects() as $objectID => $object) {
			$this->cachedObjects[$objectID] = $object;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function setPermissions(array $attachments) {
		$poiIDs = [];
		foreach ($attachments as $attachment) {
			$attachment->setPermissions([
					'canDownload' => false,
					'canViewPreview' => false
			]);
			
			if ($this->getObject($attachment->objectID) === null) {
				$poiIDs[] = $attachment->objectID;
			}
		}
		
		if (!empty($poiIDs)) {
			$this->cacheObjects($poiIDs);
		}
		
		foreach ($attachments as $attachment) {
			$poi = $this->getObject($attachment->objectID);
			if ($poi !== null) {
				if (!$poi->canRead()) continue;
				
				$attachment->setPermissions([
						'canDownload' => WCF::getSession()->getPermission('user.poi.canDownloadAttachment'),
						'canViewPreview' => WCF::getSession()->getPermission('user.poi.canDownloadAttachment')
				]);
			}
			else if ($attachment->tmpHash != '' && $attachment->userID == WCF::getUser()->userID) {
				$attachment->setPermissions([
						'canDownload' => true,
						'canViewPreview' => true
				]);
			}
		}
	}
}
