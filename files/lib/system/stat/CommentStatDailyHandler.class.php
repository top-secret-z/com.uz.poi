<?php
namespace poi\system\stat;
use wcf\system\stat\AbstractCommentStatDailyHandler;

/**
 * Stat handler implementation for poi comments.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class CommentStatDailyHandler extends AbstractCommentStatDailyHandler {
	/**
	 * @inheritDoc
	 */
	protected $objectType = 'com.uz.poi.poiComment';
}
