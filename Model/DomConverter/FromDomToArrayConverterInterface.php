<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\DomConverter;

use DOMElement;

/**
 * Interface FromDomToArrayConverterInterface
 * use do to convert DOM to array
 */
interface FromDomToArrayConverterInterface
{
    /**
     * @param DOMElement $DOMElement
     * @return array
     */
    public function execute(DOMElement $DOMElement): array;
}
