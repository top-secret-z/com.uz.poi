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
namespace poi\form;

use poi\data\category\PoiCategory;
use poi\data\category\PoiCategoryNodeTree;
use poi\data\cover\photo\CoverPhoto;
use poi\data\poi\Poi;
use poi\data\poi\PoiAction;
use poi\system\option\PoiOptionHandler;
use poi\system\POICore;
use wcf\form\MessageForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\message\censorship\Censorship;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the new poi form.
 */
class PoiAddForm extends MessageForm
{
    /**
     * @inheritDoc
     */
    public $attachmentObjectType = 'com.uz.poi.poi';

    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.poi.canAddPoi'];

    /**
     * enables the comment function
     */
    public $enableComments = 1;

    /**
     * category id
     */
    public $categoryID = 0;

    /**
     * cover photo
     */
    public $coverPhoto;

    public $coverPhotoID = 0;

    /**
     * @inheritDoc
     */
    public $enableMultilingualism = POI_ENABLE_MULTILINGUALISM;

    /**
     * tags
     */
    public $tags = [];

    /**
     * teaser text
     */
    public $teaser = '';

    /**
     * @inheritDoc
     */
    public $messageObjectType = 'com.uz.poi.poi';

    /**
     * category list
     */
    public $categoryList;

    /**
     * geo location data
     */
    public $geocode = '';

    public $latitude = 0.0;

    public $longitude = 0.0;

    public $elevation = 0;

    /**
     * option handler
     */
    public $optionHandler;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['categoryID'])) {
            $this->categoryID = \intval($_REQUEST['categoryID']);
        }

        // get max text length
        $this->maxTextLength = WCF::getSession()->getPermission('user.poi.maxTextLength');

        // init options
        $this->optionHandler = new PoiOptionHandler(false);
        $this->initOptionHandler();
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        // options
        $this->optionHandler->readUserInput($_POST);

        if (isset($_POST['tags']) && \is_array($_POST['tags'])) {
            $this->tags = ArrayUtil::trim($_POST['tags']);
        }
        if (WCF::getSession()->getPermission('user.poi.canDisableCommentFunction')) {
            if (isset($_POST['enableComments'])) {
                $this->enableComments = 1;
            } else {
                $this->enableComments = 0;
            }
        }
        if (isset($_POST['teaser'])) {
            $this->teaser = StringUtil::trim($_POST['teaser']);
        }

        if (isset($_POST['geocode'])) {
            $this->geocode = StringUtil::trim($_POST['geocode']);
        }
        if (isset($_POST['latitude'])) {
            $this->latitude = \floatval($_POST['latitude']);
        }
        if (isset($_POST['longitude'])) {
            $this->longitude = \floatval($_POST['longitude']);
        }
        if (isset($_POST['elevation'])) {
            $this->elevation = \intval($_POST['elevation']);
        }

        if (isset($_POST['coverPhotoID'])) {
            $this->coverPhotoID = \intval($_POST['coverPhotoID']);
            $this->coverPhoto = new CoverPhoto($this->coverPhotoID);
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        // options
        $optionHandlerErrors = $this->optionHandler->validate();

        parent::validate();

        // validate options
        if (!empty($optionHandlerErrors)) {
            throw new UserInputException('options', $optionHandlerErrors);
        }

        // category ids
        if (empty($this->categoryID)) {
            throw new UserInputException('categoryID');
        }
        $category = PoiCategory::getCategory($this->categoryID);
        if ($category === null) {
            throw new UserInputException('categoryID', 'invalid');
        }
        if (!$category->isAccessible() || !$category->getPermission('canUseCategory')) {
            throw new UserInputException('categoryID', 'invalid');
        }

        // teaser
        if (empty($this->teaser)) {
            throw new UserInputException('teaser');
        }
        if (\mb_strlen($this->teaser) > POI_MAX_TEASER_LENGTH) {
            throw new UserInputException('teaser', 'tooLong');
        }
        // search for censored words
        $result = Censorship::getInstance()->test($this->teaser);
        if ($result) {
            WCF::getTPL()->assign('censoredWords', $result);
            throw new UserInputException('teaser', 'censoredWordsFound');
        }

        // geo location data
        if (empty($this->geocode)) {
            throw new UserInputException('geocode', 'required');
        }

        if ($this->coverPhotoID) {
            if (!$this->coverPhoto->coverPhotoID) {
                throw new UserInputException('coverPhotoID');
            }

            $this->validateCoverPhoto();
        }
    }

    /**
     * validat cover photo
     */
    protected function validateCoverPhoto()
    {
        if ($this->coverPhoto->poiID || $this->coverPhoto->userID != WCF::getUser()->userID) {
            throw new UserInputException('coverPhotoID');
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        // options
        $options = $this->optionHandler->save();

        // save poi
        $data = \array_merge($this->additionalFields, [
            'languageID' => $this->languageID,
            'subject' => $this->subject,
            'time' => TIME_NOW,
            'userID' => WCF::getUser()->userID,
            'username' => WCF::getUser()->username,
            'teaser' => $this->teaser,
            'enableComments' => $this->enableComments,
            'isDisabled' => WCF::getSession()->getPermission('user.poi.canAddPoiWithoutModeration') ? 0 : 1,
            'categoryID' => $this->categoryID,
            'location' => $this->geocode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'elevation' => $this->elevation,
            'coverPhotoID' => $this->coverPhotoID ?: null,
        ]);

        $poiData = [
            'data' => $data,
            'attachmentHandler' => $this->attachmentHandler,
            'htmlInputProcessor' => $this->htmlInputProcessor,
            'options' => $options,
        ];
        if (MODULE_TAGGING && WCF::getSession()->getPermission('user.tag.canViewTag')) {
            $poiData['tags'] = $this->tags;
        }

        $this->objectAction = new PoiAction([], 'create', $poiData);
        $poi = $this->objectAction->executeAction()['returnValues'];

        // call saved event
        $this->saved();

        HeaderUtil::redirect(LinkHandler::getInstance()->getLink('Poi', [
            'application' => 'poi',
            'object' => $poi,
        ]));

        exit;
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // get categories
        $excludedCategoryIDs = \array_diff(PoiCategory::getAccessibleCategoryIDs(), PoiCategory::getAccessibleCategoryIDs(['canUseCategory']));
        $categoryTree = new PoiCategoryNodeTree('com.uz.poi.category', 0, false, $excludedCategoryIDs);
        $this->categoryList = $categoryTree->getIterator();

        if (empty($_POST)) {
            // multilingualism
            if (!empty($this->availableContentLanguages)) {
                if (!$this->languageID) {
                    $language = LanguageFactory::getInstance()->getUserLanguage();
                    $this->languageID = $language->languageID;
                }

                if (!isset($this->availableContentLanguages[$this->languageID])) {
                    $languageIDs = \array_keys($this->availableContentLanguages);
                    $this->languageID = \array_shift($languageIDs);
                }
            }
        }

        // set default option values
        $this->optionHandler->readData();

        // set location
        $this->setLocation();
    }

    /**
     * Sets location data.
     */
    protected function setLocation()
    {
        POICore::getInstance()->setLocation();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'categoryNodeList' => $this->categoryList,
            'categoryID' => $this->categoryID,
            'tags' => $this->tags,
            'enableComments' => $this->enableComments,
            'action' => 'add',
            'teaser' => $this->teaser,
            'geocode' => $this->geocode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'elevation' => $this->elevation,
            'options' => $this->optionHandler->getOptions(),
            'coverPhoto' => $this->coverPhoto,
            'coverPhotoID' => $this->coverPhotoID,
            'coverPhotoDimensions' => [
                'max' => [
                    'height' => CoverPhoto::MAX_HEIGHT,
                    'width' => CoverPhoto::MAX_WIDTH,
                ],
                'min' => [
                    'height' => CoverPhoto::MIN_HEIGHT,
                    'width' => CoverPhoto::MIN_WIDTH,
                ],
            ],
        ]);
    }

    /**
     * Initializes the option handler.
     */
    protected function initOptionHandler()
    {
        $this->optionHandler->init();
    }
}
