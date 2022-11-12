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
namespace poi\system\attachment;

use poi\data\poi\Poi;
use poi\data\poi\PoiList;
use wcf\system\attachment\AbstractAttachmentObjectType;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Attachment object type implementation for pois.
 */
class PoiAttachmentObjectType extends AbstractAttachmentObjectType
{
    /**
     * @inheritDoc
     */
    public function canDelete($objectID)
    {
        if ($objectID) {
            $poi = new Poi($objectID);
            if ($poi->canEdit()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function canDownload($objectID)
    {
        if ($objectID) {
            $poi = new Poi($objectID);
            if (!$poi->canRead()) {
                return false;
            }

            return WCF::getSession()->getPermission('user.poi.canDownloadAttachment');
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function canUpload($objectID, $parentObjectID = 0)
    {
        if (!WCF::getSession()->getPermission('user.poi.canUploadAttachment')) {
            return false;
        }

        if ($objectID) {
            $poi = new Poi($objectID);
            if ($poi->canEdit()) {
                return true;
            }
        }

        return WCF::getSession()->getPermission('user.poi.canAddPoi');
    }

    /**
     * @inheritDoc
     */
    public function canViewPreview($objectID)
    {
        return $this->canDownload($objectID);
    }

    /**
     * @inheritDoc
     */
    public function getAllowedExtensions()
    {
        return ArrayUtil::trim(\explode("\n", WCF::getSession()->getPermission('user.poi.allowedAttachmentExtensions')));
    }

    /**
     * @inheritDoc
     */
    public function getMaxCount()
    {
        return WCF::getSession()->getPermission('user.poi.maxAttachmentCount');
    }

    /**
     * @inheritDoc
     */
    public function getMaxSize()
    {
        return WCF::getSession()->getPermission('user.poi.maxAttachmentSize');
    }

    /**
     * @inheritDoc
     */
    public function cacheObjects(array $objectIDs)
    {
        $poiList = new PoiList();
        $poiList->setObjectIDs(\array_unique($objectIDs));
        $poiList->readObjects();

        foreach ($poiList->getObjects() as $objectID => $object) {
            $this->cachedObjects[$objectID] = $object;
        }
    }

    /**
     * @inheritDoc
     */
    public function setPermissions(array $attachments)
    {
        $poiIDs = [];
        foreach ($attachments as $attachment) {
            $attachment->setPermissions([
                'canDownload' => false,
                'canViewPreview' => false,
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
                if (!$poi->canRead()) {
                    continue;
                }

                $attachment->setPermissions([
                    'canDownload' => WCF::getSession()->getPermission('user.poi.canDownloadAttachment'),
                    'canViewPreview' => WCF::getSession()->getPermission('user.poi.canDownloadAttachment'),
                ]);
            } elseif ($attachment->tmpHash != '' && $attachment->userID == WCF::getUser()->userID) {
                $attachment->setPermissions([
                    'canDownload' => true,
                    'canViewPreview' => true,
                ]);
            }
        }
    }
}
