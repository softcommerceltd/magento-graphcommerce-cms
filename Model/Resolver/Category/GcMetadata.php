<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\Resolver\Category;

use Magento\Catalog\Model\Category;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;
use SoftCommerce\GraphCommerceCms\Model\MetadataInterface;
use SoftCommerce\GraphCommerceCms\Model\RowContentBuilderInterface;
use function array_unshift;

/**
 * Class GcMetadata
 * used to resolved GC row content data.
 */
class GcMetadata implements ResolverInterface
{
    /**
     * @var RowContentBuilderInterface
     */
    private RowContentBuilderInterface $rowContentBuilder;

    /**
     * @param RowContentBuilderInterface $rowContentBuilder
     */
    public function __construct(RowContentBuilderInterface $rowContentBuilder)
    {
        $this->rowContentBuilder = $rowContentBuilder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /* @var Category $category */
        $category = $value['model'];

        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        return $this->getRowContentData($category, (int) $store->getId());
    }

    /**
     * @param Category $category
     * @param int $storeId
     * @return array
     */
    private function getRowContentData(Category $category, int $storeId): array
    {
        if (!$gcMetadata = $category->getData(MetadataInterface::GC_METADATA)) {
            return [];
        }

        try {
            $this->rowContentBuilder->execute($gcMetadata, $storeId);
            $response = $this->rowContentBuilder->getDataStorage()->getData();
        } catch (\DOMException) {
            $response = [];
        }

        if ($category->getProductCount() > 0) {
            array_unshift(
                $response,
                [
                    'typeId' => MetadataInterface::CMS_ROW_PRODUCTS,
                    'id' => MetadataInterface::CMS_ROW_PRODUCTS . '_' . (count($response) + 1),
                    'variant' => MetadataInterface::ROW_PRODUCT_VARIANT_GRID,
                    'identity'=> "popular-in-{$category->getUrlKey()}",
                    'title' => "Popular in {$category->getName()}",
                    'asset' => null,
                    'pageLinks' => [],
                    'productCopy' => [
                        'raw' => []
                    ]
                ]
            );
        }

        return $response;
    }
}
