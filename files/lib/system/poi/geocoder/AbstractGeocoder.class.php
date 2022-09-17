<?php
namespace poi\system\poi\geocoder;
use poi\data\poi\geocache\Geocache;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use wcf\system\io\HttpFactory;

/**
 * Abstract implementation of a geocoder.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
abstract class AbstractGeocoder {
	/**
	 * URL for geocoding
	 */
	protected $gecodingUrl = '';
	
	/**
	 * Allowed requests per second
	 */
	protected $requestsPerSecond = 1;
	
	/**
	 * limit per request, ufn always 1
	 */
	protected $limit = 1;
	
	/**
	 * @var ClientInterface
	 */
	private $httpClient;
	
	/**
	 * Executes HTTP request
	 */
	protected function executeRequest($url) {
		try {
			$request = new Request('GET', $url);
			$response = $this->getHttpClient()->send($request);
		}
		catch (ClientExceptionInterface $e) {
			return null;
		}
		
		if ($response->getStatusCode() != 200) {
			return null;
		}
		
		return (string)$response->getBody();
	}
	
	/**
	 * @param string $location
	 */
	protected function checkCache($location) {
		$hash = md5($location);
		
		return Geocache::getCacheLocation($hash);
	}
	
	/**
	 * Add location result to cache
	 */
	protected function setCache($result) {
		return Geocache::setCacheLocation($result);
	}
	
	/**
	 * Delay execution iaw $requestsPerSecond
	 */
	protected function waitAfter($requestsPerSecond) {
		$microSec = ceil(1000000 / $requestsPerSecond) + 50000;
		usleep($microSec);
	}
	
	/**
	 * getHttpClient
	 */
	private function getHttpClient(): ClientInterface {
		if (!$this->httpClient) {
			$this->httpClient = HttpFactory::makeClientWithTimeout(5);
		}
		
		return $this->httpClient;
	}
}
