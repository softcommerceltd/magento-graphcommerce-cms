<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Framework\GraphQl\Config;

use Magento\Framework\Config\ReaderInterface;

/**
 * @inheritDoc
 */
class ScalarDynamicTypeReader implements ReaderInterface
{
    /**
     * @inheritDoc
     */
    public function read($scope = null) : array
    {
        $config['CmsRichTextAST'] = [
            'name' => 'CmsRichTextAST',
            'type' => 'graphql_scalar',
            'description' => 'Slate-compatible RichText AST',
        ];

        return $config;
    }
}
