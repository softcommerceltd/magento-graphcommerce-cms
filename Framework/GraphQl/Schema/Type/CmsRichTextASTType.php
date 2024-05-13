<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Framework\GraphQl\Schema\Type;

use GraphQL\Type\Definition\ScalarType;
use Magento\Framework\GraphQl\Schema\Type\OutputTypeInterface;
use SoftCommerce\GraphCommerceCms\Framework\GraphQl\Config\Element\Scalar;

/**
 * @inheritDoc
 */
class CmsRichTextASTType extends ScalarType implements OutputTypeInterface
{
    public string $name = 'CmsRichTextAST';
    public ?string $description = 'Slate-compatible RichText AST';

    /**
     * @param Scalar $configElement
     */
    public function __construct(Scalar $configElement)
    {
        $config = [
            'name' => $configElement->getName(),
            'description' => $configElement->getDescription()
        ];

        parent::__construct($config);
    }

    /**
     * @param $value
     * @return array
     */
    public function serialize($value): array
    {
        return $value;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function parseValue($value): mixed
    {
        return $value;
    }

    /**
     * @param $valueNode
     * @param array|null $variables
     * @return string
     */
    public function parseLiteral($valueNode, ?array $variables = null): string
    {
        return $valueNode->value;
    }
}
