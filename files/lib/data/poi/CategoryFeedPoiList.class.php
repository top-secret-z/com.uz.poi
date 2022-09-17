<?php
namespace poi\data\poi;

/**
 * Represents a list of pois for RSS feeds.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class CategoryFeedPoiList extends CategoryPoiList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = FeedPoi::class;
}
