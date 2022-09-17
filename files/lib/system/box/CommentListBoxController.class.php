<?php
namespace poi\system\box;
use poi\data\category\PoiCategory;
use wcf\data\comment\ViewableCommentList;
use wcf\system\box\AbstractCommentListBoxController;
use wcf\system\WCF;

/**
 * Box controller implementation for a list of poi comments.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
class CommentListBoxController extends AbstractCommentListBoxController {
	/**
	 * @inheritDoc
	 */
	protected $objectTypeName = 'com.uz.poi.poiComment';
	
	/**
	 * @inheritDoc
	 */
	protected function applyObjectTypeFilters(ViewableCommentList $commentList) {
		$accessibleCategoryIDs = PoiCategory::getAccessibleCategoryIDs();
		if (WCF::getSession()->getPermission('user.poi.canViewPoi') && !empty($accessibleCategoryIDs)) {
			$commentList->sqlJoins .= ' INNER JOIN poi' . WCF_N . '_poi poi ON (comment.objectID = poi.poiID)';
			$commentList->sqlSelects = 'poi.subject AS title';
			
			$commentList->getConditionBuilder()->add('poi.categoryID IN (?)', [$accessibleCategoryIDs]);
			
			if (!WCF::getSession()->getPermission('mod.poi.canModeratePoi')) {
				$commentList->getConditionBuilder()->add('poi.isDisabled = ?', [0]);
			}
			if (!WCF::getSession()->getPermission('mod.poi.canViewDeletedPoi')) {
				$commentList->getConditionBuilder()->add('poi.isDeleted = ?', [0]);
			}
		}
		else {
			$commentList->getConditionBuilder()->add('0 = 1');
		}
	}
}
