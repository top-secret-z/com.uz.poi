/**
 * Handles deletion of a marker.
 * 
 * @author        2017-2022 Zaydowicz
 * @license        GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package        com.uz.poi
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Ui/Confirmation", "WoltLabSuite/Core/Ui/Notification"], function (require, exports, tslib_1, Ajax, Language, UiConfirmation, UiNotification) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;

    Ajax = tslib_1.__importStar(Ajax);
    Language = tslib_1.__importStar(Language);
    UiConfirmation = tslib_1.__importStar(UiConfirmation);
    UiNotification = tslib_1.__importStar(UiNotification);

    class UZPoiAcpMarkerDelete {
        constructor() {
            var buttons = document.querySelectorAll('.jsDeleteButton');
            for (var i = 0, length = buttons.length; i < length; i++) {
                buttons[i].addEventListener("click", (ev) => this._click(ev));
            }
        }

        _click(event) {
            event.preventDefault();

            var filename = event.currentTarget.id;

            UiConfirmation.show({
                confirm: function() {
                    Ajax.apiOnce({
                        data: {
                            actionName: 'deleteMarker',
                            className: 'poi\\data\\poi\\PoiAction',
                            parameters: {
                                filename: filename
                            }
                        },
                        success: function() {
                            UiNotification.show();
                            window.location.reload();
                        }
                    });
                },
                message: Language.get('poi.acp.marker.delete.sure')
            });
        }
    }

    let uZPoiAcpMarkerDelete;
    function init() {
        if (!uZPoiAcpMarkerDelete) {
            uZPoiAcpMarkerDelete = new UZPoiAcpMarkerDelete();
        }
    }
    exports.init = init;
});
