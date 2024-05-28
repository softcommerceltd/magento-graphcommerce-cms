<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\Resolver\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\ViewModel\Category\Image as CategoryImage;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;
use SoftCommerce\GraphCommerceCms\Model\MetadataInterface;
use SoftCommerce\GraphCommerceCms\Model\RowContentBuilderInterface;
use function getimagesize;

/**
 * Class GcAsset
 * used to resolved GC media asset.
 */
class GcAsset implements ResolverInterface
{
    /**
     * @var CategoryImage
     */
    private CategoryImage $categoryImage;

    /**
     * @var RowContentBuilderInterface
     */
    private RowContentBuilderInterface $rowContentBuilder;

    /**
     * @param CategoryImage $categoryImage
     * @param RowContentBuilderInterface $rowContentBuilder
     */
    public function __construct(
        CategoryImage $categoryImage,
        RowContentBuilderInterface $rowContentBuilder
    ) {
        $this->categoryImage = $categoryImage;
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

        if ($asset = $this->getRowContentData($category, (int) $store->getId())) {
            return $asset[0]['asset'] ?? [];
        }

        return $this->getCategoryImageAsset($category);
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

        return $response;
    }

    /**
     * @param Category $category
     * @return array
     */
    private function getCategoryImageAsset(Category $category): array
    {
        if (!$imageUrl = $this->categoryImage->getUrl($category)) {
            return [];
        }

        $imageData = getimagesize($imageUrl);

        return [
            'url' => $imageUrl,
            'width' => $imageData[0] ?? null,
            'height' => $imageData[1] ?? null,
            'mimeType' => $imageData['mime'] ?? '',
            'size' => null,
            'alt' => $category->getName()
        ];
    }
}
