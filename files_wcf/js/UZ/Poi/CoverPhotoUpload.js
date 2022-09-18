/**
 * Uploads a cover photo for Pois.
 * 
 * @author        2017-2022 Zaydowicz
 * @license        GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package        com.uz.poi
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Upload", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Ui/Confirmation", "WoltLabSuite/Core/Dom/Util"], function (require, exports, tslib_1, Upload_1, Language, UiConfirmation, Util_1) {
    "use strict";

    Upload_1 = tslib_1.__importDefault(Upload_1);
    Language = tslib_1.__importStar(Language);
    UiConfirmation = tslib_1.__importStar(UiConfirmation);
    Util_1 = tslib_1.__importDefault(Util_1);

    class PoiCoverPhotoUpload extends Upload_1.default {
        constructor() {
            super("coverPhotoUploadButtonContainer", "coverPhotoUploadPreview", {
                action:     "upload",
                className:     "poi\\data\\cover\\photo\\CoverPhotoAction",
            });
            this.coverPhotoInput = undefined;
            this.deleteButton = this._buttonContainer.querySelector(".jsButtonDeleteCoverPhoto");
            this.deleteButton.addEventListener("click", (ev) => this.deleteCoverPhoto(ev));
        }

        _success(uploadId, data) {
            Util_1.default.innerError(this._buttonContainer, data.returnValues.errorMessage);

            this._target.innerHTML = "";
            if (data.returnValues.coverPhotoID) {
                this.getCoverPhotoInput().value = data.returnValues.coverPhotoID.toString();
                const img = document.createElement("img");
                img.classList.add("contentItemImagePreview");
                img.src = data.returnValues.url;
                this._target.appendChild(img);
                Util_1.default.show(this.deleteButton);
            }
            else {
                Util_1.default.show(this.deleteButton);
            }
        }

        deleteCoverPhoto(event) {
            event.preventDefault();

            UiConfirmation.show({
                message: Language.get("wcf.image.coverPhoto.delete.confirmMessage"),
                confirm: () => {
                    this.getCoverPhotoInput().value = "0";
                    this._target.innerHTML = "";
                    Util_1.default.hide(this.deleteButton);
                },
            });
        }

        getCoverPhotoInput() {
            if (!this.coverPhotoInput) {
                const form = this._button.closest("form");
                const formSubmit = form.querySelector(".formSubmit");
                let input = formSubmit.querySelector('input[name="coverPhotoID"]');
                if (input === null) {
                    input = document.createElement("input");
                    input.name = "coverPhotoID";
                    input.type = "hidden";
                    input.value = "0";
                    formSubmit.appendChild(input);
                }
                this.coverPhotoInput = input;
            }

            return this.coverPhotoInput;
        }
    }

    return PoiCoverPhotoUpload;
});
