define([
    'jquery',
    'underscore',
    'knockout',
    'mage/translate',
    'Magento_PageBuilder/js/events',
    'Magento_PageBuilder/js/content-type/preview-collection',
    'Magento_PageBuilder/js/content-type-factory',
    'Magento_PageBuilder/js/config'
], function ($, _, ko, $t, events, PreviewCollection, createContentType, pageBuilderConfig) {
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

    /**
     * Root element
     */
    Preview.prototype.element = null;

    /**
     * Bind events to add empty FAQ item when FAQ added and reinitialize accordion when FAQ item added
     */
    Preview.prototype.bindEvents = function bindEvents() {
        const self = this;

        PreviewCollection.prototype.bindEvents.call(this);

        events.on("gc-row-quote:dropAfter", function (args) {
            if (args.id === self.contentType.id && self.contentType.children().length === 0) {
                self.addRichText();
            }
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
     * Set root element
     *
     * @returns {void}
     */
    Preview.prototype.afterRender = function (element) {
        this.element = element;
    };

    return Preview;
});
