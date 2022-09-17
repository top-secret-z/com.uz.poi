/**
 * Dialog to select poi visit
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.poi
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/Dom/Traverse", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Ui/Dialog", "WoltLabSuite/Core/Date/Picker", "WoltLabSuite/Core/Ui/Notification"], function (require, exports, tslib_1, Ajax, Util_1, DomTraverse, Language, Dialog_1, Picker_1, UiNotification) {
	"use strict";
	Object.defineProperty(exports, "__esModule", { value: true });
	exports.init = void 0;
	
	Ajax = tslib_1.__importStar(Ajax);
	Util_1 = tslib_1.__importDefault(Util_1);
	DomTraverse = tslib_1.__importStar(DomTraverse);
	Language = tslib_1.__importStar(Language);
	Dialog_1 = tslib_1.__importDefault(Dialog_1);
	Picker_1 = tslib_1.__importDefault(Picker_1);
	UiNotification = tslib_1.__importStar(UiNotification);
	
	class UzPoiVisit {
		constructor(poiID, visitTime) {
			this._poiID = poiID;
			this._visitTime = visitTime;
			
			var button = document.querySelector('.jsPoiVisit');
			button.addEventListener("click", (ev) => this._click(ev));
		}
		
		_click(event) {
			event.preventDefault();
			
			Ajax.api(this, {
				actionName: 'prepareVisit',
				parameters: {
					poiID: this._poiID
				}
			});
		}
		
		_submit() {
			var visitTime = 0;
			
			var timeInput = document.querySelector('.jsVisitError');
			var timeError = DomTraverse.nextByClass(timeInput, 'innerError');
			
			if (timeError) {
				timeError.remove();
				timeInput.closest('dl').classList.remove('formError');
			}
			
			if (document.getElementById('visitEnable').checked) {
				visitTime = Picker_1.default.getValue('visitTime');
				if (visitTime == '') {
					timeError = document.createElement('small');
					timeError.className = 'innerError';
					timeError.innerText = Language.get('wcf.global.form.error.empty');
					Util_1.default.insertAfter(timeError, timeInput);
					timeInput.closest('dl').classList.add('formError');
					
					return;
				}
			}
			
			Ajax.api(this, {
				actionName:	'saveVisit',
				parameters:	{
					poiID:		this._poiID,
					visitTime:	visitTime
				}
			});
		}
		
		_ajaxSuccess(data) {
			switch (data.actionName) {
				case 'prepareVisit':
					this._render(data);
					break;
				case 'saveVisit':
					UiNotification.show(Language.get('poi.poi.visit.success'));
					Dialog_1.default.close(this);
					window.location.reload();
					break;
			}
		}
		
		_render(data) {
			Dialog_1.default.open(this, data.returnValues.template);
			
			var submitButton = document.querySelector('.jsSubmitVisit');
			submitButton.addEventListener("click", (ev) => this._submit(ev));
		}
		
		_ajaxSetup() {
			return {
				data: {
					className: 'poi\\data\\poi\\PoiAction',
				}
			};
		}
		
		_dialogSetup() {
			return {
				id: 'poiVisitDialog',
				options: { 
					onClose: function () { 
						Picker_1.default.destroy('visitTime');
					},
					onShow: (function (content) {
						document.getElementById('visitEnable').addEventListener('change', function () {
							Util_1.default.toggle(document.getElementById('visitTimeContainer'));
						});
					}).bind(this),
					title: Language.get('poi.poi.visit.dialog.title') 
				},
				source: null
			};
		}
	}
	
	let uzPoiVisit;
	function init(poiID, visitTime) {
		if (!uzPoiVisit) {
			uzPoiVisit = new UzPoiVisit(poiID, visitTime);
		}
	}
	exports.init = init;
});