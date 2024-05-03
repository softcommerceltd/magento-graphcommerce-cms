/*eslint-disable */
/* jscs:disable */

function _inheritsLoose(subClass, superClass) { subClass.prototype = Object.create(superClass.prototype); subClass.prototype.constructor = subClass; _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

define([
    "jquery",
    "knockout",
    "mage/translate",
    "Magento_PageBuilder/js/events",
    "underscore",
    "Magento_PageBuilder/js/config",
    "Magento_PageBuilder/js/content-type-menu/hide-show-option",
    "Magento_PageBuilder/js/content-type/preview"
], function (_jquery, _knockout, _translate, _events, _underscore, _config, _hideShowOption, _preview) {
    /**
     * Copyright © Magento, Inc. All rights reserved.
     * See COPYING.txt for license details.
     */

    /**
     * @api
     */
    const Preview = /*#__PURE__*/function (_preview2) {
        "use strict";

        _inheritsLoose(Preview, _preview2);

        /**
         * @inheritdoc
         */
        function Preview(contentType, config, observableUpdater) {
            let _this;

            _this = _preview2.call(this, contentType, config, observableUpdater) || this;
            _this.displayPreview = _knockout.observable(false);
            _this.previewElement = _jquery.Deferred();
            _this.widgetUnsanitizedHtml = _knockout.observable();
            _this.slidesToShow = 5;
            _this.productItemSelector = ".product-item";
            _this.centerModeClass = "center-mode";
            _this.messages = {
                EMPTY: (0, _translate)("Empty Products"),
                NO_RESULTS: (0, _translate)("No products were found matching your condition"),
                LOADING: (0, _translate)("Loading..."),
                UNKNOWN_ERROR: (0, _translate)("An unknown error occurred. Please try again.")
            };
            _this.ignoredKeysForBuild = ["margins_and_padding", "border", "border_color", "border_radius", "border_width", "css_classes", "text_align"];
            _this.placeholderText = _knockout.observable(_this.messages.EMPTY);

            return _this;
        }

        const _proto = Preview.prototype;

        _proto.retrieveOptions = function retrieveOptions() {
            const options = _preview2.prototype.retrieveOptions.call(this);

            options.hideShow = new _hideShowOption({
                preview: this,
                icon: _hideShowOption.showIcon,
                title: _hideShowOption.showText,
                action: this.onOptionVisibilityToggle,
                classes: ["hide-show-content-type"],
                sort: 40
            });
            return options;
        };

        _proto.onAfterRender = function onAfterRender(element) {
            this.element = element;
            this.previewElement.resolve(element);
        };

        _proto.afterObservablesUpdated = function afterObservablesUpdated() {
            const _this2 = this;

            _preview2.prototype.afterObservablesUpdated.call(this);

            const data = this.contentType.dataStore.getState();

            if (this.hasDataChanged(this.previousData, data)) {
                this.displayPreview(false);

                if (typeof data.conditions_encoded !== "string" || data.conditions_encoded.length === 0) {
                    this.placeholderText(this.messages.EMPTY);
                    return;
                }

                const url = _config.getConfig("preview_url");
                const requestConfig = {
                    method: "POST",
                    data: {
                        role: 'products',
                        directive: this.data.main.html()
                    }
                };
                this.placeholderText(this.messages.LOADING);

                _jquery.ajax(url, requestConfig).done(function (response) {
                    if (typeof response.data !== "object" || !Boolean(response.data.content)) {
                        _this2.placeholderText(_this2.messages.NO_RESULTS);
                        return;
                    }

                    if (response.data.error) {
                        _this2.widgetUnsanitizedHtml(response.data.error);
                    } else {
                        _this2.widgetUnsanitizedHtml(response.data.content);
                        _this2.displayPreview(true);
                    }

                    _this2.previewElement.done(function () {
                        (0, _jquery)(_this2.element).trigger("contentUpdated");
                    });
                }).fail(function () {
                    _this2.placeholderText(_this2.messages.UNKNOWN_ERROR);
                });
            }

            this.previousData = Object.assign({}, data);
        };

        _proto.hasDataChanged = function hasDataChanged(previousData, newData) {
            previousData = _underscore.omit(previousData, this.ignoredKeysForBuild);
            newData = _underscore.omit(newData, this.ignoredKeysForBuild);
            return !_underscore.isEqual(previousData, newData);
        };

        return Preview;
    }(_preview);

    return Preview;
});
//# sourceMappingURL=preview.js.map
