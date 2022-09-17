<?php
namespace poi\data\cover\photo;
use poi\system\upload\CoverPhotoUploadFileValidationStrategy;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IUploadAction;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\image\ImageHandler;
use wcf\system\upload\UploadFile;
use wcf\system\upload\UploadHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Executes cover photo-related actions.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class CoverPhotoAction extends AbstractDatabaseObjectAction implements IUploadAction {
	/**
	 * @inheritDoc
	 */
	protected $className = CoverPhotoEditor::class;
	
	// upload
	public $uploadFile;
	
	/**
	 * @inheritDoc
	 */
	public function validateUpload() {
		if (!isset($this->parameters['__files']) || count($this->parameters['__files']->getFiles()) != 1) {
			throw new UserInputException('files');
		}
		
		$uploadHandler = $this->parameters['__files'];
		$this->uploadFile = $uploadHandler->getFiles()[0];
		
		$uploadHandler->validateFiles(new CoverPhotoUploadFileValidationStrategy());
	}
	
	/**
	 * @inheritDoc
	 */
	public function upload() {
		if ($this->uploadFile->getValidationErrorType()) {
			return [
					'filesize' => $this->uploadFile->getFilesize(),
					'errorMessage' => WCF::getLanguage()->getDynamicVariable('wcf.image.coverPhoto.upload.error.' . $this->uploadFile->getValidationErrorType(), ['file' => $this->uploadFile]),
					'errorType' => $this->uploadFile->getValidationErrorType()
			];
		}
		
		try {
			// validate size and dimensions / shrink
			$fileLocation = $this->enforceCoverPhotoDimensions($this->uploadFile->getLocation());
		}
		catch (UserInputException $e) {
			return [
					'filesize' => $this->uploadFile->getFilesize(),
					'errorMessage' => WCF::getLanguage()->getDynamicVariable('wcf.image.coverPhoto.upload.error.' . $e->getType(), ['file' => $this->uploadFile]),
					'errorType' => $e->getType()
			];
		}
		
		$coverPhoto = CoverPhotoEditor::create(['userID' => WCF::getUser()->userID, 'time' => TIME_NOW, 'fileExtension' => $this->uploadFile->getFileExtension(), 'fileHash' => StringUtil::getRandomID()]);
		
		$dir = dirname($coverPhoto->getCoverPhotoLocation());
		if (!@file_exists($dir)) {
			FileUtil::makePath($dir);
		}
		
		if (@copy($fileLocation, $coverPhoto->getCoverPhotoLocation())) {
			@unlink($fileLocation);
			
			return [
					'coverPhotoID' => $coverPhoto->coverPhotoID,
					'url' => $coverPhoto->getCoverPhotoUrl()
			];
		}
		else {
			return [
					'filesize' => $this->uploadFile->getFilesize(),
					'errorMessage' => WCF::getLanguage()->getDynamicVariable('wcf.image.coverPhoto.upload.error.uploadFailed', ['file' => $this->uploadFile]),
					'errorType' => 'uploadFailed'
			];
		}
	}
	
	/**
	 * Enforces size and dimensions for the given cover photo.
	 */
	protected function enforceCoverPhotoDimensions($filename) {
		$imageData = getimagesize($filename);
		if ($imageData[0] > CoverPhoto::MAX_WIDTH || $imageData[1] > CoverPhoto::MAX_HEIGHT) {
			try {
				$adapter = ImageHandler::getInstance()->getAdapter();
				$adapter->loadFile($filename);
				$filename = FileUtil::getTemporaryFilename();
				$thumbnail = $adapter->createThumbnail(CoverPhoto::MAX_WIDTH, CoverPhoto::MAX_HEIGHT);
				$adapter->writeImage($thumbnail, $filename);
			}
			catch (SystemException $e) {
				throw new UserInputException('coverPhoto', 'maxSize');
			}
			
			// dimensions
			$imageData = getimagesize($filename);
			if ($imageData[0] < CoverPhoto::MIN_WIDTH || $imageData[1] < CoverPhoto::MIN_HEIGHT) {
				throw new UserInputException('coverPhoto', 'maxSize');
			}
			
			// filesize
			if (@filesize($filename) > WCF::getSession()->getPermission('user.poi.coverPhoto.maxSize')) {
				throw new UserInputException('coverPhoto', 'maxSize');
			}
		}
		
		return $filename;
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		parent::delete();
		
		foreach ($this->objects as $coverPhotoEditor) {
			@unlink($coverPhotoEditor->getCoverPhotoLocation());
		}
	}
}
