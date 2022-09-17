<?php
namespace poi\system\event\listener;
use wcf\system\event\listener\AbstractUserActionRenameListener;

/**
 * Updates the stored username on user rename.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class UserActionRenameListener extends AbstractUserActionRenameListener {
	/**
	 * @inheritDoc
	 */
	protected $databaseTables = ['poi{WCF_N}_poi'];
}
