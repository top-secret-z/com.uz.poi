/**
 * Class and function collection for POI
 * 
 * @author        2017-2022 Zaydowicz
 * @license        GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package        com.uz.poi
 */

/**
 * Initialize Poi namespace
 */
var Poi = { };
Poi.Category = { };
Poi.Map = { };
Poi.Map.GoogleMaps = { };
Poi.Poi = { };
Poi.Poi.Coordinates = { };


/**
 * Marks all categories as read.
 */
Poi.Category.MarkAllAsRead = Class.extend({
    /**
     * success callback function
     */
    _callback: null,

    /**
     * action proxy
     */
    _proxy: null,

    /**
     * Initializes the class.
     */
    init: function(callback) {
        this._callback = callback;

        // initialize proxy
        this._proxy = new WCF.Action.Proxy({
            success: $.proxy(this._success, this)
        });

        // bind event listener
        $('.markAllAsReadButton').click($.proxy(this._click, this));
    },

    /**
     * Handles clicks on the 'mark all as read' button.
     */
    _click: function(event) {
        event.preventDefault();

        this._proxy.setOption('data', {
            actionName: 'markAllAsRead',
            className: 'poi\\data\\category\\PoiCategoryAction'
        });

        this._proxy.sendRequest();
    },

    /**
     * Marks all categories as read.
     */
    _success: function(data, textStatus, jqXHR) {
        if (this._callback && $.isFunction(this._callback)) {
            return this._callback();
        }

        var $categoryList = $('.nestedCategoryList');

        $categoryList.find('.badge.badgeUpdate').hide();
        $('.mainMenu .active .badge').hide();

        var notify = new WCF.System.Notification(WCF.Language.get('wcf.global.success'), 'success');
        notify.show();
    }
});

/**
 * Provides extended functions for poi clipboard actions.
 */
Poi.Poi.Clipboard = Class.extend({
    /**
     * category id
     */
    _categoryID: 0,

    /**
     * current environment
     */
    _environment: 'category',

    /**
     * poi update handler
     */
    _updateHandler: null,

    /**
     * Initializes a new Poi.Poi.Clipboard object.
     */
    init: function(updateHandler, environment, categoryID) {
        this._updateHandler = updateHandler;
        this._environment = environment;
        this._categoryID = (categoryID) ? categoryID : 0;

        require(['EventHandler'], function(EventHandler) {
            EventHandler.add('com.woltlab.wcf.clipboard', 'com.uz.poi.poi', this._clipboardAction.bind(this));
        }.bind(this));
    },

    /**
     * Reacts to executed clipboard actions.
     */
    _clipboardAction: function(actionData) {
        if (actionData.responseData && actionData.responseData.returnValues && actionData.responseData.returnValues.poiData) {
            var poiData = actionData.responseData.returnValues.poiData;
            for (var poiID in poiData) {
                if (poiData.hasOwnProperty(poiID)) {
                    this._updateHandler.update(poiID, poiData[poiID]);
                }
            }
        }
    }
});

/**
 * Inline editor for pois.
 */
Poi.Poi.InlineEditor = WCF.InlineEditor.extend({
    /**
     * current editor environment
     */
    _environment: 'poi',

    /**
     * list of permissions
     */
    _permissions: { },

    /**
     * redirect URL
     */
    _redirectURL: '',

    /**
     * poi update handler
     */
    _updateHandler: null,

    /**
     * @see    WCF.InlineEditor._setOptions()
     */
    _setOptions: function() {
        this._environment = 'poi';

        this._options = [
            // isDisabled
            { label: WCF.Language.get('poi.poi.edit.enable'), optionName: 'enable' },
            { label: WCF.Language.get('poi.poi.edit.disable'), optionName: 'disable' },

            // isDeleted
            { label: WCF.Language.get('poi.poi.edit.trash'), optionName: 'trash' },
            { label: WCF.Language.get('poi.poi.edit.restore'), optionName: 'restore' },
            { label: WCF.Language.get('poi.poi.edit.delete'), optionName: 'delete' },

            { optionName: 'divider' },

            // isFeatured
            { label: WCF.Language.get('poi.poi.edit.setAsFeatured'), optionName: 'setAsFeatured' },
            { label: WCF.Language.get('poi.poi.edit.unsetAsFeatured'), optionName: 'unsetAsFeatured' },

            { optionName: 'divider' },

            // edit poi
            { label: WCF.Language.get('wcf.global.button.edit'), optionName: 'edit', isQuickOption: true }
        ];
    },

    /**
     * Returns current update handler.
     */
    setUpdateHandler: function(updateHandler) {
        this._updateHandler = updateHandler;
    },

    /**
     * @see    WCF.InlineEditor._getTriggerElement()
     */
    _getTriggerElement: function(element) {
        return element.find('.jsPoiInlineEditor');
    },

    /**
     * @see    WCF.InlineEditor._show()
     */
    _show: function(event) {
        var $elementID = $(event.currentTarget).data('elementID');

        // dropdown
        var $trigger = null;
        if (!this._dropdowns[$elementID]) {
            $trigger = this._getTriggerElement(this._elements[$elementID]).addClass('dropdownToggle');
            $trigger.parent().addClass('dropdown');
            this._dropdowns[$elementID] = $('<ul class="dropdownMenu" />').insertAfter($trigger);
        }

        this._super(event);

        if ($trigger !== null) {
            WCF.Dropdown.initDropdown($trigger, true);
        }

        return false;
    },

    /**
     * @see    WCF.InlineEditor._validate()
     */
    _validate: function(elementID, optionName) {
        var $poiID = $('#' + elementID).data('poiID');

        switch (optionName) {
            //isDeleted
            case 'delete':
                if (!this._getPermission('canDeletePoiCompletely')) {
                    return false;
                }

                return (this._updateHandler.getValue($poiID, 'isDeleted'));
            break;

            case 'restore':
                if (!this._getPermission('canRestorePoi')) {
                    return false;
                }

                return (this._updateHandler.getValue($poiID, 'isDeleted'));
            break;

            case 'trash':
                if (!this._getPermission('canDeletePoi')) {
                    return false;
                }

                return !(this._updateHandler.getValue($poiID, 'isDeleted'));
            break;

            // isDisabled
            case 'enable':
                if (!this._getPermission('canEnablePoi')) {
                    return false;
                }

                if (this._updateHandler.getValue($poiID, 'isDeleted')) {
                    return false;
                }

                return (this._updateHandler.getValue($poiID, 'isDisabled'));
            break;

            case 'disable':
                if (!this._getPermission('canEnablePoi')) {
                    return false;
                }

                if (this._updateHandler.getValue($poiID, 'isDeleted')) {
                    return false;
                }

                return !(this._updateHandler.getValue($poiID, 'isDisabled'));
            break;

            // isFeatured
            case 'setAsFeatured':
                if (!this._getPermission('canSetAsFeatured')) {
                    return false;
                }

                return !(this._updateHandler.getValue($poiID, 'isFeatured'));
            break;

            case 'unsetAsFeatured':
                if (!this._getPermission('canSetAsFeatured')) {
                    return false;
                }

                return (this._updateHandler.getValue($poiID, 'isFeatured'));
            break;

            case 'edit':
                return true;
            break;
        }

        return false;
    },

    /**
     * @see    WCF.InlineEditor._execute()
     */
    _execute: function(elementID, optionName) {
        // only validated options
        if (!this._validate(elementID, optionName)) {
            return false;
        }

        switch (optionName) {
            case 'enable':
            case 'disable':
                this._updatePoi(elementID, optionName, { isDisabled: (optionName === 'enable' ? 0 : 1) });
            break;

            case 'delete':
                var self = this;
                WCF.System.Confirmation.show(WCF.Language.get('poi.poi.confirmDelete'), function(action) {
                    if (action === 'confirm') {
                        self._updatePoi(elementID, optionName, { deleted: 1 });
                    }
                });
            break;

            case 'restore':
                this._updatePoi(elementID, optionName, { isDeleted: 0 });
            break;

            case 'trash':
                var self = this;
                WCF.System.Confirmation.show(WCF.Language.get('poi.poi.confirmTrash'), function(action) {
                    if (action === 'confirm') {
                        self._updatePoi(elementID, optionName, { isDeleted: 1, reason: $('#wcfSystemConfirmationContent').find('textarea').val() });
                    }
                }, { }, $('<div class="section"><dl><dt><label for="poiDeleteReason">' + WCF.Language.get('poi.poi.confirmTrash.reason') + '</label></dt><dd><textarea id="poiDeleteReason" cols="40" rows="4" /></dd></dl></div>'));
            break;

            case 'setAsFeatured':
            case 'unsetAsFeatured':
                this._updatePoi(elementID, optionName, { isFeatured: (optionName === 'setAsFeatured' ? 1 : 0) });
            break;

            case 'edit':
                window.location = this._getTriggerElement($('#' + elementID)).prop('href');
            break;

            default:
                return false;
            break;
        }

        return true;
    },

    /**
     * Updates the poi.
     */
    _updatePoi: function(elementID, optionName, data) {
        if (optionName === 'delete') {
            var self = this;
            var $poiID = this._elements[elementID].data('poiID');

            new WCF.Action.Proxy({
                autoSend: true,
                data: {
                    actionName: optionName,
                    className: 'poi\\data\\poi\\PoiAction',
                    objectIDs: [ $poiID ]
                },
                success: function(data) {
                    self._updateHandler.update($poiID, data.returnValues.poiData[$poiID]);
                }
            });
        }
        else {
            this._updateData.push({
                data:         data,
                elementID:     elementID,
                optionName: optionName
            });

            this._proxy.setOption('data', {
                actionName: optionName,
                className:     'poi\\data\\poi\\PoiAction',
                objectIDs:     [ this._elements[elementID].data('poiID') ],
                parameters: { data: data }
            });
            this._proxy.sendRequest();
        }
    },

    /**
     * @see    WCF.InlineEditor._updateState()
     */
    _updateState: function() {
        // redirect user if they may not see deleted pois
        if (this._environment == 'poi' && this._updateData.length == 1 && this._updateData[0].optionName == 'trash' && !this._getPermission('canViewDeletedPoi')) {
            this._notification.show($.proxy(function() {
                window.location = this._redirectURL;
            }, this));
            return;
        }

        this._notification.show();

        for (var $i = 0, $length = this._updateData.length; $i < $length; $i++) {
            var $data = this._updateData[$i];
            var $poiID = $('#' + $data.elementID).data('poiID');

            this._updateHandler.update($poiID, $data.data);
        }
    },

    /**
     * Returns the value of a permission.
     */
    _getPermission: function(permission) {
        if (this._permissions[permission]) {
            return this._permissions[permission];
        }

        return 0;
    },

    /**
     * Sets current environment.
     */
    setEnvironment: function(environment, redirectURL) {
        if (environment !== 'category') {
            environment = 'poi';
        }

        this._environment = environment;
        this._redirectURL = redirectURL;
    },

    /**
     * Sets a single permission.
     */
    setPermission: function(permission, value) {
        this._permissions[permission] = value;
    },

    /**
     * Sets permissions.
     */
    setPermissions: function(permissions) {
        for (var $permission in permissions) {
            this.setPermission($permission, permissions[$permission]);
        }
    }
});

/**
 * Provides a update handler for pois.
 */
Poi.Poi.UpdateHandler = Class.extend({
    /**
     * list of pois
     */
    _pois: { },

    /**
     * Initializes the handler.
     */
    init: function() {
        var self = this;
        $('.poiPoi').each(function(index, poi) {
            var $poi = $(poi);

            self._pois[$poi.data('objectID')] = $poi;
        });
    },

    /**
     * Updates properties for given poiID.
     */
    update: function(poiID, data) {
        if (!this._pois[poiID]) {
            console.debug("[Poi.Poi.UpdateHandler] Unknown poi id " + poiID);
            return;
        }

        for (var $property in data) {
            this._updateProperty(poiID, $property, data[$property]);
        }
    },

    /**
     * Wrapper for property updating.
     */
    _updateProperty: function(poiID, property, value) {
        switch (property) {
            case 'deleted':
                this._delete(poiID, value);
            break;

            case 'isDeleted':
                if (value) {
                    this._trash(poiID);
                }
                else {
                    this._restore(poiID);
                }
            break;

            case 'isDisabled':
                if (value) {
                    this._disable(poiID);
                }
                else {
                    this._enable(poiID);
                }
            break;

            case 'isFeatured':
                if (value) {
                    this._setAsFeatured(poiID);
                }
                else {
                    this._unsetAsFeatured(poiID);
                }
            break;

            default:
                this._handleCustomProperty(poiID, property, value);
            break;
        }
    },

    /**
     * Handles unknown properties.
     */
    _handleCustomProperty: function(poiID, property, value) {
        this._pois[poiID].trigger('poiUpdateHandlerProperty', [ poiID, property, value ]);
    },

    _delete: function(poiID, link) { },

    _disable: function(poiID) {
        this._pois[poiID].data('isDisabled', 1);
    },

    _enable: function(poiID) {
        this._pois[poiID].data('isDisabled', 0);
    },

    _restore: function(poiID) {
        this._pois[poiID].data('isDeleted', 0);
    },

    _setAsFeatured: function(poiID) {
        this._pois[poiID].data('isFeatured', 1);
    },

    _trash: function(poiID) {
        this._pois[poiID].data('isDeleted', 1);
    },

    _unsetAsFeatured: function(poiID) {
        this._pois[poiID].data('isFeatured', 0);
    },

    /**
     * Returns property values of a poi.
     * 
     */
    getValue: function(poiID, property) {
        if (!this._pois[poiID]) {
            console.debug("[Poi.Poi.UpdateHandler] Unknown poi id " + poiID);
            return;
        }

        switch (property) {
            case 'isDeleted':
                return this._pois[poiID].data('isDeleted');
            break;

            case 'isDisabled':
                return this._pois[poiID].data('isDisabled');
            break;

            case 'isFeatured':
                return this._pois[poiID].data('isFeatured');
            break;
        }
    }
});

/**
 * Update handler for poi list.
 */
Poi.Poi.UpdateHandler.Category = Poi.Poi.UpdateHandler.extend({
    /**
     * @see    Poi.Poi.UpdateHandler._delete()
     */
    _delete: function(poiID, link) {
        this._pois[poiID].remove();
        delete this._pois[poiID];

        WCF.Clipboard.reload();
    },

    /**
     * @see    Poi.Poi.UpdateHandler._disable()
     */
    _disable: function(poiID) {
        this._super(poiID);

        this._pois[poiID].addClass('messageDisabled');
    },

    /**
     * @see    Poi.Poi.UpdateHandler._enable()
     */
    _enable: function(poiID) {
        this._super(poiID);

        this._pois[poiID].removeClass('messageDisabled');
    },

    /**
     * @see    Poi.Poi.UpdateHandler._restore()
     */
    _restore: function(poiID) {
        this._super(poiID);

        this._pois[poiID].removeClass('messageDeleted');
        this._pois[poiID].find('.poiDeleteNote').remove();

        var poi = elByClass('poi' + poiID); //Poi');
        var attr = poi[0].getAttribute('data-is-disabled');
        if (attr == "1") {
            this._pois[poiID].addClass('messageDisabled');
        }

    },

    /**
     * @see    Poi.Poi.UpdateHandler._setAsFeatured()
     */
    _setAsFeatured: function(poiID) {
        this._super(poiID);

        $('<span class="badge label green jsLabelFeatured">' + WCF.Language.get('poi.poi.featured') + '</span>').appendTo(this._pois[poiID].find('.poiListPoiIcon'));
    },

    /**
     * @see    Poi.Poi.UpdateHandler._trash()
     */
    _trash: function(poiID) {
        this._super(poiID);

        this._pois[poiID].removeClass('messageDisabled');
        this._pois[poiID].addClass('messageDeleted');
    },

    /**
     * @see    Poi.Poi.UpdateHandler._unsetAsFeatured()
     */
    _unsetAsFeatured: function(poiID) {
        this._super(poiID);

        this._pois[poiID].find('.jsLabelFeatured').remove();
    }
});

/**
 * Update handler for poi.
 */
Poi.Poi.UpdateHandler.Poi = Poi.Poi.UpdateHandler.extend({
    /**
     * @see    Poi.Poi.UpdateHandler.update()
     */
    update: function(poiID, data) {

        var test = this._pois[poiID];

        if (this._pois[poiID]) {
            if (data.isDeleted !== undefined && !data.isDeleted) {
                this._restore(poiID, true);

                delete data.isDeleted;
            }
            if (data.isDisabled !== undefined && !data.isDisabled) {
                this._enable(poiID, true);

                delete data.isDisabled;
            }
        }

        this._super(poiID, data);
    },

    _delete: function(poiID, link) {
        new WCF.PeriodicalExecuter(function(pe) {
            pe.stop();

            window.location = link;
        }, 1000);
    },

    _disable: function(poiID) {
        this._super(poiID);

        this._pois[poiID].addClass('messageDisabled');
    },

    _enable: function(poiID) {
        this._super(poiID);

        this._pois[poiID].removeClass('messageDisabled');
    },

    _restore: function(poiID, disable) {
        this._super(poiID);

        this._pois[poiID].removeClass('messageDeleted');

        var deleteNote = elById('poiDeleteNoteDiv');
        deleteNote.innerHTML = '';

        var poi = elByClass('poi' + poiID);
        var attr = poi[0].getAttribute('data-is-disabled');
        if (attr == "true") {
            this._pois[poiID].addClass('messageDisabled');
        }
    },

    _setAsFeatured: function(poiID) {
        this._super(poiID);

        $('<span class="badge label green jsLabelFeatured">' + WCF.Language.get('poi.poi.featured') + '</span>').prependTo($('.poiPoi .contentTitle'));
    },

    _trash: function(poiID) {
        this._super(poiID);

        this._pois[poiID].removeClass('messageDisabled');
        this._pois[poiID].addClass('messageDeleted');

        var deleteNote = elById('poiDeleteNoteDiv');
        deleteNote.innerHTML = '<div class="section"><p class="poiPoiDeleteNote">' + WCF.Language.get('poi.poi.log.poi.trash.summary.js') + '</p></div>';
    },

    _unsetAsFeatured: function(poiID) {
        this._super(poiID);

        $('.jsLabelFeatured').remove();
    }
});

/**
 * Provides a poi preview.
 */
Poi.Poi.Preview = WCF.Popover.extend({
    /**
     * action proxy
     */
    _proxy: null,

    /**
     * @see    WCF.Popover.init()
     */
    init: function() {
        this._super('.poiPoiLink');

        this._proxy = new WCF.Action.Proxy({
            showLoadingOverlay: false
        });

        WCF.DOMNodeInsertedHandler.addCallback('Poi.Poi.Preview', $.proxy(this._initContainers, this));
    },

    /**
     * @see    WCF.Popover._loadContent()
     */
    _loadContent: function() {
        var $link = $('#' + this._activeElementID);
        this._proxy.setOption('data', {
            actionName: 'getPoiPreview',
            className:     'poi\\data\\poi\\PoiAction',
            objectIDs:     [ $link.data('poiID') ]
        });

        var $elementID = this._activeElementID;
        var self = this;
        this._proxy.setOption('success', function(data, textStatus, jqXHR) {
            self._insertContent($elementID, data.returnValues.template, true);
        });
        this._proxy.sendRequest();
    }
});

/**
 * Handles unsubscribing multiple pois.
 */
Poi.Poi.WatchedPoiList = Class.extend({
    /**
     * button to stop watching all/marked pois
     */
    _button: null,

    /**
     * mark all-checkbox
     */
    _markAllCheckbox: null,

    /**
     * Creates a new instance.
     */
    init: function() {
        this._button = $('#stopWatchingButton').click($.proxy(this._stopWatching, this));
        this._markAllCheckbox = $('.jsMarkAllWatchedPois').change($.proxy(this._markAll, this));

        $('.jsWatchedPoi').change($.proxy(this._mark, this));
    },

    /**
     * Handles a watched checkbox.
     */
    _mark: function(event) {
        $(event.target).parents('tr').toggleClass('jsMarked');

        if (this._markAllCheckbox.is(':checked')) {
            this._markAllCheckbox.prop('checked', false);
        }
        else {
            this._markAllCheckbox.prop('checked', $('.jsWatchedPoi:not(:checked)').length == 0);
        }

        this._updateButtonLabel();
    },

    /**
     * Handles the 'mark all' checkbox.
     */
    _markAll: function(event) {
        $('.jsWatchedPoi').prop('checked', this._markAllCheckbox.prop('checked')).parents('tr').toggleClass('jsMarked', this._markAllCheckbox.prop('checked'));

        this._updateButtonLabel();
    },

    /**
     * Handles a click on the stop watching-button.
     */
    _stopWatching: function(event) {
        var $selectedPois = $('.jsWatchedPoi:checked');
        var $poiIDs = [ ];
        var $stopWatchingAll = false;
        if ($selectedPois.length) {
            $selectedPois.each(function(index, element) {
                $poiIDs.push($(element).data('objectID'));
            });
        }
        else {
            $stopWatchingAll = true;
        }

        var $languageItem = 'poi.poi.watchedPois.stopWatchingMarked.confirmMessage';
        if ($stopWatchingAll) {
            $languageItem = 'poi.poi.watchedPois.stopWatchingAll.confirmMessage';
        }

        WCF.System.Confirmation.show(WCF.Language.get($languageItem), function(action) {
            if (action === 'confirm') {
                new WCF.Action.Proxy({
                    autoSend: true,
                    data: {
                        actionName: 'stopWatching',
                        className: 'poi\\data\\poi\\PoiAction',
                        parameters: {
                            stopWatchingAll: $stopWatchingAll,
                            poiIDs: $poiIDs
                        }
                    },
                    success: function() {
                        window.location.reload();
                    }
                });
            }
        });
    },

    /**
     * Updates the label of the 'stop watching' button.
     */
    _updateButtonLabel: function() {
        var $selectedPois = $('.jsWatchedPoi:checked');

        var $text = '';
        if ($selectedPois.length) {
            $text = WCF.Language.get('poi.poi.watchedPois.stopWatchingMarked', {
                count: $selectedPois.length
            });
        }
        else {
            $text = WCF.Language.get('poi.poi.watchedPois.stopWatchingAll');
        }

        this._button.html($text);
    }
});

/**
 * Appends latitude/longitude to form parameters on submit.
 */
Poi.Poi.Coordinates.Handler = Class.extend({
    /**
     * form element
     */
    _form: null,

    /**
     * location input object
     */
    _locationInput: null,

    /**
     * Initializes the class.
     */
    init: function(locationInput) {
        this._locationInput = locationInput;

        this._form = $('#messageContainer').submit($.proxy(this._submit, this));
    },

    /**
     * Handles the submit event.
     */
    _submit: function(event) {
        if (this._form.data('geocodingCompleted')) {
            return true;
        }

        var $location = $.trim($('#geocode').val());
        if (!$location) {
            WCF.Location.GoogleMaps.Util.reverseGeocoding($.proxy(this._reverseGeocoding, this), this._locationInput.getMarker());

            event.preventDefault();
            return false;
        }

        this._setCoordinates();
    },

    /**
     * Performs a reverse geocoding request.
     */
    _reverseGeocoding: function(location) {
        $('#geocode').val(location);

        this._setCoordinates();
        this._form.trigger('submit');
    },

    /**
     * Appends the coordinates to form parameters.
     */
    _setCoordinates: function() {
        var $formSubmit = this._form.find('.formSubmit');
        $('<input type="hidden" name="latitude" value="' + this._locationInput.getMarker().getPosition().lat() + '" />').appendTo($formSubmit);
        $('<input type="hidden" name="longitude" value="' + this._locationInput.getMarker().getPosition().lng() + '" />').appendTo($formSubmit);

        this._form.data('geocodingCompleted', true);
    }
});

/**
 * Handles the global Google Maps settings for poi map.
 */
Poi.Map.GoogleMaps.Settings = {
    /**
     * Google Maps settings
     */
    _settings: { },

    /**
     * Returns the value of a setting or null.
     */
    get: function(setting) {
        if (setting === undefined) {
            return this._settings;
        }

        if (this._settings[setting] !== undefined) {
            return this._settings[setting];
        }

        return null;
    },

    /**
     * Sets the value of a setting.
     */
    set: function(setting, value) {
        if ($.isPlainObject(setting)) {
            for (var index in setting) {
                this._settings[index] = setting[index];
            }
        }
        else {
            this._settings[setting] = value;
        }
    }
};

/**
 * Handles a Google Maps map.
 */
Poi.Map.GoogleMaps.Map = Class.extend({
    /**
     * map object for the displayed map
     */
    _map: null,

    /**
     * list of markers on the map
     */
    _markers: [ ],

    /**
     * list of infoWindows on the map
     */
    _infoWindows: [ ],

    /**
     * Initalizes a new WCF.Location.Map object.
     */
    init: function(mapContainerID, mapOptions) {
        this._mapContainer = $('#' + mapContainerID);
        this._mapOptions = $.extend(true, this._getDefaultMapOptions(), mapOptions);

        this._map = new google.maps.Map(this._mapContainer[0], this._mapOptions);
        this._markers = [ ];
        this._infoWindows = [ ];

        // fix maps in mobile sidebars by refreshing the map when displaying the map
        if (this._mapContainer.parents('.sidebar').length) {
            enquire.register('(max-width: 767px)', {
                setup: $.proxy(this._addSidebarMapListener, this),
                deferSetup: true
            });
        }

        this.refresh();
    },

    /**
     * Adds the info window event listener.
     */
    _addInfoWindowEventListener: function(marker, infoWindow) {
        google.maps.event.addListener(marker, 'click', $.proxy(function() {
            infoWindow.open(this._map, marker);
        }, this));
    },

    /**
     * Adds click listener to mobile sidebar toggle button to refresh map.
     */
    _addSidebarMapListener: function() {
        $('.content > .mobileSidebarToggleButton').click($.proxy(this.refresh, this));
    },

    /**
     * Returns the default map options.
     */
    _getDefaultMapOptions: function() {
        var $defaultMapOptions = { };

        $defaultMapOptions.center = new google.maps.LatLng(Poi.Map.GoogleMaps.Settings.get('defaultLatitude'), Poi.Map.GoogleMaps.Settings.get('defaultLongitude'));
        $defaultMapOptions.disableDoubleClickZoom = Poi.Map.GoogleMaps.Settings.get('disableDoubleClickZoom');
        $defaultMapOptions.draggable = Poi.Map.GoogleMaps.Settings.get('draggable');

        switch (Poi.Map.GoogleMaps.Settings.get('mapType')) {
            case 'map':
                $defaultMapOptions.mapTypeId = google.maps.MapTypeId.ROADMAP;
            break;

            case 'satellite':
                $defaultMapOptions.mapTypeId = google.maps.MapTypeId.SATELLITE;
            break;

            case 'physical':
                $defaultMapOptions.mapTypeId = google.maps.MapTypeId.TERRAIN;
            break;

            case 'hybrid':
            default:
                $defaultMapOptions.mapTypeId = google.maps.MapTypeId.HYBRID;
            break;
        }

        $defaultMapOptions.mapTypeControl = Poi.Map.GoogleMaps.Settings.get('mapTypeControl') != 'off';
        if ($defaultMapOptions.mapTypeControl) {
            switch (Poi.Map.GoogleMaps.Settings.get('mapTypeControl')) {
                case 'dropdown':
                    $defaultMapOptions.mapTypeControlOptions = {
                        style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                    };
                break;

                case 'horizontalBar':
                    $defaultMapOptions.mapTypeControlOptions = {
                        style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR
                    };
                break;

                default:
                    $defaultMapOptions.mapTypeControlOptions = {
                        style: google.maps.MapTypeControlStyle.DEFAULT
                    };
                break;
            }
        }

        $defaultMapOptions.scaleControl = Poi.Map.GoogleMaps.Settings.get('scaleControl');
        $defaultMapOptions.scrollwheel = Poi.Map.GoogleMaps.Settings.get('scrollwheel');
        $defaultMapOptions.zoom = Poi.Map.GoogleMaps.Settings.get('zoom');

        return $defaultMapOptions;
    },

    /**
     * Adds a draggable marker to the map.
     */
    addDraggableMarker: function(latitude, longitude) {
        var $marker = new google.maps.Marker({
            clickable: false,
            draggable: true,
            map: this._map,
            position: new google.maps.LatLng(latitude, longitude),
            zIndex: 1
        });

        this._markers.push($marker);

        return $marker;
    },

    /**
     * Adds a marker to the map.
     */
    addMarker: function(latitude, longitude, title, icon, information) {
        var $marker = new google.maps.Marker({
            map: this._map,
            position: new google.maps.LatLng(latitude, longitude),
            title: title
        });

        // add icon
        if (icon) {
            $marker.setIcon(icon);
        }

        // add info window
        if (information) {
            var $infoWindow = new google.maps.InfoWindow({
                content: information,
                maxWidth: 350
            });
            this._addInfoWindowEventListener($marker, $infoWindow);

            $marker.infoWindow = $infoWindow;
            this._infoWindows.push($infoWindow);
        }

        this._markers.push($marker);

        return $marker;
    },

    /**
     * Returns all markers on the map.
     */
    getMarkers: function() {
        return this._markers;
    },

    /**
     * Returns the Google Maps map object.
     */
    getMap: function() {
        return this._map;
    },

    /**
     * Refreshes the map.
     */
    refresh: function() {
        var $center = this._map.getCenter();
        google.maps.event.trigger(this._map, 'resize');
        this._map.setCenter($center);
    },

    /**
     * Refreshes the boundaries of the map to show all markers.
     */
    refreshBounds: function() {
        var $minLatitude = null;
        var $maxLatitude = null;
        var $minLongitude = null;
        var $maxLongitude = null;

        for (var $index in this._markers) {
            var $marker = this._markers[$index];
            var $latitude = $marker.getPosition().lat();
            var $longitude = $marker.getPosition().lng();

            if ($minLatitude === null) {
                $minLatitude = $maxLatitude = $latitude;
                $minLongitude = $maxLongitude = $longitude;
            }
            else {
                if ($minLatitude > $latitude) {
                    $minLatitude = $latitude;
                }
                else if ($maxLatitude < $latitude) {
                    $maxLatitude = $latitude;
                }

                if ($minLongitude > $latitude) {
                    $minLongitude = $latitude;
                }
                else if ($maxLongitude < $longitude) {
                    $maxLongitude = $longitude;
                }
            }
        }

        this._map.fitBounds(new google.maps.LatLngBounds(
            new google.maps.LatLng($minLatitude, $minLongitude),
            new google.maps.LatLng($maxLatitude, $maxLongitude)
        ));
    },

    /**
     * Removes all markers from the map.
     */
    removeMarkers: function() {
        for (var $index in this._markers) {
            this._markers[$index].setMap(null);
        }

        this._markers = [ ];
    },

    /**
     * Changes the map bounds.
     */
    setBounds: function(northEast, southWest) {
        this._map.fitBounds(new google.maps.LatLngBounds(
            new google.maps.LatLng(southWest.latitude, southWest.longitude),
            new google.maps.LatLng(northEast.latitude, northEast.longitude)
        ));
    },

    /**
     * Sets the map center.
     */
    setCenter: function(latitude, longitude) {
        this._map.setCenter(new google.maps.LatLng(latitude, longitude));
    }
});

/**
 * Handles a large map with many markers.
 */
Poi.Map.LargeMap = Poi.Map.GoogleMaps.Map.extend({
    /**
     * additional parameters for various switches
     */
    _additionalParameters: { },

    /**
     * indicates if the maps center can be set by location search
     */
    _locationSearch: null,

    /**
     * selector for the location search input
     */
    _locationSearchInputSelector: null,

    /**
     * cluster handling the markers on the map
     */
    _markerClusterer: null,

    /**
     * switch for inital loading of markers to avoid unneccessary reloads
     */
    _markersLoaded: false,

    /**
     * map bounds after loading of markers
     */
    _bounds: null,


    /**
     * circles
     */
    _circleLocation: null,

    /**
     * buttons
     */
    _buttonCenter: null,
    _buttonCleanup: null,
    _buttonFilter: null,
    _buttonResetFilter: null,

    /**
     * filter and search
     */
    _count: 0,
    _userFilter: null,
    _poiSearch: '',
    _categoryID: 0,


    _loadingOverlay: null,

    /**
     * selected categories
     */
    _selectedCategories: [ ],

    /**
     * saved markers / open markers
     */
    _markerInfoSave: [ ],
    _markerOpen: [ ],

    /**
     * data for direction service
     */
    _directionsService: null,
    _directionsDisplay: null,

    /**
     * search markers
     */
    _searchMarker: [],

    /**
     * categories for ViewablePoiList
     */
    _usedCategories: [],

    /**
     * @see    WCF.Location.GoogleMaps.Map.init()
     */
    init: function(mapContainerID, mapOptions, locationSearchInputSelector, additionalParameters, poiSearch, categoryID) {
        this._super(mapContainerID, mapOptions);

        // hide Google POIs
        var mapOptions = parseInt(mapOptions);
        if (mapOptions) {
            var noPoi = [{
                featureType: "poi",
                elementType: "labels",
                stylers: [{
                    visibility: "off"
                }]
            }];

            this._map.setOptions({
                styles: noPoi
            });
        }

        this._additionalParameters = additionalParameters || { };

        this._poiSearch = poiSearch;
        this._categoryID = parseInt(categoryID);


        // preset, define and disable some buttons
        this._count = 0;
        this._buttonCenter = $('#centerButton');
        this._buttonCleanup = $('#cleanupButton');
        this._buttonFilter = $('#filterButton');
        this._buttonResetFilter = $('#filterResetButton');
        this._buttonCenter.disable();
        this._buttonCleanup.disable();
        this._buttonFilter.disable();
        this._buttonResetFilter.disable();

        this._locationSearchInputSelector = locationSearchInputSelector || '';
        if (this._locationSearchInputSelector) {
            this._locationSearch = new WCF.Location.GoogleMaps.LocationSearch(locationSearchInputSelector, $.proxy(this._locationList, this));
        }

        this._markerClusterer = new MarkerClusterer(this._map, this._markers, {
            maxZoom: 17,
            imagePath: Poi.Map.GoogleMaps.Settings.get('markerClustererImagePath') + 'm'
        });

        this._markerSpiderfier = new OverlappingMarkerSpiderfier(this._map, {
            keepSpiderfied: true,
            markersWontHide: true,
            markersWontMove: true
        });
        this._markerSpiderfier.addListener('click', $.proxy(function(marker) {
            if (marker.infoWindow) {
                if (marker.infoWindow.getMap()) {
                    marker.infoWindow.close();

                    // memorize open markers
                    var index = this._markerOpen.indexOf(marker);
                    if (index > -1) {
                        this._markerOpen.splice(index, 1);
                    }
                }
                else {
                    marker.infoWindow.open(this._map, marker);

                    // memorize open markers
                    this._markerOpen.push(marker);
                }
            }
        }, this));

        this._proxy = new WCF.Action.Proxy({
            showLoadingOverlay: true,
            success: $.proxy(this._success, this)
        });

        google.maps.event.addListener(this._map, 'idle', $.proxy(this._loadMarkers, this));

        // location search / route
        $('#searchButton').click($.proxy(this._search, this));
        $('#routeButton').click($.proxy(this._route, this));

        // center and cleanup button
        this._buttonCenter = $('#centerButton');
        this._buttonCenter.click($.proxy(this._centerBounds, this));
        this._buttonCleanup = $('#cleanupButton');
        this._buttonCleanup.click($.proxy(this._cleanup, this));

        // filter
        this._userFilter = $('#userFilter');
        this._selectedCategories.push(0);
        this._buttonFilter = $('#filterButton');
        this._buttonFilter.click($.proxy(this._filter, this));
        this._buttonResetFilter = $('#filterResetButton');
        this._buttonResetFilter.click($.proxy(this._filterReset, this));
        this._buttonResetFilter.hide();
    },

    /**
     * Handles click on filter reset button.
     */
    _filterReset: function() {
        // category filter
        var $categoryCheckBoxes = document.getElementsByName('category');
        if ($categoryCheckBoxes.length) {
            for (var $i = 0; $i < $categoryCheckBoxes.length; $i++) {
                if (!$categoryCheckBoxes[$i].disabled) {
                    $categoryCheckBoxes[$i].checked = true;
                }
            }
        }

        // user filter
        this._userFilter.val('');

        // filter new
        this._filter();

        // reset filter text and button
        document.getElementById('filterActive').innerHTML = '';
        this._buttonResetFilter.hide();
    },

    /**
     * Handles click on filter button.
     */
    _filter: function() {
        // category filter
        var $categoryCheckBoxes = document.getElementsByName('category');
        var $categories = [];
        for (var $i = 0; $i < $categoryCheckBoxes.length; $i++) {
            if ($categoryCheckBoxes[$i].checked == true) {
                $categories.push(parseInt($categoryCheckBoxes[$i].value));
            }
        }

        // get new marker 
        var $temp = [ ];
        var $count = 0;

        for (var $i = 0; $i < this._markerInfoSave.length; $i++) {
            // user filter
            if (this._userFilter.val() != '') {
                var $username = this._markerInfoSave[$i].username.toLocaleUpperCase();
                var $search = this._userFilter.val().toLocaleUpperCase();
                if (!$username.includes($search)) {
                    continue;
                }
            }

            // category filter
            if ($categories.length) {
                var $poiCategoryID = this._markerInfoSave[$i].categoryID;

                //var $index = -1;
                for (var $k = 0; $k < $categories.length; $k++) {
                    if ($poiCategoryID == parseInt($categories[$k])) {
                        $temp.push(this._markerInfoSave[$i]);
                        $count ++;
                        break;
                    }
                }
            }
        }

        // remove markers
        this._markerClusterer.clearMarkers();
        this._markerSpiderfier.clearMarkers();

        // load markers if any
        this._bounds = null;
        this._bounds = new google.maps.LatLngBounds();

        for (var $i = 0; $i < $temp.length; $i++) {
            var $markerInfo = $temp[$i];

            this.addMarker($markerInfo.latitude, $markerInfo.longitude, $markerInfo.title, $markerInfo.icon, $markerInfo.infoWindow, $markerInfo.dialog, $markerInfo.location);

            // get bounds from all loaded markers
            this._bounds.extend(new google.maps.LatLng($markerInfo.latitude, $markerInfo.longitude));
        }

        // set info to active, show button
        document.getElementById('filterActive').innerHTML = WCF.Language.get('poi.map.filter.active');
        this._buttonResetFilter.show();
    },

    /**
     * Handles clicking on the cleanup button.
     */
    _cleanup: function() {
        // close circles
        if (this._circleLocation) {
            this._circleLocation.setMap(null);
            this._circleLocation = null;
        }

        // remove search pins
        if (this._searchMarker.length) {
            for (var $i = 0, $length = this._searchMarker.length; $i < $length; $i++) {
                this._searchMarker[$i].setMap(null);
            }
            this._searchMarker = [ ];
        }

        // close info windows by filtering again
        if (this._markerOpen.length) {
            this._markerOpen = [ ];
        }

        // filter again to reset all incl. bounds
        this._filter();

        // remove direction
        if (this._directionsDisplay !== null) {
            this._directionsDisplay.setMap(null);
            this._directionsDisplay = null;
        }
        if (this._directionsService !== null) {
            this._directionsService = null;
        }
    },

    /**
     * Handles clicking on the route button.
     */
    _route: function() {
        // remove existing
        if (this._directionsDisplay !== null) {
            this._directionsDisplay.setMap(null);
            this._directionsDisplay = null;
        }
        if (this._directionsService !== null) {
            this._directionsService = null;
        }

        // abort if no sufficient points
        if (this._searchMarker.length + this._markerOpen.length < 2) {
            $('<header class="boxHeadline">' + WCF.Language.get('poi.map.route.error') + '</header>').wcfDialog({ title: WCF.Language.get('wcf.global.error.title') });
        }
        else {
            // search results are start, end and then way points
            // open markers are way points if search results
            var $start = null;
            var $end = null;
            var $limit = 0;
            var $count = 0;
            var $temp = 0;
            var $tempArray = [ ];
            var $wayPoints = [ ];

            if (this._searchMarker.length) {
                $limit = 0;
                while($count < 25 && $limit < this._searchMarker.length) {
                    $temp = this._searchMarker[$limit].getPosition();
                    $wayPoints.push({
                        location: $temp,
                        stopover: true
                    });
                    $count ++;
                    $limit ++;
                }
            }
            if (this._markerOpen.length) {
                $limit = 0;
                while($count < 25 && $limit < this._markerOpen.length) {
                    $temp = this._markerOpen[$limit].getPosition();
                    $wayPoints.push({
                        location: $temp,
                        stopover: true
                    });
                    $count ++;
                    $limit ++;
                }
            }

            if ($wayPoints.length > 1) {
                $start = $wayPoints[0].location;
                $end = $wayPoints[1].location;
                $wayPoints.splice(0, 2);
            }

            // get directions
            this._directionsService = new google.maps.DirectionsService();
            this._directionsDisplay = new google.maps.DirectionsRenderer({
                suppressMarkers: true
            });
            this._directionsDisplay.setMap(this.getMap());
            var $directionsDisplay = this._directionsDisplay;

            var $request = {
                    origin:         $start,
                    destination:     $end,
                    waypoints:        $wayPoints,
                    travelMode:     'DRIVING'
            };

            this._directionsService.route($request, function(response, status) {
                if (status == 'OK') {
                    $directionsDisplay.setDirections(response);

                // display dialog with route information
                var $legs = response.routes[0].legs;
                var points = response.geocoded_waypoints.length;
                var $waypoints = [ ];
                var $distance = 0
                var $wayString = '';

                var $text = '<div><p>' + WCF.Language.get('poi.map.route.found') + '</p><br>';

                for (var $i = 0, $length = $legs.length; $i < $length; $i++) {
                    if ($waypoints.indexOf($legs[$i].start_address) < 0) {
                        $waypoints.push($legs[$i].start_address);
                        $wayString += encodeURIComponent($legs[$i].start_address) + '/';
                    }
                    if ($waypoints.indexOf($legs[$i].end_address) < 0) {
                        $waypoints.push($legs[$i].end_address);
                        $wayString += encodeURIComponent($legs[$i].end_address) + '/';
                    }

                    $distance += $legs[$i].distance.value;

                    $text = $text.concat('<p>' + $legs[$i].start_address + '</p>');
                    $text = $text.concat('<p>' + $legs[$i].end_address + '</p>');
                    $text = $text.concat('<p>' + $legs[$i].distance.text + '</p><br>');
                }

                $text = $text.concat('<p>' + WCF.Language.get('poi.map.route.waypoints') + ' ' + points + '</p>');
                $text = $text.concat('<p>' + WCF.Language.get('poi.map.route.distance') + ' ' + parseInt($distance / 1000) + '</p>');

                $text = $text.concat('<div class="formSubmit"><a href="https://www.google.com/maps/dir/' + $wayString + ' "{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}{if EXTERNAL_LINK_REL_NOFOLLOW} rel="nofollow"{/if}" class="button" > <span>' + WCF.Language.get('poi.map.route.open') + '</span></a></div>');

                $('<div>' + $text + '</div>').wcfDialog({ title: WCF.Language.get('poi.map.route')});
                }
                else {
                    $('<header class="boxHeadline">' + WCF.Language.get('poi.map.search.error.direction') + '</header>').wcfDialog({ title: WCF.Language.get('wcf.global.error.title') });
                }
            });
        }
    },

    /**
     * Handles clicking on the center button.
     */
    _centerBounds: function() {
        // center iaw configuration
        if (Poi.Map.GoogleMaps.Settings.get('centerOnOpen')) {
            if (this._bounds) {
                this.getMap().fitBounds(this._bounds);
            }
        }
        else {
            this._map.setCenter(new google.maps.LatLng(Poi.Map.GoogleMaps.Settings.get('defaultLatitude'), Poi.Map.GoogleMaps.Settings.get('defaultLongitude')));
            this._map.setZoom(Poi.Map.GoogleMaps.Settings.get('zoom'));
        }
    },

    /**
     * Handles clicking on the search button.
     */
    _search: function() {
        var $location = document.getElementById('geocode').value;

        if (!$location) {
            $('<header class="boxHeadline">' + WCF.Language.get('poi.map.search.error.empty') + '</header>').wcfDialog({ title: WCF.Language.get('wcf.global.error.title') });
        }
        else {
            var $proxy = new WCF.Action.Proxy({
                autoSend: true,
                showLoadingOverlay: true,
                data: {
                    actionName: 'search',
                    className: 'poi\\data\\poi\\PoiAction',
                    parameters: {
                        location: $location
                    },
                },
                success: $.proxy(this._searchSuccess, this)
            });
        }
    },

    /**
     * Handles search result.
     */
    _searchSuccess: function(data, textStatus, jqXHR) {
        var $location = document.getElementById('geocode').value;
        var $data = data.returnValues;

        if ($location) {
            if (typeof $data.locationLat == 'undefined') {
                $('<header class="boxHeadline">' + WCF.Language.get('poi.map.search.error.locationNotFound') + '</header>').wcfDialog({ title: WCF.Language.get('wcf.global.error.title') });
            }
        }
        else {
            if (typeof $data.locationLat === 'undefined') {
                $('<header class="boxHeadline">' + WCF.Language.get('poi.map.search.error.locationNotFound') + '</header>').wcfDialog({ title: WCF.Language.get('wcf.global.error.title') });
            }
        }

        // display found location
        var $locationFound = 0;

        if ($location && typeof $data.locationLat !== 'undefined') {
            if (this._circleLocation) {
                this._circleLocation.setMap(null);
                this._circleLocation = null;
            }
            this._circleLocation = new google.maps.Circle({
                strokeColor: '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 1,
                fillColor: '#FF0000',
                fillOpacity: 0.1,
                map: this._map,
                center: new google.maps.LatLng($data.locationLat, $data.locationLng),
                radius: 2500,
                editable: false,
                visible: true
            });
            this._bounds.extend(new google.maps.LatLng($data.locationLat, $data.locationLng));
            $locationFound = 1;

            var latLng = {lat: $data.locationLat, lng: $data.locationLng};
            var $found = 0;
            if (this._searchMarker.length) {
                for (var $i = 0, $length = this._searchMarker.length; $i < $length; $i++) {
                    var lat = this._searchMarker[$i].getPosition().lat();
                    var lng = this._searchMarker[$i].getPosition().lng();
                    lat = lat.toFixed(7);
                    lng = lng.toFixed(7);

                    if (lat == latLng.lat && lng == latLng.lng) {
                        $found = 1;
                        break;
                    }
                }
            }
            if ($found == 0) {
                var marker = new google.maps.Marker({
                    position:     new google.maps.LatLng(latLng.lat, latLng.lng),
                    map:         this._map,
                    icon:        $data.icon,
                    title:         $location,
                    zIndex:        999999
                });
                marker.setMap(this._map);
                this._searchMarker.push(marker);
            }
        }

        if ($locationFound) {
            this._map.fitBounds(this._circleLocation.getBounds());
        }
    },

    /**
     * @see    WCF.Location.GoogleMaps.Map.addMarker()
     */
    _addInfoWindowEventListener: function(marker, infoWindow) {

    },

    /**
     * Fills location input based on a location search result.
     */
    _locationList: function(data) {
        $(this._locationSearchInputSelector).val(data.label);
    },

    /**
     * Loads markers only once for fitted bounds for all
     */
    _loadMarkers: function() {
        if (this._markersLoaded === false) {
            this._proxy.setOption('data', {
                className: 'poi\\data\\poi\\PoiAction',
                actionName: 'getMapMarkers',
                parameters: {
                    poiSearch: this._poiSearch,
                    categoryID: this._categoryID

                }
            });

            this._proxy.sendRequest();
            this._markersLoaded = true;
            return true;
        }
        return false;
    },

    /**
     * Handles a successful AJAX request.
     */
    _success: function(data, textStatus, jqXHR) {
        // save bounds in any case
        this._bounds = new google.maps.LatLngBounds();

        if (data.returnValues && data.returnValues.markers) {
            for (var $i = 0, $length = data.returnValues.markers.length; $i < $length; $i++) {
                var $markerInfo = data.returnValues.markers[$i];

                this.addMarker($markerInfo.latitude, $markerInfo.longitude, $markerInfo.title, $markerInfo.icon, $markerInfo.infoWindow, $markerInfo.dialog, $markerInfo.location);

                // save marker info
                this._markerInfoSave.push($markerInfo);
                this._count ++;

                // save used categories
                this._usedCategories.push($markerInfo.categoryID);

                // get bounds from all loaded markers
                this._bounds.extend(new google.maps.LatLng($markerInfo.latitude, $markerInfo.longitude));
            }
        }

        // center map if configured
        if ($length && Poi.Map.GoogleMaps.Settings.get('centerOnOpen')) {
            this.getMap().fitBounds(this._bounds);
        }

        //disable categories on string search
        if (this._poiSearch != '') {
            var checkboxes = document.getElementsByName("category");
            for (var i = 0; i < checkboxes.length; i++) {
                if (!this._usedCategories.includes(checkboxes[$i].value)) {
                    checkboxes[$i].disabled = true;
                }
            }
        }

        // show disabled buttons
        this._buttonCenter.enable();
        this._buttonCleanup.enable();
        this._buttonFilter.enable();
        this._buttonResetFilter.enable();
    },

    /**
     * @see    WCF.Location.GoogleMaps.Map.addMarker()
     */
    addMarker: function(latitude, longitude, title, icon, information, dialog, location) {
        var $information = $(information).get(0);
        var $marker = this._super(latitude, longitude, title, icon, $information);

        this._markerClusterer.addMarker($marker);
        this._markerSpiderfier.addMarker($marker);

        if (dialog) {
            // skip, want to scroll in infoWindow / remove if staying with spiderfyer
        }

        return $marker.infoWindow;
    }
});

/**
 * Extends location input for displaying coordinates.
 */
Poi.Map.GoogleMaps.LocationInput = WCF.Location.GoogleMaps.LocationInput.extend({
    /**
     * Initializes a new WCF.Location.GoogleMaps.LocationInput object.
     */
    init: function(mapContainerID, mapOptions, searchInput, latitude, longitude, actionClassName) {
        this._searchInput = searchInput;

        if (actionClassName) {
            this._map = new WCF.Location.GoogleMaps.SuggestionMap(mapContainerID, mapOptions, actionClassName);
            this._map.setSuggestionSelectionCallback($.proxy(this._useSuggestion, this));
        }
        else {
            this._map = new WCF.Location.GoogleMaps.Map(mapContainerID, mapOptions);
        }

        this._locationSearch = new WCF.Location.GoogleMaps.LocationSearch(searchInput, $.proxy(this._setMarkerByLocation, this));

        if (latitude && longitude) {
            this._marker = this._map.addDraggableMarker(latitude, longitude);
        }
        else {
            this._marker = this._map.addDraggableMarker(WCF.Location.GoogleMaps.Settings.get('defaultLatitude'), WCF.Location.GoogleMaps.Settings.get('defaultLongitude'));

            WCF.Location.Util.getLocation($.proxy(function(latitude, longitude) {
                if (latitude !== undefined && longitude !== undefined) {
                    WCF.Location.GoogleMaps.Util.moveMarker(this._marker, latitude, longitude);
                    WCF.Location.GoogleMaps.Util.focusMarker(this._marker);
                }
            }, this));
        }

        this._marker.addListener('dragend', $.proxy(this._updateLocation, this));

        // location button
        this._buttonFind = $('.jsButtonFind');
        this._buttonFind.click($.proxy(this._find, this));

        // hide error
        var error = document.getElementById('coordError');
        error.style.display = 'none';
    },

    /**
     * find location from coordinates
     */
    _find: function() {
        var lat = document.getElementById('dirLatitude').value;
        var lng = document.getElementById('dirLongitude').value;

        var error = document.getElementById('coordError');

        lat = parseFloat(lat.replace(',', '.'));
        lng = parseFloat(lng.replace(',', '.'));

        if (isNaN(lat) || isNaN(lng)) {
            error.style.display = '';
        }
        else {
            error.style.display = 'none';
            WCF.Location.GoogleMaps.Util.moveMarker(this._marker, lat, lng);
            WCF.Location.GoogleMaps.Util.focusMarker(this._marker);
            this._updateLocation();
        }
    },

    /**
     * Updates location on marker position change.
     */
    _updateLocation: function() {
        WCF.Location.GoogleMaps.Util.reverseGeocoding($.proxy(function(result) {
            if (result !== null) {
                $(this._searchInput).val(result);
            }
        }, this), this._marker);

        var lat = this._marker.getPosition().lat();
        var lng = this._marker.getPosition().lng();

        // update lat long
        document.getElementById('dirLatitude').value = lat.toFixed(5);
        document.getElementById('dirLongitude').value = lng.toFixed(5);
    },

    /**
     * Sets the marker based on an entered location.
     */
    _setMarkerByLocation: function(data) {
        this._marker.setPosition(data.location);
        WCF.Location.GoogleMaps.Util.focusMarker(this._marker);

        $(this._searchInput).val(data.label);

        var lat = this._marker.getPosition().lat();
        var lng = this._marker.getPosition().lng();

        // update lat long
        document.getElementById('dirLatitude').value = lat.toFixed(5);
        document.getElementById('dirLongitude').value = lng.toFixed(5);
    }
});
