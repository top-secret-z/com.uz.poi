<?php
namespace poi\system\moderation;
use poi\data\poi\DeletedPoiList;
use wcf\system\moderation\AbstractDeletedContentProvider;

/**
 * Implementation of IDeletedContentProvider for deleted pois.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class DeletedPoiProvider extends AbstractDeletedContentProvider {
	/**
	 * @inheritDoc
	 */
	public function getObjectList() {
		return new DeletedPoiList();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTemplateName() {
		return 'deletedPoiList';
	}
}
