define([
    'jquery',
    'underscore',
    'knockout',
    'mage/translate',
    'Magento_PageBuilder/js/events',
    'Magento_PageBuilder/js/content-type/preview-collection',
    'Magento_PageBuilder/js/content-type-factory',
    'Magento_PageBuilder/js/config',
    'Magento_PageBuilder/js/content-type-menu/option',
    'mage/accordion',
], function ($, _, ko, $t, events, PreviewCollection, createContentType, pageBuilderConfig, option) {
    'use strict';

    /**
     * @param parent
     * @param config
     * @param stageId
     * @constructor
     */
    function Preview(parent, config, stageId) {
        PreviewCollection.call(this, parent, config, stageId);
    }

    Preview.prototype = Object.create(PreviewCollection.prototype);

    Preview.prototype.element = null;

    Preview.prototype.bindEvents = function bindEvents() {
        const self = this;

        PreviewCollection.prototype.bindEvents.call(this);

        events.on("gc-row-service-links:dropAfter", function (args) {
            if (args.id === self.contentType.id && self.contentType.children().length === 0) {
                self.addHeader();
                self.addPageLinks();
                self.addRichText();
            }
        });
    };

    Preview.prototype.addHeader = function () {
        const self = this;
        createContentType(
            pageBuilderConfig.getContentTypeConfig("gc-heading"),
            this.contentType,
            this.contentType.stageId,
            {}
        ).then(function (container) {
            self.contentType.addChild(container);
        });
    };

    /**
     * Add a page link component
     */
    Preview.prototype.addPageLinks = function () {
        const self = this;
        createContentType(
            pageBuilderConfig.getContentTypeConfig("gc-page-links"),
            this.contentType,
            this.contentType.stageId,
            {}
        ).then(function (container) {
            self.contentType.addChild(container);
        });
    };

    /**
     * Add a text component
     */
    Preview.prototype.addRichText = function () {
        const self = this;
        createContentType(
            pageBuilderConfig.getContentTypeConfig("gc-richtext"),
            this.contentType,
            this.contentType.stageId,
            {}
        ).then(function (container) {
            self.contentType.addChild(container);
        });
    };

    /**
     * Return content menu options
     *
     * @returns {object}
     */
    Preview.prototype.retrieveOptions = function () {
        const self = this;
        const options = PreviewCollection.prototype.retrieveOptions.call(this);

        options.add = new option({
            preview: this,
            icon: "<i class='icon-pagebuilder-add'></i>",
            title: "Add Page Links",
            action: self.addPageLinks,
            classes: ["add-child"],
            sort: 10
        });
        return options;
    };

    /**
     * Set root element
     *
     * @returns {void}
     */
    Preview.prototype.afterRender = function (element) {
        this.element = element;
    };

    /**
     * Check if content type is container
     *
     * @returns {boolean}
     */
    Preview.prototype.isContainer = function () {
        return true;
    };

    return Preview;
});
