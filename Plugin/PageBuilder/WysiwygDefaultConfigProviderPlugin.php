<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Plugin\PageBuilder;

use Magento\Framework\DataObject;
use Magento\PageBuilder\Model\Wysiwyg\DefaultConfigProvider;

/**
 * @plugin WysiwygDefaultConfigProviderPlugin
 * Used to extend PageBuilder Wysiwyg configuration.
 */
class WysiwygDefaultConfigProviderPlugin
{
    /**
     * @param DefaultConfigProvider $subject
     * @param DataObject $result
     * @return DataObject
     */
    public function afterGetConfig(DefaultConfigProvider $subject, DataObject $result): DataObject
    {
        $data = $result->getData();
        $data['add_widgets'] = 0;
        $data['tinymce']['toolbar'] = 'undo redo | styleselect | bold italic underline'
            . ' | numlist bullist | link image table charmap';
        $data['settings']['formats'] = [
            'underline' => ['inline' => 'u', 'remove' => 'all'],
            'strikethrough' => ['inline' => 's', 'remove' => 'all']
        ];

        $result->setData($data);

        return $result;
    }
}
