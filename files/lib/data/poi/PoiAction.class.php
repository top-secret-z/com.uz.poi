<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace poi\data\poi;

use poi\data\category\PoiCategory;
use poi\data\cover\photo\CoverPhoto;
use poi\data\cover\photo\CoverPhotoAction;
use poi\data\cover\photo\CoverPhotoEditor;
use poi\data\modification\log\PoiListModificationLogList;
use poi\system\cache\builder\StatsCacheBuilder;
use poi\system\log\modification\PoiModificationLogHandler;
use poi\system\poi\geocoder\GoogleMapsGeocoder;
use poi\system\user\notification\object\PoiUserNotificationObject;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\category\Category;
use wcf\data\category\CategoryList;
use wcf\data\IGroupedUserListAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\object\watch\UserObjectWatchList;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\edit\EditHistoryManager;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\like\LikeHandler;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\moderation\queue\ModerationQueueActivationManager;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\tagging\TagEngine;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;

use const SORT_ASC;

/**
 * Executes poi-related actions.
 */
class PoiAction extends AbstractDatabaseObjectAction implements IGroupedUserListAction
{
    /**
     * @inheritDoc
     */
    protected $className = PoiEditor::class;

    /**
     * @inheritDoc
     */
    protected $requireACP = ['deleteMarker'];

    protected $allowGuestAccess = ['getPoiPreview', 'getMapMarkers', 'search', 'getGroupedUserList'];

    /**
     * viewable poi object
     */
    public $viewablePoi;

    /**
     * poi object
     */
    public $poi;

    /**
     * list of poi data
     */
    public $poiData = [];

    /**
     * poi category
     */
    public $poiCategory;

    // visited poi
    public $visitedPoi;

    /**
     * @inheritDoc
     */
    public function create()
    {
        $data = $this->parameters['data'];

        // set default value
        if (!isset($data['enableHtml'])) {
            $data['enableHtml'] = 1;
        }
        if (!isset($data['lastChangeTime'])) {
            $data['lastChangeTime'] = $data['time'];
        }

        // count attachments
        if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
            $data['attachments'] = \count($this->parameters['attachmentHandler']);
        }

        // handle ip address
        if (LOG_IP_ADDRESS) {
            if (!isset($data['ipAddress'])) {
                $data['ipAddress'] = WCF::getSession()->ipAddress;
            }
        } else {
            if (isset($data['ipAddress'])) {
                unset($data['ipAddress']);
            }
        }

        if (!empty($this->parameters['htmlInputProcessor'])) {
            $data['message'] = $this->parameters['htmlInputProcessor']->getHtml();
        }

        // save poi
        $poi = \call_user_func([$this->className, 'create'], $data);
        $poiEditor = new PoiEditor($poi);

        // save options
        $optionSearch = '';
        if (!empty($this->parameters['options'])) {
            $sql = "INSERT INTO    poi" . WCF_N . "_poi_option_value
                        (poiID, optionID, optionValue)
                    VALUES        (?, ?, ?)";
            $statement = WCF::getDB()->prepareStatement($sql);
            foreach ($this->parameters['options'] as $optionID => $optionValue) {
                $statement->execute([$poi->poiID, $optionID, ($optionValue ?: '')]);

                $optionSearch .= ' ' . $optionValue;
            }
        }

        // update search index
        // use message for various other data (MEDIUMTEXT)
        $message = $poi->message;
        if (!empty($poi->location)) {
            $message .= ' ' . \str_replace(',', ' ', $poi->location);
        }
        if (!empty($poi->teaser)) {
            $message .= ' ' . $poi->teaser;
        }
        if (!empty($optionSearch)) {
            $message .= ' ' . $optionSearch;
        }

        if (\mb_strlen($message) > 10000000) {
            $message = \substr($message, 0, 10000000);
        }

        SearchIndexManager::getInstance()->set(
            'com.uz.poi.poi',
            $poi->poiID,
            $message,
            $poi->subject,
            $poi->time,
            $poi->userID,
            $poi->username,
            $poi->languageID
        );

        // update attachments
        if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
            $this->parameters['attachmentHandler']->updateObjectID($poi->poiID);
        }

        // save embedded objects
        if (!empty($this->parameters['htmlInputProcessor'])) {
            $this->parameters['htmlInputProcessor']->setObjectID($poi->poiID);
            if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor'])) {
                $poiEditor->update(['hasEmbeddedObjects' => 1]);
            }
        }

        // set language id != 0
        $languageID = (!isset($this->parameters['data']['languageID']) || ($this->parameters['data']['languageID'] === null)) ? LanguageFactory::getInstance()->getDefaultLanguageID() : $this->parameters['data']['languageID'];

        // save tags
        if (!empty($this->parameters['tags'])) {
            TagEngine::getInstance()->addObjectTags('com.uz.poi.poi', $poi->poiID, $this->parameters['tags'], $languageID);
        }

        // trigger publication
        if (!$poi->isDisabled) {
            $action = new self([$poiEditor], 'triggerPublication');
            $action->executeAction();
        } else {
            ModerationQueueActivationManager::getInstance()->addModeratedContent('com.uz.poi.poi', $poi->poiID);
        }

        // set cover photo
        if ($poi->hasCoverPhoto()) {
            (new CoverPhotoEditor($poi->getCoverPhoto()))->assignToPoi($poi);
        }

        return $poi;
    }

    /**
     * Triggers the publication of pois.
     */
    public function triggerPublication()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $poi) {
            // update user's poi counter
            PoiEditor::updatePoiCounter([$poi->userID => 1]);

            // fire activity event
            UserActivityEventHandler::getInstance()->fireEvent('com.uz.poi.recentActivityEvent.poi', $poi->poiID, $poi->languageID, $poi->userID, $poi->time);
            UserActivityPointHandler::getInstance()->fireEvent('com.uz.poi.activityPointEvent.poi', $poi->poiID, $poi->userID);

            // watched categories
            $category = $poi->getCategory();
            UserObjectWatchHandler::getInstance()->updateObject(
                'com.uz.poi.category',
                $category->categoryID,
                'category',
                'com.uz.poi.poi.notification',
                new PoiUserNotificationObject($poi->getDecoratedObject())
            );
        }

        // reset storage
        UserStorageHandler::getInstance()->resetAll('poiUnreadPois');
        UserStorageHandler::getInstance()->resetAll('poiUnreadWatchedPois');

        // reset cache
        StatsCacheBuilder::getInstance()->reset();
    }

    /**
     * Validates parameters to mark pois as read.
     */
    public function validateMarkAsRead()
    {
        if (empty($this->objects)) {
            $this->readObjects();

            if (empty($this->objects)) {
                throw new UserInputException('objectIDs');
            }
        }
    }

    /**
     * Marks entries as read.
     */
    public function markAsRead()
    {
        if (empty($this->parameters['visitTime'])) {
            $this->parameters['visitTime'] = TIME_NOW;
        }

        if (empty($this->objects)) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $poi) {
            VisitTracker::getInstance()->trackObjectVisit('com.uz.poi.poi', $poi->poiID, $this->parameters['visitTime']);
        }

        // reset storage
        if (WCF::getUser()->userID) {
            UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'poiUnreadPois');
            UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'poiUnreadWatchedPois');
        }
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        // count attachments
        if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
            $this->parameters['data']['attachments'] = \count($this->parameters['attachmentHandler']);
        }

        if (!empty($this->parameters['htmlInputProcessor'])) {
            $this->parameters['data']['message'] = $this->parameters['htmlInputProcessor']->getHtml();
        }

        // update lastVersionTime for edit history
        if (MODULE_EDIT_HISTORY && isset($this->parameters['isEdit']) && isset($this->parameters['data']['message'])) {
            $this->parameters['data']['lastVersionTime'] = TIME_NOW;
        }

        // cover photo
        $coverPhoto = null;
        if (isset($this->parameters['data']['coverPhotoID'])) {
            $coverPhoto = new CoverPhoto($this->parameters['data']['coverPhotoID']);
        }

        // last change
        $this->parameters['data']['lastChangeTime'] = TIME_NOW;

        parent::update();

        // get ids
        $objectIDs = [];
        foreach ($this->getObjects() as $poi) {
            $objectIDs[] = $poi->poiID;
        }

        // delete old search index entries
        if (!empty($objectIDs)) {
            SearchIndexManager::getInstance()->delete('com.uz.poi.poi', $objectIDs);
        }

        foreach ($this->getObjects() as $poi) {
            // update tags
            if (isset($this->parameters['tags'])) {
                // set language id
                $languageID = (!isset($this->parameters['data']['languageID']) || ($this->parameters['data']['languageID'] === null)) ? LanguageFactory::getInstance()->getDefaultLanguageID() : $this->parameters['data']['languageID'];

                TagEngine::getInstance()->addObjectTags('com.uz.poi.poi', $poi->poiID, $this->parameters['tags'], $languageID);
            }

            // edit
            if (isset($this->parameters['isEdit']) && isset($this->parameters['data']['message'])) {
                $historySavingPoi = new HistorySavingPoi($poi->getDecoratedObject());
                EditHistoryManager::getInstance()->add(
                    'com.uz.poi.poi',
                    $poi->poiID,
                    $poi->message,
                    $historySavingPoi->getTime(),
                    $historySavingPoi->getUserID(),
                    $historySavingPoi->getUsername(),
                    $this->parameters['editReason'] ?? '',
                    WCF::getUser()->userID
                );
            }

            // watched pois
            if (!$poi->isDeleted && !$poi->isDisabled) {
                UserObjectWatchHandler::getInstance()->updateObject(
                    'com.uz.poi.poi',
                    $poi->poiID,
                    'poi',
                    'com.uz.poi.poi.notification',
                    new PoiUserNotificationObject($poi->getDecoratedObject())
                );
            }
            // add log entry
            PoiModificationLogHandler::getInstance()->edit($poi->getDecoratedObject(), ($this->parameters['reason'] ?? ''));

            // update embedded objects
            if (!empty($this->parameters['htmlInputProcessor'])) {
                $this->parameters['htmlInputProcessor']->setObjectID($poi->poiID);
                if ($poi->hasEmbeddedObjects != MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor'])) {
                    $poi->update([
                        'hasEmbeddedObjects' => $poi->hasEmbeddedObjects ? 0 : 1,
                    ]);
                }
            }

            // cover photo
            if ($coverPhoto !== null) {
                (new CoverPhotoEditor($coverPhoto))->assignToPoi($poi->getDecoratedObject());
            }

            // updates options
            $optionSearch = '';
            if (!empty($this->parameters['options'])) {
                $sql = "DELETE FROM    poi" . WCF_N . "_poi_option_value
                        WHERE        poiID = ?";
                $statement = WCF::getDB()->prepareStatement($sql);
                $statement->execute([$poi->poiID]);

                $sql = "INSERT INTO    poi" . WCF_N . "_poi_option_value
                            (poiID, optionID, optionValue)
                        VALUES        (?, ?, ?)";
                $statement = WCF::getDB()->prepareStatement($sql);
                foreach ($this->parameters['options'] as $optionID => $optionValue) {
                    $statement->execute([$poi->poiID, $optionID, ($optionValue ?: '')]);

                    $optionSearch .= ' ' . $optionValue;
                }
            }

            // create new search index poi
            // use message for various other data (MEDIUMTEXT)
            $message = $this->parameters['data']['message'] ?? $poi->message;
            $teaser = $this->parameters['data']['teaser'] ?? $poi->teaser;
            $location = $this->parameters['data']['location'] ?? $poi->location;

            if (!empty($location)) {
                $message .= ' ' . \str_replace(',', ' ', $location);
            }
            if (!empty($teaser)) {
                $message .= ' ' . $teaser;
            }
            if (!empty($optionSearch)) {
                $message .= ' ' . $optionSearch;
            }

            if (\mb_strlen($message) > 10000000) {
                $message = \substr($message, 0, 10000000);
            }
            SearchIndexManager::getInstance()->set(
                'com.uz.poi.poi',
                $poi->poiID,
                $message,
                $this->parameters['data']['subject'] ?? $poi->subject,
                $poi->time,
                $poi->userID,
                $poi->username,
                $poi->languageID
            );
        }
    }

    /**
     * Validates the get poi preview action.
     */
    public function validateGetPoiPreview()
    {
        $this->viewablePoi = ViewablePoi::getPoi(\reset($this->objectIDs));

        if ($this->viewablePoi === null || !$this->viewablePoi->canRead()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Returns a preview of a poi.
     */
    public function getPoiPreview()
    {
        WCF::getTPL()->assign([
            'poi' => $this->viewablePoi,
        ]);

        return [
            'template' => WCF::getTPL()->fetch('poiPreview', 'poi'),
        ];
    }

    /**
     * Validates the stopWatching action.
     */
    public function validateStopWatching()
    {
        $this->readBoolean('stopWatchingAll', true);

        if (!$this->parameters['stopWatchingAll']) {
            if (!isset($this->parameters['poiIDs']) || !\is_array($this->parameters['poiIDs'])) {
                throw new UserInputException('poiIDs');
            }
        }
    }

    /**
     * Stops watching certain pois for a certain user.
     */
    public function stopWatching()
    {
        if ($this->parameters['stopWatchingAll']) {
            $poiWatchList = new UserObjectWatchList();
            $poiWatchList->getConditionBuilder()->add('user_object_watch.objectTypeID = ?', [UserObjectWatchHandler::getInstance()->getObjectTypeID('com.uz.poi.poi')]);
            $poiWatchList->getConditionBuilder()->add('user_object_watch.userID = ?', [WCF::getUser()->userID]);
            $poiWatchList->readObjects();

            $this->parameters['poiIDs'] = [];
            foreach ($poiWatchList as $watchedObject) {
                $this->parameters['poiIDs'][] = $watchedObject->objectID;
            }
        }

        UserObjectWatchHandler::getInstance()->deleteObjects('com.uz.poi.poi', $this->parameters['poiIDs']);
        UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'poiWatchedPois');
        UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'poiUnreadWatchedPois');
    }

    /**
     * Loads pois for given object ids.
     */
    protected function loadPois()
    {
        if (empty($this->objectIDs)) {
            throw new UserInputException("objectIDs");
        }

        $this->readObjects();

        if (empty($this->objects)) {
            throw new UserInputException("objectIDs");
        }
    }

    /**
     * Validates parameters to set pois as featured.
     */
    public function validateSetAsFeatured()
    {
        $this->loadPois();

        if (!WCF::getSession()->getPermission('mod.poi.canEditPoi')) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Validates parameters to unset pois as featured.
     */
    public function validateUnsetAsFeatured()
    {
        $this->loadPois();

        if (!WCF::getSession()->getPermission('mod.poi.canEditPoi')) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Sets pois as featured.
     */
    public function setAsFeatured()
    {
        foreach ($this->getObjects() as $poi) {
            $poi->update([
                'isFeatured' => 1,
            ]);

            $this->addPoiData($poi->getDecoratedObject(), 'isFeatured', 1);
            PoiModificationLogHandler::getInstance()->setAsFeatured($poi->getDecoratedObject());
        }

        $this->unmarkItems();

        return $this->getPoiData();
    }

    /**
     * Unsets pois as featured.
     */
    public function unsetAsFeatured()
    {
        foreach ($this->getObjects() as $poi) {
            $poi->update([
                'isFeatured' => 0,
            ]);

            $this->addPoiData($poi->getDecoratedObject(), 'isFeatured', 0);
            PoiModificationLogHandler::getInstance()->unsetAsFeatured($poi->getDecoratedObject());
        }

        $this->unmarkItems();

        return $this->getPoiData();
    }

    /**
     * Unmarks pois.
     */
    protected function unmarkItems(array $poiIDs = [])
    {
        if (empty($poiIDs)) {
            foreach ($this->getObjects() as $poi) {
                $poiIDs[] = $poi->poiID;
            }
        }

        if (!empty($poiIDs)) {
            ClipboardHandler::getInstance()->unmark($poiIDs, ClipboardHandler::getInstance()->getObjectTypeID('com.uz.poi.poi'));
        }
    }

    /**
     * Adds poi data.
     */
    protected function addPoiData(Poi $poi, $key, $value)
    {
        if (!isset($this->poiData[$poi->poiID])) {
            $this->poiData[$poi->poiID] = [];
        }

        $this->poiData[$poi->poiID][$key] = $value;
    }

    /**
     * Returns stored poi data.
     */
    protected function getPoiData()
    {
        return [
            'poiData' => $this->poiData,
        ];
    }

    /**
     * Validating parameters for enabling pois.
     */
    public function validateEnable()
    {
        $this->loadPois();

        if (!WCF::getSession()->getPermission('mod.poi.canModeratePoi')) {
            throw new PermissionDeniedException();
        }

        foreach ($this->getObjects() as $poi) {
            if (!$poi->isDisabled || $poi->isDeleted) {
                throw new UserInputException('objectIDs');
            }
        }
    }

    /**
     * Enables given pois.
     */
    public function enable()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        $poiIDs = [];
        foreach ($this->getObjects() as $poi) {
            $poi->update([
                'isDisabled' => 0,
                'lastChangeTime' => TIME_NOW,
            ]);

            $this->addPoiData($poi->getDecoratedObject(), 'isDisabled', 0);
            PoiModificationLogHandler::getInstance()->enable($poi->getDecoratedObject());

            $poiIDs[] = $poi->poiID;
        }

        // publish pois
        $poiAction = new self($this->objects, 'triggerPublication');
        $poiAction->executeAction();

        $this->unmarkItems();

        return $this->getPoiData();
    }

    /**
     * Validating parameters for disabling pois.
     */
    public function validateDisable()
    {
        $this->loadPois();

        if (!WCF::getSession()->getPermission('mod.poi.canModeratePoi')) {
            throw new PermissionDeniedException();
        }

        foreach ($this->getObjects() as $poi) {
            if ($poi->isDisabled || $poi->isDeleted) {
                throw new UserInputException('objectIDs');
            }
        }
    }

    /**
     * Disables given pois.
     */
    public function disable()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        $poiData = $userCounters = [];
        foreach ($this->getObjects() as $poi) {
            $poi->update([
                'isDisabled' => 1,
            ]);

            $this->addPoiData($poi->getDecoratedObject(), 'isDisabled', 1);
            PoiModificationLogHandler::getInstance()->disable($poi->getDecoratedObject());

            $poiData[$poi->poiID] = $poi->userID;

            if (!isset($userCounters[$poi->userID])) {
                $userCounters[$poi->userID] = 0;
            }
            $userCounters[$poi->userID]--;
        }

        // remove user activity events
        $this->removeActivityEvents($poiData, true);

        // decrease user poi counter
        if (!empty($userCounters)) {
            PoiEditor::updatePoiCounter($userCounters);
        }

        // reset cache
        StatsCacheBuilder::getInstance()->reset();

        $this->unmarkItems();

        return $this->getPoiData();
    }

    /**
     * Validating parameters for trashing pois.
     */
    public function validateTrash()
    {
        $this->loadPois();
        $this->readString('reason', true, 'data');

        if (!WCF::getSession()->getPermission('mod.poi.canDeletePoi')) {
            if (!WCF::getSession()->getPermission('user.poi.canDeletePoi') || !WCF::getUser()->userID) {
                throw new PermissionDeniedException();
            }

            foreach ($this->getObjects() as $poi) {
                if ($poi->userID != WCF::getUser()->userID) {
                    throw new PermissionDeniedException();
                }
            }
        }

        foreach ($this->getObjects() as $poi) {
            if ($poi->isDeleted) {
                throw new UserInputException('objectIDs');
            }
        }
    }

    /**
     * Trashes given pois.
     */
    public function trash()
    {
        $poiIDs = [];
        foreach ($this->getObjects() as $poi) {
            $poi->update([
                'deleteTime' => TIME_NOW,
                'isDeleted' => 1,
            ]);

            $this->addPoiData($poi->getDecoratedObject(), 'isDeleted', 1);
            PoiModificationLogHandler::getInstance()->trash($poi->getDecoratedObject(), $this->parameters['data']['reason']);

            $poiIDs[] = $poi->poiID;
        }

        // get delete notes
        $logList = new PoiListModificationLogList($poiIDs, 'trash');
        $logList->getConditionBuilder()->add("modification_log.time = ?", [TIME_NOW]);
        $logList->readObjects();
        $logEntries = [];
        foreach ($logList as $logEntry) {
            $logEntries[$logEntry->objectID] = $logEntry->__toString();
        }

        foreach ($this->getObjects() as $poi) {
            $this->addPoiData($poi->getDecoratedObject(), 'deleteNote', $logEntries[$poi->poiID]);
        }

        $this->unmarkItems();

        UserStorageHandler::getInstance()->resetAll('poiUnreadPois');
        UserStorageHandler::getInstance()->resetAll('poiWatchedPois');
        UserStorageHandler::getInstance()->resetAll('poiUnreadWatchedPois');

        return $this->getPoiData();
    }

    /**
     * Validating parameters for deleting pois.
     */
    public function validateDelete()
    {
        $this->loadPois();

        if (!WCF::getSession()->getPermission('mod.poi.canDeletePoiCompletely')) {
            throw new PermissionDeniedException();
        }

        foreach ($this->getObjects() as $poi) {
            if (!$poi->isDeleted) {
                throw new UserInputException('objectIDs');
            }
        }
    }

    /**
     * Deletes given pois.
     */
    public function delete()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        $coverPhotoIDs = $poiIDs = $poiData = $attachmentPoiIDs = $userCounters = [];
        foreach ($this->getObjects() as $poi) {
            $poiIDs[] = $poi->poiID;

            if ($poi->attachments) {
                $attachmentPoiIDs[] = $poi->poiID;
            }

            if ($poi->coverPhotoID) {
                $coverPhotoIDs[] = $poi->coverPhotoID;
            }

            if (!$poi->isDisabled) {
                $poiData[$poi->poiID] = $poi->userID;

                if (!isset($userCounters[$poi->userID])) {
                    $userCounters[$poi->userID] = 0;
                }
                $userCounters[$poi->userID]--;
            }
        }

        // remove user activity events
        if (!empty($poiData)) {
            $this->removeActivityEvents($poiData);
        }

        // remove pois
        foreach ($this->getObjects() as $poi) {
            $poi->delete();

            $this->addPoiData($poi->getDecoratedObject(), 'deleted', LinkHandler::getInstance()->getLink('PoiList', [
                'application' => 'poi',
            ]));
            PoiModificationLogHandler::getInstance()->delete($poi->getDecoratedObject());
        }

        // delete cover photos
        if (!empty($coverPhotoIDs)) {
            (new CoverPhotoAction($coverPhotoIDs, 'delete'))->executeAction();
        }

        // decrease user poi counter
        if (!empty($userCounters)) {
            PoiEditor::updatePoiCounter($userCounters);
        }

        if (!empty($poiIDs)) {
            // delete like data
            LikeHandler::getInstance()->removeLikes('com.uz.poi.likeablePoi', $poiIDs);

            // remove edit history
            EditHistoryManager::getInstance()->delete('com.uz.poi.poi', $poiIDs);

            // delete comments
            CommentHandler::getInstance()->deleteObjects('com.uz.poi.poiComment', $poiIDs);

            // delete tag to object entries
            TagEngine::getInstance()->deleteObjects('com.uz.poi.poi', $poiIDs);

            // delete entry from search index
            SearchIndexManager::getInstance()->delete('com.uz.poi.poi', $poiIDs);

            // delete embedded objects
            MessageEmbeddedObjectManager::getInstance()->removeObjects('com.uz.poi.poi', $poiIDs);

            // delete the log entries except for deleting the poi
            PoiModificationLogHandler::getInstance()->deleteLogs($poiIDs, ['delete']);
        }

        // reset cache
        StatsCacheBuilder::getInstance()->reset();

        // delete attachments
        if (!empty($attachmentPoiIDs)) {
            AttachmentHandler::removeAttachments('com.uz.poi.poi', $attachmentPoiIDs);
        }

        // delete subscriptions
        UserObjectWatchHandler::getInstance()->deleteObjects('com.uz.poi.poi', $poiIDs);

        $this->unmarkItems();

        return $this->getPoiData();
    }

    /**
     * Validating parameters for restoring pois.
     */
    public function validateRestore()
    {
        $this->loadPois();

        if (!WCF::getSession()->getPermission('mod.poi.canDeletePoi')) {
            throw new PermissionDeniedException();
        }

        foreach ($this->getObjects() as $poi) {
            if (!$poi->isDeleted) {
                throw new UserInputException('objectIDs');
            }
        }
    }

    /**
     * Restores given pois.
     */
    public function restore()
    {
        $poiIDs = [];
        foreach ($this->getObjects() as $poi) {
            $poi->update([
                'deleteTime' => 0,
                'isDeleted' => 0,
            ]);
            $poiIDs[] = $poi->poiID;

            $this->addPoiData($poi->getDecoratedObject(), 'isDeleted', 0);
            PoiModificationLogHandler::getInstance()->restore($poi->getDecoratedObject());
        }

        $this->unmarkItems();

        UserStorageHandler::getInstance()->resetAll('poiUnreadPois');
        UserStorageHandler::getInstance()->resetAll('poiWatchedPois');
        UserStorageHandler::getInstance()->resetAll('poiUnreadWatchedPois');

        return $this->getPoiData();
    }

    /**
     * Removes user activity events for pois.
     */
    protected function removeActivityEvents(array $poiData, $ignoreVersions = false)
    {
        $poiIDs = \array_keys($poiData);
        $userToItems = [];
        foreach ($poiData as $userID) {
            if (!$userID) {
                continue;
            }

            if (!isset($userToItems[$userID])) {
                $userToItems[$userID] = 0;
            }
            $userToItems[$userID]++;
        }

        // remove poi data
        UserActivityEventHandler::getInstance()->removeEvents('com.uz.poi.recentActivityEvent.poi', $poiIDs);
        UserActivityPointHandler::getInstance()->removeEvents('com.uz.poi.activityPointEvent.poi', $userToItems);
    }

    /**
     * Validates the 'getMapMarkers' action.
     */
    public function validateGetMapMarkers()
    {
        if (isset($this->parameters['categoryID']) && !empty($this->parameters['categoryID'])) {
            $category = new Category($this->parameters['categoryID']);
            if (!$category->categoryID) {
                throw new IllegalLinkException();
            }
            $this->poiCategory = new PoiCategory($category);
            if (!$this->poiCategory->isAccessible()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * Loads the user markers to be displayed on the map.
     */
    public function getMapMarkers()
    {
        // get pois, consider search and category
        $poiList = new AccessiblePoiList();

        if (isset($this->parameters['poiSearch']) && !empty($this->parameters['poiSearch'])) {
            $query = '%' . $this->parameters['poiSearch'] . '%';
            $poiList->getConditionBuilder()->add('poi.subject LIKE ? OR poi.teaser LIKE ? OR poi.message LIKE ? OR poi.location LIKE ?', [$query, $query, $query, $query]);
        }

        if (isset($this->parameters['categoryID']) && !empty($this->parameters['categoryID'])) {
            $categoryIDs[] = $this->poiCategory->categoryID;

            // get accessible childs
            $childs = $this->poiCategory->getAllChildCategories();
            if (\count($childs)) {
                $accessibleIDs = PoiCategory::getAccessibleCategoryIDs();

                foreach ($childs as $category) {
                    if (\in_array($category->categoryID, $accessibleIDs)) {
                        $categoryIDs[] = $category->categoryID;
                    }
                }
            }

            $poiList->getConditionBuilder()->add('poi.categoryID IN (?)', [$categoryIDs]);
        }

        $poiList->readObjects();
        $pois = $poiList->getObjects();

        // available marker
        $files = DirectoryUtil::getInstance(POI_DIR . 'images/marker/')->getFiles(SORT_ASC);
        $icons = [];
        foreach ($files as $file) {
            if (\is_dir($file)) {
                continue;
            }

            $name = \basename($file);
            $icons[$name] = $name;
        }

        // category assigned markers
        $categoryIDs = [];
        foreach ($pois as $poi) {
            $categoryIDs[] = $poi->categoryID;
        }
        $categoryIDs = \array_unique($categoryIDs);

        $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.category', 'com.uz.poi.category');
        $categoryList = new CategoryList();
        $categoryList->getConditionBuilder()->add('category.objectTypeID = ?', [$objectTypeID]);
        $categoryList->readObjects();

        $categoryToMarker = [];
        foreach ($categoryList->getObjects() as $category) {
            $name = $category->additionalData['marker'];
            if (!\in_array($name, $icons)) {
                $name = \reset($icons);
            }
            $categoryToMarker[$category->categoryID] = $name;
        }

        $markers = [];
        foreach ($pois as $poi) {
            $markers[] = [
                'infoWindow' => WCF::getTPL()->fetch('infoWindow', 'poi', [
                    'poi' => $poi,
                ]),
                'latitude' => $poi->latitude,
                'longitude' => $poi->longitude,
                'location' => $poi->location,
                'categoryID' => $poi->categoryID,
                'userID' => $poi->userID,
                'username' => $poi->username,
                'icon' => WCF::getPath('poi') . 'images/marker/' . $categoryToMarker[$poi->categoryID],
            ];
        }

        return ['markers' => $markers];
    }

    /**
     * Validates the marker delete action.
     */
    public function validateDeleteMarker()
    {
        /* nothing to validate */
    }

    /**
     * deletes a marker
     */
    public function deleteMarker()
    {
        $file = POI_DIR . 'images/marker/' . $this->parameters['filename'];
        if (\file_exists($file)) {
            @\unlink($file);
        }
    }

    /**
     * Validates user search action.
     */
    public function validateSearch()
    {
        /* nothing to validate */
    }

    /**
     * search user and location
     */
    public function search()
    {
        $location = $this->parameters['location'];
        $data = [];
        $data['icon'] = WCF::getPath('poi') . 'images/marker/search/search.png';

        if (!empty($location)) {
            $geocoder = new GoogleMapsGeocoder(true);
            $result = $geocoder->geocode($location, null, false);
            if ($result) {
                $data['locationLat'] = $result->lat;
                $data['locationLng'] = $result->lng;
            }
        }

        return $data;
    }

    /**
     * Validates prepare visit action.
     */
    public function validatePrepareVisit()
    {
        $poi = new Poi($this->parameters['poiID']);
        if (!$poi->poiID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * Executes the prepare visit action.
     */
    public function prepareVisit()
    {
        // get visit time
        $sql = "SELECT    time
                FROM    poi" . WCF_N . "_poi_visit
                WHERE    userID = ? AND poiID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([WCF::getUser()->userID, $this->parameters['poiID']]);
        $visitTime = $statement->fetchColumn();
        if (!$visitTime) {
            $visitTime = '';
        } else {
            $visitTime = \date('r', $visitTime);
        }

        WCF::getTPL()->assign([
            'visitTime' => $visitTime,
        ]);

        return [
            'template' => WCF::getTPL()->fetch('poiVisit', 'poi'),
            'visitTime' => $visitTime,
        ];
    }

    /**
     * Validates save visit action.
     */
    public function validateSaveVisit()
    {
        $this->visitedPoi = new Poi($this->parameters['poiID']);
        if (!$this->visitedPoi->poiID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * Executes the save visit action.
     */
    public function saveVisit()
    {
        $visitTime = $this->parameters['visitTime'];
        $isVisitor = $this->visitedPoi->isVisitor();

        // always delete from visits first
        $sql = "DELETE FROM    poi" . WCF_N . "_poi_visit
                WHERE        userID = ? AND poiID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([WCF::getUser()->userID, $this->visitedPoi->poiID]);

        // update visits
        if (!$visitTime) {
            if ($isVisitor && $this->visitedPoi->visits > 0) {
                $editor = new PoiEditor($this->visitedPoi);
                $editor->updateCounters(['visits' => -1]);
            }
        } else {
            $visitTime = \strtotime($visitTime);

            if (!$isVisitor) {
                $editor = new PoiEditor($this->visitedPoi);
                $editor->updateCounters(['visits' => 1]);
            }

            $sql = "INSERT INTO    poi" . WCF_N . "_poi_visit
                        (poiID, userID, time)
                    VALUES        (?, ?, ?)";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$this->visitedPoi->poiID, WCF::getUser()->userID, $visitTime]);
        }
    }

    /**
     * @inheritDoc
     */
    public function validateGetGroupedUserList()
    {
        $this->readInteger('poiID');

        // read poi
        $this->visitedPoi = new Poi($this->parameters['poiID']);
        if (!$this->visitedPoi->poiID) {
            throw new UserInputException('pollID');
        }
    }

    /**
     * @inheritDoc
     */
    public function getGroupedUserList()
    {
        // get users
        $userIDs = $times = [];
        $sql = "SELECT        userID, time
                FROM        poi" . WCF_N . "_poi_visit
                WHERE        poiID = ?
                ORDER BY    time DESC";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->visitedPoi->poiID]);
        while ($row = $statement->fetchArray()) {
            $userIDs[] = $row['userID'];
            $times[$row['userID']] = $row['time'];
        }

        $users = [];
        $count = 0;
        if (!empty($userIDs)) {
            $users = UserProfileRuntimeCache::getInstance()->getObjects($userIDs);
            $count = \count($users);
            if ($count) {
                foreach ($users as $user) {
                    $user->poiVisitTime = $times[$user->userID];
                }
            }
        }

        WCF::getTPL()->assign([
            'userList' => $users,
            'visits' => $this->visitedPoi->visits,
            'diff' => $this->visitedPoi->visits - $count,
        ]);

        return [
            'pageCount' => 1,
            'template' => WCF::getTPL()->fetch('visitorsList', 'poi'),
        ];
    }
}
