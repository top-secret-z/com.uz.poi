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
namespace poi\data\poi;

use wcf\data\IFeedEntryWithEnclosure;
use wcf\data\TUserContent;
use wcf\system\feed\enclosure\FeedEnclosure;
use wcf\system\request\LinkHandler;

/**
 * Represents a viewable poi for RSS feeds.
 */
class FeedPoi extends ViewablePoi implements IFeedEntryWithEnclosure
{
    use TUserContent;

    /**
     * @var FeedEnclosure
     */
    protected $enclosure;

    /**
     * @inheritdoc
     */
    public function getLink()
    {
        return LinkHandler::getInstance()->getLink('Poi', [
            'application' => 'poi',
            'object' => $this->getDecoratedObject(),
            'appendSession' => false,
            'encodeTitle' => true,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->getDecoratedObject()->getTitle();
    }

    /**
     * @inheritdoc
     */
    public function getFormattedMessage()
    {
        return $this->getDecoratedObject()->getFormattedMessage();
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->getDecoratedObject()->getMessage();
    }

    /**
     * @inheritdoc
     */
    public function getExcerpt($maxLength = 255)
    {
        return $this->getDecoratedObject()->getExcerpt($maxLength);
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->getDecoratedObject()->__toString();
    }

    /**
     * @inheritdoc
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @inheritDoc
     */
    public function getCategories()
    {
        $categories = [];
        $category = $this->getDecoratedObject()->getCategory();
        if ($category !== null) {
            $categories[] = $category->getTitle();
            foreach ($category->getParentCategories() as $category) {
                $categories[] = $category->getTitle();
            }
        }

        return $categories;
    }

    /**
     * @inheritDoc
     */
    public function getEnclosure()
    {
        if ($this->enclosure === null) {
            if ($this->iconHash) {
                $MIMEType = 'image/jpeg';
                switch ($this->iconExtension) {
                    case 'gif':
                        $MIMEType = 'image/gif';
                        break;
                    case 'png':
                        $MIMEType = 'image/png';
                        break;
                }

                $this->enclosure = new FeedEnclosure($this->getIconURL(), $MIMEType, @\filesize($this->getIconLocation()));
            }
        }

        return $this->enclosure;
    }

    /**
     * @inheritdoc
     */
    public function isVisible()
    {
        return $this->canRead();
    }
}
