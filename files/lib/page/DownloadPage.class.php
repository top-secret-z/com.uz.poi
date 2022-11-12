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
namespace poi\page;

use poi\data\poi\CategoryPoiList;
use poi\data\poi\Poi;
use poi\data\poi\ViewablePoiList;
use wcf\data\category\Category;
use wcf\data\user\User;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Download page for single POIs or POI categories.
 */
class DownloadPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $useTemplate = false;

    /**
     * POI list / data
     */
    public $pois;

    public $bounds;

    public $name;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        $this->name = WCF::getLanguage()->get(PAGE_TITLE);

        if (isset($_REQUEST['poiID'])) {
            $poiID = \intval($_REQUEST['poiID']);
            $poiList = new ViewablePoiList();
            $poiList->getConditionBuilder()->add('poi.poiID = ?', [$poiID]);
            $poiList->readObjects();
            $this->pois = $poiList->getObjects();
            if (empty($this->pois)) {
                throw new IllegalLinkException();
            }

            $this->name .= ' - ' . $this->pois[$poiID]->getSubject();
        } elseif (isset($_REQUEST['catID'])) {
            $catID = \intval($_REQUEST['catID']);

            if (!$catID) {
                $poiList = new ViewablePoiList();
                $poiList->readObjects();
                $this->pois = $poiList->getObjects();
            } else {
                $poiList = new CategoryPoiList($catID, true);
                $poiList->readObjects();
                $this->pois = $poiList->getObjects();

                $category = new Category($catID);
                $this->name .= ' - ' . $category->getTitle();
            }
        } elseif (isset($_REQUEST['userID'])) {
            $userID = \intval($_REQUEST['userID']);

            $poiList = new ViewablePoiList();
            $poiList->getConditionBuilder()->add('poi.userID = ?', [$userID]);
            $poiList->readObjects();
            $this->pois = $poiList->getObjects();

            $user = new User($userID);
            $this->name .= ' - ' . $user->username;
        }

        // calculate bounds
        $maxlat = $maxlon = -180.0;
        $minlat = $minlon = 180.0;
        foreach ($this->pois as $poi) {
            if ($poi->latitude > $maxlat) {
                $maxlat = $poi->latitude;
            }
            if ($poi->latitude < $minlat) {
                $minlat = $poi->latitude;
            }
            if ($poi->longitude > $maxlon) {
                $maxlon = $poi->longitude;
            }
            if ($poi->longitude < $minlon) {
                $minlon = $poi->longitude;
            }
        }
        $this->bounds = 'maxlat="' . $maxlat . '" maxlon="' . $maxlon . '" minlat="' . $minlat . '" minlon="' . $minlon . '"';
    }

    /**
     * @inheritDoc
     */
    public function checkPermissions()
    {
        parent::checkPermissions();

        if (!WCF::getSession()->getPermission('user.poi.canDownloadPois')) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        parent::show();

        // send
        \header('Content-Type: text/xml; charset=UTF-8');
        \header('Content-Disposition: attachment; filename="POI_export.gpx"');

        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<gpx xmlns=\"http://www.topografix.com/GPX/1/1\" version=\"1.1\" creator=\"Points of Interest - https://zaydowicz.de\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd\">\n";
        echo "<metadata><name><![CDATA[" . StringUtil::escapeCDATA($this->name) . "]]></name><time>" . \date('c') . "</time><bounds " . $this->bounds . "/></metadata>\n";

        foreach ($this->pois as $poi) {
            echo "<wpt lat=\"" . $poi->latitude . "\" lon=\"" . $poi->longitude . "\"><ele>" . $poi->elevation . "</ele><name><![CDATA[" . StringUtil::escapeCDATA($poi->getSubject()) . "]]></name><desc><![CDATA[" . StringUtil::escapeCDATA($poi->getTeaser()) . "]]></desc></wpt>\n";
        }
        echo "</gpx>";

        exit;
    }
}
