<?php
namespace poi\data\poi\geocache;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes geocache related actions.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class GeocacheAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = GeocacheEditor::class;
}
