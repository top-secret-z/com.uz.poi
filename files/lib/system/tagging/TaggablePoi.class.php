<?php
namespace poi\system\tagging;
use poi\data\poi\TaggedPoiList;
use wcf\system\tagging\AbstractCombinedTaggable;

/**
 * Implementation of ITaggable for poi tagging.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class TaggablePoi extends AbstractCombinedTaggable {
	/**
	 * @inheritDoc
	 */
	public function getObjectListFor(array $tags) {
		return new TaggedPoiList($tags);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTemplateName() {
		return 'taggedPoiList';
	}
}
