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
namespace poi\acp\form;

use wcf\acp\form\AbstractCategoryAddForm;
use wcf\system\exception\NamedUserException;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\StringUtil;

use const SORT_ASC;

/**
 * Shows the category add form.
 */
class CategoryAddForm extends AbstractCategoryAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'poi.acp.menu.link.poi.category.add';

    /**
     * @inheritDoc
     */
    public $objectTypeName = 'com.uz.poi.category';

    /**
     * marker
     */
    public $markers = [];

    public $selectedMarker = '';

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['selectedMarker'])) {
            $this->selectedMarker = StringUtil::trim($_POST['selectedMarker']);
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        //not ufn
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        $this->additionalData['marker'] = $this->selectedMarker;

        parent::save();

        $this->selectedMarker = '';
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        $files = DirectoryUtil::getInstance(POI_DIR . 'images/marker/')->getFiles(SORT_ASC);
        if (empty($files)) {
            throw new NamedUserException(WCF::getLanguage()->get('poi.acp.marker.error.noIcons'));
        }

        $path = WCF::getPath('poi') . 'images/marker/';
        $first = '';
        foreach ($files as $file) {
            if (\is_dir($file)) {
                continue;
            }
            if (\strpos($file, '/marker/search/')) {
                continue;
            }

            $name = \basename($file);
            if (empty($first)) {
                $first = $name;
            }
            $link = '<img src="' . $path . $name . '" height="30" alt="' . $name . '">';
            $this->markers[$name] = $link;
        }

        $this->selectedMarker = $first;

        WCF::getTPL()->assign([
            'markers' => $this->markers,
            'selectedMarker' => $this->selectedMarker,
        ]);
    }
}
