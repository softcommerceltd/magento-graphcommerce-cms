<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model;

use DOMElement;
use DOMText;
/**
 * @inheritDoc
 */
class DomConverter implements DomConverterInterface, MetadataInterface
{
    /**
     * @inheritDoc
     */
    public function fromArrayToDom(array $data): string
    {
        return '';
    }

    /**
     * @param DOMElement $DOMElement
     * @return array
     */
    public function fromDomToArray(DOMElement $DOMElement): array
    {
        return $this->processDomToArray($DOMElement);
    }

    /**
     * @param DOMElement|DOMText $element
     * @return array
     */
    public function processDomToArray(DOMElement|DOMText $element): array
    {
        $result = [];

        foreach ($element->attributes ?: [] as $attribute) {
            if ($attribute->name === self::DATA_APPEARANCE) {
                $result[self::APPEARANCE] = $attribute->value;
            }

            if ($attribute->name === self::DATA_CONTENT_TYPE) {
                $result[self::TYPE_ID] = $attribute->value;
            }

            if (!isset($result[self::TYPE_ID]) && $attribute->name === self::DATA_ELEMENT) {
                $result[self::TYPE_ID] = $attribute->value;
            }
        }

        if ($element->nodeType == XML_ELEMENT_NODE) {
            $result[self::NODE_NAME] = $element->nodeName;
            if ($element->childElementCount > 0) {
                foreach ($element->childNodes as $childNode) {
                    $nodeData = $this->processDomToArray($childNode);
                    $result[self::CHILDREN][] = $nodeData;
                }
            } elseif (null !== $element->nodeValue) {
                $result[self::CONTENT] = $element->nodeValue;
            }

            return $result;
        }

        if (in_array($element->nodeType, [XML_TEXT_NODE, XML_CDATA_SECTION_NODE])
            && null !== $element->nodeValue
        ) {
            $result[self::NODE_NAME] = self::TEXT;
            $result[self::CONTENT] = $element->nodeValue;
        }

        return $result;
    }
}
