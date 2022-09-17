<?php
namespace poi\data\poi;
use wcf\data\search\ISearchResultObject;
use wcf\system\search\SearchResultTextParser;

/**
 * Represents a poi search result.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class SearchResultPoi extends ViewablePoi implements ISearchResultObject {
	/**
	 * @inheritDoc
	 */
	public function getContainerLink() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContainerTitle() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFormattedMessage() {
		return SearchResultTextParser::getInstance()->parse($this->getDecoratedObject()->getSimplifiedFormattedMessage());
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink($query = '') {
		return $this->getDecoratedObject()->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectTypeName() {
		return 'com.uz.poi.poi';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSubject() {
		return $this->getDecoratedObject()->getSubject();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTime() {
		return $this->time;
	}
}
