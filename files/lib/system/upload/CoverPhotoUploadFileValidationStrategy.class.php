<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace poi\system\upload;

use poi\data\cover\photo\CoverPhoto;
use wcf\system\image\ImageHandler;
use wcf\system\upload\IUploadFileValidationStrategy;
use wcf\system\upload\UploadFile;
use wcf\system\WCF;
use wcf\util\ExifUtil;

/**
 * Upload file validation strategy implementation for Poi cover photos.
 */
class CoverPhotoUploadFileValidationStrategy implements IUploadFileValidationStrategy
{
    /**
     * list of allowed file extensions
     */
    public static $allowedExtensions = ['gif', 'jpg', 'jpeg', 'png'];

    /**
     * @inheritDoc
     */
    public function validate(UploadFile $uploadFile)
    {
        if ($uploadFile->getErrorCode() != 0) {
            $uploadFile->setValidationErrorType('uploadFailed');

            return false;
        }

        // validate file extension
        if (!\in_array($uploadFile->getFileExtension(), self::$allowedExtensions)) {
            $uploadFile->setValidationErrorType('fileExtension');

            return false;
        }

        // check image data
        $imageData = $uploadFile->getImageData();
        if ($imageData === null) {
            $uploadFile->setValidationErrorType('uploadFailed');

            return false;
        }

        $height = $imageData['height'];
        $width = $imageData['width'];
        $orientation = ExifUtil::getOrientation(ExifUtil::getExifData($uploadFile->getLocation()));

        // flip height and width if image is rotated 90 or 270 degrees
        if ($orientation == ExifUtil::ORIENTATION_90_ROTATE || $orientation == ExifUtil::ORIENTATION_270_ROTATE) {
            $height = $imageData['width'];
            $width = $imageData['height'];
        }

        $sufficientMemory = ImageHandler::getInstance()->getAdapter()->checkMemoryLimit($width, $height, $imageData['mimeType']);

        // check width
        if ($width < CoverPhoto::MIN_WIDTH) {
            $uploadFile->setValidationErrorType('minWidth');

            return false;
        } elseif (!$sufficientMemory && $width > CoverPhoto::MAX_WIDTH) {
            $uploadFile->setValidationErrorType('maxWidth');

            return false;
        }

        // check height
        if ($height < CoverPhoto::MIN_HEIGHT) {
            $uploadFile->setValidationErrorType('minHeight');

            return false;
        } elseif (!$sufficientMemory && $height > CoverPhoto::MAX_HEIGHT) {
            $uploadFile->setValidationErrorType('maxHeight');

            return false;
        }

        // check file size if image will not be resized automatically
        $filesize = $uploadFile->getFilesize();
        if ($width <= CoverPhoto::MAX_WIDTH && $height <= CoverPhoto::MAX_HEIGHT) {
            if ($filesize > WCF::getSession()->getPermission('user.poi.coverPhoto.maxSize')) {
                $uploadFile->setValidationErrorType('maxSize');

                return false;
            }
        }

        return true;
    }
}
