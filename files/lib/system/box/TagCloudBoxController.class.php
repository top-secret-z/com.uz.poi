<?php
namespace poi\system\box;

/**
 * Box for the tag cloud of a poi.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class TagCloudBoxController extends \wcf\system\box\TagCloudBoxController {
	/**
	 * @inheritDoc
	 */
	protected $objectType = 'com.uz.poi.poi';
	
	/**
	 * @inheritDoc
	 */
	protected $neededPermission = 'user.poi.canViewPoi';
}
