<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\PageInterface;

/**
 * Interface GetProfileDataInterface used to
 * provide profile data in array format.
 */
interface RetrieveCmsRowContentInterface
{
    public const ROW_CONTENT = 'row_content';
    public const CONTENT_TYPE_ID = 'content_type_id';
    public const IDENTIFIER = 'id';

    /**
     * @param BlockInterface|PageInterface $subject
     * @return array
     */
    public function execute(BlockInterface|PageInterface $subject): array;
}
