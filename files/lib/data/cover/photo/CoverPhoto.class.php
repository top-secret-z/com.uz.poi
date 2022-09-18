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
namespace poi\data\cover\photo;

use wcf\data\DatabaseObject;
use wcf\system\image\cover\photo\ICoverPhotoImage;
use wcf\system\WCF;

/**
 * Represents a cover photo.
 */
class CoverPhoto extends DatabaseObject implements ICoverPhotoImage
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'coverPhotoID';

    const MAX_HEIGHT = 400;

    const MAX_WIDTH = 2000;

    const MIN_HEIGHT = 100;

    const MIN_WIDTH = 500;

    /**
     * Returns the relative path to the cover photo.
     */
    protected function getStorage()
    {
        $directory = \substr($this->fileHash, 0, 2);

        return "images/coverPhotos/{$directory}/{$this->coverPhotoID}-{$this->fileHash}.{$this->fileExtension}";
    }

    /**
     * @inheritDoc
     */
    public function getCoverPhotoCaption()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getCoverPhotoLocation()
    {
        return POI_DIR . $this->getStorage();
    }

    /**
     * @inheritDoc
     */
    public function getCoverPhotoUrl()
    {
        return WCF::getPath('poi') . $this->getStorage();
    }
}
