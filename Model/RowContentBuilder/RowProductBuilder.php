<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\RowContentBuilder;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogWidget\Model\Rule;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\Template\Tokenizer\Parameter;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\MediaContentApi\Api\ExtractAssetsFromContentInterface;
use Magento\PageBuilder\Model\Catalog\Sorting;
use Magento\Rule\Model\Condition\Combine;
use Magento\Rule\Model\Condition\Sql\Builder as SqlBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Helper\Conditions;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;
use SoftCommerce\Core\Framework\Processor\ProcessorInterface;
use SoftCommerce\GraphCommerceCms\Model\DomConverter\FromDomToArrayConverterInterface;
use SoftCommerce\GraphCommerceCms\Model\MetadataInterface;
use function preg_match;

/**
 * @inheritDoc
 */
class RowProductBuilder extends AbstractBuilder implements ProcessorInterface, MetadataInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    private CategoryRepositoryInterface $categoryRepository;

    /**
     * @var Conditions
     */
    private Conditions $conditionsHelper;

    private Parameter $parameterTokenizer;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $productCollectionFactory;

    /**
     * @var Rule
     */
    private Rule $widgetRule;

    /**
     * @var Sorting
     */
    private Sorting $catalogSorting;

    /**
     * @var SqlBuilder
     */
    private SqlBuilder $sqlBuilder;

    /**
     * @var Visibility
     */
    private Visibility $catalogProductVisibility;

    /**
     * @var string
     */
    protected string $typeId = self::CMS_ROW_PRODUCTS;

    /**
     * @var string[]
     */
    protected array $metaDataMapping = [
        self::VARIANT => self::VARIANT,
        self::GC_HEADING => self::TITLE,
        self::GC_ASSET => self::GQL_ASSET,
        self::GC_PAGE_LINKS => self::GQL_PAGE_LINKS,
        self::GC_RICHTEXT => self::GQL_PRODUCT_COPY,
        self::GC_PRODUCTS => self::GQL_ROW_PRODUCTS
    ];

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Conditions $conditionsHelper
     * @param CollectionFactory $productCollectionFactory
     * @param Parameter $parameterTokenizer
     * @param Rule $widgetRule
     * @param Sorting $catalogSorting
     * @param SqlBuilder $sqlBuilder
     * @param Visibility $catalogProductVisibility
     * @param FromDomToArrayConverterInterface $domConverter
     * @param ExtractAssetsFromContentInterface $extractAssetsFromContent
     * @param SerializerInterface $serializer
     * @param StoreManagerInterface $storeManager
     * @param DataStorageInterfaceFactory $dataStorageFactory
     * @param MessageStorageInterfaceFactory $messageStorageFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     * @param array $processors
     * @param array $metaDataMapping
     * @param string|null $typeId
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        Conditions $conditionsHelper,
        CollectionFactory $productCollectionFactory,
        Parameter $parameterTokenizer,
        Rule $widgetRule,
        Sorting $catalogSorting,
        SqlBuilder $sqlBuilder,
        Visibility $catalogProductVisibility,
        FromDomToArrayConverterInterface $domConverter,
        ExtractAssetsFromContentInterface $extractAssetsFromContent,
        SerializerInterface $serializer,
        StoreManagerInterface $storeManager,
        DataStorageInterfaceFactory $dataStorageFactory,
        MessageStorageInterfaceFactory $messageStorageFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = [],
        array $processors = [],
        array $metaDataMapping = [],
        ?string $typeId = null
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->conditionsHelper = $conditionsHelper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->parameterTokenizer = $parameterTokenizer;
        $this->widgetRule = $widgetRule;
        $this->catalogSorting = $catalogSorting;
        $this->sqlBuilder = $sqlBuilder;
        $this->catalogProductVisibility = $catalogProductVisibility;
        parent::__construct(
            $domConverter,
            $extractAssetsFromContent,
            $serializer,
            $storeManager,
            $dataStorageFactory,
            $messageStorageFactory,
            $searchCriteriaBuilder,
            $data,
            $processors,
            $metaDataMapping,
            $typeId
        );
    }

    /**
     * @inheritDoc
     */
    public function execute(): void
    {
        $context = $this->getContext();

        $result = [];
        foreach ($context->getDomElement()->childNodes as $childNode) {
            $response = $this->domConverter->execute($childNode);
            $typeId = $response[self::TYPE_ID] ?? null;

            if (!$typeId || !$metadata = $this->metaDataMapping[$typeId] ?? null) {
                continue;
            }

            if ($this->getProcessorInstance($typeId)) {
                try {
                    $data = $this->buildData($typeId, $response[self::CHILDREN] ?? [$response]);
                } catch (\Exception) {
                    $data = [];
                }

                if ($data) {
                    if ($typeId === self::GC_PAGE_LINKS) {
                        $result[$metadata][] = $data;
                    } else {
                        $result[$metadata] = $data;
                    }
                }
                continue;
            }

            if (!$content = $response[self::CONTENT] ?? []) {
                continue;
            }

            if ($typeId !== self::GC_PRODUCTS || !str_contains($content, '{{widget')) {
                $result[$metadata] = $content;
                continue;
            }

            if ($products = $this->buildProductData($content)) {
                $result[$metadata] = $products;
                $result[self::VARIANT] = isset($response[self::APPEARANCE])
                    ? ucfirst($response[self::APPEARANCE])
                    : self::ROW_PRODUCT_VARIANT_GRID;
                $result[self::GQL_IDENTITY] = 'home-favorites';
            }
        }

        if ($result) {
            $result[self::GQL_ID] = $this->getUniqueId($this->getTypeId());
            $result[self::TYPE_ID] = $this->getTypeId();

            // Page links - required field!
            if (!isset($result[self::GQL_PAGE_LINKS])) {
                $result[self::GQL_PAGE_LINKS] = [];
            }

            $context->getDataStorage()->addData($result);
        }
    }

    /**
     * @param string $content
     * @return array
     * @throws LocalizedException
     */
    private function buildProductData(string $content): array
    {
        preg_match(self::WIDGET_PATTERN, $content, $matches);

        if (!isset($matches[2])) {
            return [];
        }

        $this->parameterTokenizer->setString($matches[2]);
        $params = $this->parameterTokenizer->tokenize();

        if (!$conditions = $params['conditions_encoded'] ?? null) {
            return [];
        }

        $collection = $this->getProductCollection(
            $conditions,
            (int) ($params['products_count'] ?? 4)
        );

        if (!empty($params['sort_order'])) {
            $collection = $this->catalogSorting->applySorting($params['sort_order'], $collection);
        }

        return [
            'sku' => $collection->getColumnValues('sku'),
            'id' => $collection->getColumnValues('entity_id')
        ];
    }

    /**
     * @param string $conditions
     * @param int $pageSize
     * @return Collection
     * @throws LocalizedException
     */
    private function getProductCollection(string $conditions, int $pageSize): Collection
    {
        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId($this->getContext()->getStoreId());
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        $conditions = $this->getConditions($conditions);
        $this->sqlBuilder->attachConditionToCollection($collection, $conditions);

        $collection->distinct(true);
        $collection->setPageSize($pageSize);

        return $collection;
    }

    /**
     * @param string $conditions
     * @return Combine
     * @throws LocalizedException
     */
    private function getConditions(string $conditions): Combine
    {
        $conditions = $this->conditionsHelper->decode($conditions);

        foreach ($conditions as $index => $condition) {
            if (empty($condition['attribute'])) {
                continue;
            }

            if (in_array($condition['attribute'], ['special_from_date', 'special_to_date'])) {
                $conditions[$index]['value'] = date('Y-m-d H:i:s', strtotime($condition['value']));
            }

            if ($condition['attribute'] == 'category_ids') {
                $conditions[$index] = $this->updateAnchorCategoryConditions($condition);
            }
        }

        $this->widgetRule->loadPost(['conditions' => $conditions]);
        return $this->widgetRule->getConditions();
    }

    /**
     * @param array $condition
     * @return array
     * @throws LocalizedException
     */
    private function updateAnchorCategoryConditions(array $condition): array
    {
        if (array_key_exists('value', $condition)) {
            $categoryId = $condition['value'];

            try {
                $category = $this->categoryRepository->get($categoryId, $this->getContext()->getStoreId());
            } catch (NoSuchEntityException $e) {
                return $condition;
            }

            $children = $category->getIsAnchor() ? $category->getChildren(true) : [];
            if ($children) {
                $children = explode(',', $children);
                $condition['operator'] = "()";
                $condition['value'] = array_merge([$categoryId], $children);
            }
        }

        return $condition;
    }
}
