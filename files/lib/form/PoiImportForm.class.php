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

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use poi\data\category\PoiCategory;
use poi\data\category\PoiCategoryNodeTree;
use poi\data\poi\PoiAction;
use poi\system\POICore;
use Psr\Http\Client\ClientExceptionInterface;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\io\HttpFactory;
use wcf\system\WCF;
use wcf\util\JSON;

/**
 * Imports POIs from gpx files.
 */
class PoiImportForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.poi.canImportPois'];

    /**
     * category id
     */
    public $categoryID = 0;

    /**
     * file upload
     */
    public $upload;

    /**
     * pois / fail
     */
    public $pois = [];

    public $fail = 0;

    /**
     * @var HtmlInputProcessor
     */
    protected $htmlInputProcessor;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['categoryID'])) {
            $this->categoryID = \intval($_REQUEST['categoryID']);
        }
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_FILES['upload'])) {
            $this->upload = $_FILES['upload'];
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        // validate category id
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

        if ($this->upload && $this->upload['error'] != 4) {
            if (empty($this->upload['tmp_name'])) {
                throw new UserInputException('upload', 'uploadFailed');
            }

            // get and parse file contents
            try {
                $gpx = \simplexml_load_file($this->upload['tmp_name']);
            } catch (Exception $e) {
                throw new UserInputException('upload', 'poi');
            }

            $this->pois = [];
            $count = $this->fail = 0;

            if (!empty(POI_MAP_GEOCODING_KEY)) {
                $key = POI_MAP_GEOCODING_KEY;
            } else {
                $key = GOOGLE_MAPS_API_KEY;
            }
            $geoUrl = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=%s,%s&key=' . \rawurlencode($key);

            foreach ($gpx->wpt as $wpt) {
                // max 5 pois
                $count++;
                if ($count > 5) {
                    break;
                }

                // parse gpx
                $poi = [];
                $lat = (float)$wpt["lat"];
                $lon = (float)$wpt["lon"];
                $poi['latitude'] = \round($lat, 7);
                $poi['longitude'] = \round($lon, 7);
                $poi['elevation'] = isset($wpt->ele) ? (int)$wpt->ele : 0;
                $poi['subject'] = isset($wpt->name) ? (string)$wpt->name : '';
                $poi['message'] = isset($wpt->desc) ? (string)$wpt->desc : '';

                // get location
                try {
                    $url = \sprintf($geoUrl, $poi['latitude'], $poi['longitude']);

                    $request = new Request('GET', $url);
                    $response = $this->getHttpClient()->send($request);

                    \usleep(1000000);
                } catch (ClientExceptionInterface $e) {
                    $this->fail++;
                    continue;
                }

                if ($response->getStatusCode() != 200) {
                    $this->fail++;
                    continue;
                }

                $result = JSON::decode((string)$response->getBody());

                // skip if empty
                if (!isset($result['results']) || empty($result['results'])) {
                    $this->fail++;
                    continue;
                }

                $result = $result['results'][0];
                $poi['location'] = $result['formatted_address'];

                $this->pois[] = $poi;
            }

            if (empty($this->pois)) {
                throw new UserInputException('upload', 'poi');
            }
        } else {
            throw new UserInputException('upload');
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        foreach ($this->pois as $poi) {
            // make texts
            $subject = $poi['subject'];
            $message = $poi['message'];
            $teaser = '';

            if (empty($message)) {
                if (empty($subject)) {
                    $message = WCF::getLanguage()->get('poi.poi.import.description.default');
                } else {
                    $message = $subject;
                }
            }

            if (empty($subject)) {
                $subject = WCF::getLanguage()->get('poi.poi.import.subject.default');
                $teaser = WCF::getLanguage()->get('poi.poi.import.teaser.default');
            } else {
                $teaser = $subject;
            }

            // create poi
            $data = [
                'subject' => $subject,
                'time' => TIME_NOW,
                'userID' => WCF::getUser()->userID,
                'username' => WCF::getUser()->username,
                'message' => '',
                'elevation' => $poi['elevation'],
                'location' => $poi['location'],
                'latitude' => $poi['latitude'],
                'longitude' => $poi['longitude'],
                'teaser' => $teaser,
                'isDisabled' => WCF::getSession()->getPermission('user.poi.canAddPoiWithoutModeration') ? 0 : 1,
                'categoryID' => $this->categoryID,
                'coverPhotoID' => null,
            ];

            $this->getHtmlInputProcessor()->process($message, 'com.uz.poi.poi', 0, true);
            $data['message'] = $this->getHtmlInputProcessor()->getHtml();
            $data['enableHtml'] = 1;

            $poiData = [
                'data' => $data,
                'attachmentHandler' => null,
                'htmlInputProcessor' => $this->htmlInputProcessor,
                'options' => null,
            ];

            $objectAction = new PoiAction([], 'create', $poiData);
            $objectAction->executeAction();
        }
        $this->saved();

        // show success
        WCF::getTPL()->assign([
            'success' => \count($this->pois),
            'fail' => $this->fail,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        POICore::getInstance()->setLocation();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'categoryNodeList' => (new PoiCategoryNodeTree('com.uz.poi.category'))->getIterator(),
            'categoryID' => $this->categoryID,
        ]);
    }

    /**
     * @return HtmlInputProcessor
     */
    protected function getHtmlInputProcessor()
    {
        if ($this->htmlInputProcessor === null) {
            $this->htmlInputProcessor = new HtmlInputProcessor();
        }

        return $this->htmlInputProcessor;
    }

    /**
     * getHttpClient
     */
    private function getHttpClient(): ClientInterface
    {
        if (!$this->httpClient) {
            $this->httpClient = HttpFactory::makeClientWithTimeout(5);
        }

        return $this->httpClient;
    }
}
