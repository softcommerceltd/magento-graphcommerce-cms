<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * {@inheritdoc}
 */
class CmsPageLinkResolver implements ResolverInterface
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
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        return [];
    }
}
