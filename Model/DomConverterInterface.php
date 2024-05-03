<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model;

use DOMElement;

/**
 * Interface DomConverterInterface
 * use do to convert DOM to array
 */
interface DomConverterInterface
{
    /**
     * @param array $data
     * @return string
     */
    public function fromArrayToDom(array $data): string;

    /**
     * @param DOMElement $DOMElement
     * @return array
     */
    public function fromDomToArray(DOMElement $DOMElement): array;
}
