<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * {@inheritdoc}
 */
class CmsRowContentInterfaceTypeComposite implements TypeResolverInterface
{
    /**
     * @var array
     */
    private array $contentTypeIdMapping;

    /**
     * @param array $contentTypeIdMapping
     */
    public function __construct(array $contentTypeIdMapping = [])
    {
        $this->contentTypeIdMapping = $contentTypeIdMapping;
    }

    /**
     * {@inheritdoc}
     * @throws GraphQlInputException
     */
    public function resolveType(array $data) : string
    {
        if (!$typeId = $data['typeId'] ?? null) {
            throw new GraphQlInputException(
                __('A field with the name: [typeId] is required.')
            );
        }

        if (!isset($this->contentTypeIdMapping[$typeId])) {
            throw new GraphQlInputException(
                __('A content with type ID: [%1] isn\'t implemented for [CmsRowContentInterface]', $typeId)
            );
        }

        return $this->contentTypeIdMapping[$typeId];
    }
}
