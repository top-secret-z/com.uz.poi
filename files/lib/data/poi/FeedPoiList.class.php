<?php
namespace poi\data\poi;

/**
 * Represents a list of pois for RSS feeds.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class FeedPoiList extends AccessiblePoiList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = FeedPoi::class;
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'poi.time DESC';
}
