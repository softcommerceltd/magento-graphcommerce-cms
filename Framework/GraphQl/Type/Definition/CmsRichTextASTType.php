<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Framework\GraphQl\Type\Definition;

use GraphQL\Type\Definition\ScalarType;

/**
 * @inheritDoc
 */
class CmsRichTextASTType extends ScalarType
{
    public string $name = 'CmsRichTextAST';
    public ?string $description = 'Slate-compatible RichText AST';

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
