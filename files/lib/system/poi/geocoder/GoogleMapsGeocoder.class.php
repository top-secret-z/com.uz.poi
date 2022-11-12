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
namespace poi\system\poi\geocoder;

use wcf\util\JSON;

/**
 * Geocoder implementation for Google Maps.
 */
class GoogleMapsGeocoder extends AbstractGeocoder
{
    /**
     * Geocoder data
     *
     */
    protected $gecodingUrl = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s';

    protected $requestsPerSecond = 25;

    protected $limit = 1;

    protected $wait = true;

    public function __construct($wait = true)
    {
        $this->wait = $wait;
    }

    /**
     * Geocode a location
     */
    public function geocode($location, $user = null)
    {
        // try cache first
        $cache = $this->checkCache($location);
        if ($cache->geocacheID) {
            return $cache;
        }

        // request
        if (!empty(POI_MAP_GEOCODING_KEY)) {
            $key = POI_MAP_GEOCODING_KEY;
        } else {
            $key = GOOGLE_MAPS_API_KEY;
        }
        $url = \sprintf($this->gecodingUrl, \rawurlencode($location)) . '&key=' . \rawurlencode($key);
        $reply = $this->executeRequest($url);

        if (empty($reply)) {
            return null;
        }

        // analyse
        $result = JSON::decode($reply);

        if (!isset($result['status'])) {
            return null;
        }

        // use first result
        if (!isset($result['results'][0])) {
            return null;
        }

        $result = $result['results'][0];

        $hash = \md5($location);
        $data = [
            'hash' => $hash,
            'lat' => \round(\floatval($result['geometry']['location']['lat']), 6),
            'lng' => \round(\floatval($result['geometry']['location']['lng']), 6),
            'location' => $location,
            'time' => TIME_NOW,
            'type' => 1,
        ];
        $geoLocation = $result['formatted_address'];

        $result = $this->setCache($data);

        // extend cache by formatted_address
        if ($location != $geoLocation) {
            $data['location'] = $geoLocation;
            $data['hash'] = \md5($geoLocation);

            $this->setCache($data);
        }

        // wait if required
        if ($this->wait) {
            $this->waitAfter($this->requestsPerSecond);
        }

        // finally
        return $result;
    }
}
