<?php
namespace poi\system\poi\geocoder;
use wcf\util\JSON;

/**
 * Geocoder implementation for Google Maps.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class GoogleMapsGeocoder extends AbstractGeocoder {
	/**
	 * Geocoder data
	 *
	 */
	protected $gecodingUrl = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s';
	protected $requestsPerSecond = 25;
	protected $limit = 1;
	protected $wait = true;
	
	public function __construct($wait = true) {
		$this->wait = $wait;
	}
	
	/**
	 * Geocode a location
	 */
	public function geocode($location, $user = null) {
		// try cache first
		$cache = $this->checkCache($location);
		if ($cache->geocacheID) return $cache;
		
		// request
		if (!empty(POI_MAP_GEOCODING_KEY)) {
			$key = POI_MAP_GEOCODING_KEY;
		}
		else {
			$key = GOOGLE_MAPS_API_KEY;
		}
		$url = sprintf($this->gecodingUrl, rawurlencode($location)) . '&key=' . rawurlencode($key);
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
		if (!isset($result['results'][0])) return null;
		
		$result = $result['results'][0];
		
		$hash = md5($location);
		$data = [
				'hash' => $hash,
				'lat' => round(floatval($result['geometry']['location']['lat']), 6),
				'lng' => round(floatval($result['geometry']['location']['lng']), 6),
				'location' => $location,
				'time' => TIME_NOW,
				'type' => 1
		];
		$geoLocation = $result['formatted_address'];
		
		$result = $this->setCache($data);
		
		// extend cache by formatted_address
		if ($location != $geoLocation) {
			$data['location'] = $geoLocation;
			$data['hash'] = md5($geoLocation);
			
			$this->setCache($data);
		}
		
		// wait if required
		if ($this->wait) $this->waitAfter($this->requestsPerSecond);
		
		// finally
		return $result;
	}
}
