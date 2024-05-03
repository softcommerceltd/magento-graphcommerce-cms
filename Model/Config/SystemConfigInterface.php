<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\Config;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface SystemConfigInterface
 * used to provide configuration for GraphCommerce CMS.
 */
interface SystemConfigInterface
{
    /**#@+
     * Config Paths
     */
    public const XML_PATH_PRODUCT_ROUTE = 'gc_cms/general/product_route';
    public const XML_PATH_CATEGORY_URL_SUFFIX = 'catalog/seo/category_url_suffix';
    public const XML_PATH_PRODUCT_URL_SUFFIX = 'catalog/seo/product_url_suffix';
    /**#@-*/

    /**
     * @param int|string|null $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductRoute(int|string|null $storeId = null): string;

    /**
     * @param int|string|null $storeId
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getCategoryUrlSuffix(int|string|null $storeId = null): ?string;

    /**
     * @param int|string|null $storeId
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getProductUrlSuffix(int|string|null $storeId = null): ?string;
}
