<?php
namespace poi\data\poi;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\WCF;

/**
 * Object type provider for pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class LikeablePoiProvider extends PoiProvider implements ILikeObjectTypeProvider, IViewableLikeProvider {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = LikeablePoi::class;
	
	/**
	 * @inheritDoc
	 */
	public function checkPermissions(ILikeObject $object) {
		return $object->poiID && $object->canRead();
	}
	
	/**
	 * @inheritDoc
	 */
	public function prepare(array $likes) {
		$poiIDs = [];
		foreach ($likes as $like) {
			$poiIDs[] = $like->objectID;
		}
		
		// fetch pois
		$poiList = new ViewablePoiList();
		$poiList->setObjectIDs($poiIDs);
		$poiList->readObjects();
		$pois = $poiList->getObjects();
		
		// set message
		foreach ($likes as $like) {
			if (isset($pois[$like->objectID])) {
				$poi = $pois[$like->objectID];
				
				// check permissions
				if (!$poi->canRead()) {
					continue;
				}
				$like->setIsAccessible();
				
				// short output
				$text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.uz.poi.likeablePoi', [
						'poi' => $poi,
						'like' => $like
				]);
				$like->setTitle($text);
				
				$like->setDescription($poi->getExcerpt());
			}
		}
	}
}
