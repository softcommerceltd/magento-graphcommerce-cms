<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\UrlRewrites;

/**
 * Interface GetUrlByEntityIdInterface
 * used to retrieve URL by entity and ID.
 */
interface GetUrlByEntityIdInterface
{
    /**
     * @param string $entity
     * @param int $entityId
     * @param int $storeId
     * @param bool $useBaseUrl
     * @return string
     */
    public function execute(string $entity, int $entityId, int $storeId, bool $useBaseUrl = false): string;
}
