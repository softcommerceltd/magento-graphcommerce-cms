/*eslint-disable */
/* jscs:disable */
define(["underscore", "Magento_PageBuilder/js/utils/object"], function (_underscore, _object) {
    /**
     * @api
     */
    const CreateLinkValue = /*#__PURE__*/function () {
        "use strict";

        function CreateLinkValue() {
            this.widgetParamsByLinkType = {
                category: {
                    id_path: "category/:href",
                },
                product: {
                    id_path: "product/:href",
                },
                page: {
                    page_id: ":href",
                }
            };
        }

        const _proto = CreateLinkValue.prototype;

        _proto.fromDom = function fromDom(value) {
            return value;
        };

        _proto.toDom = function toDom(name, data) {
            const link = (0, _object.get)(data, name);
            let href = "";

            if (!link) {
                return href;
            }

            const linkType = link.type;
            const isHrefId = !isNaN(parseInt(link[linkType], 10));

            if (isHrefId && link) {
                href = this.convertToWidget(link[linkType], linkType);
            } else if (typeof link[linkType] === "string") {
                href = link[linkType];
            }

            return href;
        };

        _proto.convertToWidget = function convertToWidget(href, linkType) {
            if (!href || !this.widgetParamsByLinkType[linkType]) {
                return href;
            }

            const attributesString = _underscore.map(this.widgetParamsByLinkType[linkType], function (val, key) {
                return key + "='" + val.replace(":href", href) + "'";
            }).join(" ");

            return "{{widget " + attributesString + "}}";
        };

        return CreateLinkValue;
    }();

    return CreateLinkValue;
});
//# sourceMappingURL=link-href.js.map
