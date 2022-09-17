<?php
namespace poi\data\poi;

/**
 * Represents a list of poi search results.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class SearchResultPoiList extends ViewablePoiList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = SearchResultPoi::class;
}
