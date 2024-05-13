<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\Resolver;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\Store;
use Magento\Widget\Model\Template\FilterEmulate;
use SoftCommerce\GraphCommerceCms\Model\MetadataInterface;
use SoftCommerce\GraphCommerceCms\Model\RowContentBuilderInterface;
use function is_numeric;

/**
 * CMS blocks field resolver, used for GraphQL request processing
 */
class CmsBlocks implements ResolverInterface
{
    /**
     * @var BlockRepositoryInterface
     */
    private BlockRepositoryInterface $blockRepository;

    /**
     * @var FilterEmulate
     */
    private FilterEmulate $widgetFilter;

    /**
     * @var RowContentBuilderInterface
     */
    private RowContentBuilderInterface $rowContentBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @param BlockRepositoryInterface $blockRepository
     * @param FilterEmulate $widgetFilter
     * @param RowContentBuilderInterface $rowContentBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        FilterEmulate $widgetFilter,
        RowContentBuilderInterface $rowContentBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->blockRepository = $blockRepository;
        $this->widgetFilter = $widgetFilter;
        $this->rowContentBuilder = $rowContentBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
        $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();
        $blockIdentifiers = $this->getBlockIdentifiers($args);
        $blocksData = $this->getBlocksData($blockIdentifiers, $storeId);

        return [
            'items' => $blocksData,
        ];
    }

    /**
     * @param array $args
     * @return array
     * @throws GraphQlInputException
     */
    private function getBlockIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of CMS blocks should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * @param array $blockIdentifiers
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     */
    private function getBlocksData(array $blockIdentifiers, int $storeId): array
    {
        $blocksData = [];
        foreach ($blockIdentifiers as $blockIdentifier) {
            try {
                if (!is_numeric($blockIdentifier)) {
                    $blocksData[$blockIdentifier] = $this->getBlockByIdentifier($blockIdentifier, $storeId);
                } else {
                    $blocksData[$blockIdentifier] = $this->getBlockById((int)$blockIdentifier, $storeId);
                }
            } catch (NoSuchEntityException $e) {
                $blocksData[$blockIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $blocksData;
    }

    /**
     * @param string $blockIdentifier
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getBlockByIdentifier(string $blockIdentifier, int $storeId): array
    {
        return $this->generateBlockData($blockIdentifier, BlockInterface::IDENTIFIER, $storeId);
    }

    /**
     * @param int $blockId
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getBlockById(int $blockId, int $storeId): array
    {
        return $this->generateBlockData($blockId, BlockInterface::BLOCK_ID, $storeId);
    }

    /**
     * @param $identifier
     * @param string $field
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function generateBlockData($identifier, string $field, int $storeId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter($field, $identifier)
            ->addFilter(Store::STORE_ID, [$storeId, Store::DEFAULT_STORE_ID], 'in')
            ->addFilter(BlockInterface::IS_ACTIVE, true)->create();

        $blockResults = $this->blockRepository->getList($searchCriteria)->getItems();

        if (empty($blockResults)) {
            throw new NoSuchEntityException(
                __('The CMS block with the "%1" ID doesn\'t exist.', $identifier)
            );
        }

        $block = current($blockResults);
        $renderedContent = $this->widgetFilter->filterDirective($block->getContent());
        return [
            BlockInterface::BLOCK_ID => $block->getId(),
            BlockInterface::IDENTIFIER => $block->getIdentifier(),
            BlockInterface::TITLE => $block->getTitle(),
            BlockInterface::CONTENT => $renderedContent,
            MetadataInterface::CMS_ROW_CONTENT => $this->getRowContentData($block)
        ];
    }

    /**
     * @param BlockInterface $block
     * @return array
     */
    private function getRowContentData(BlockInterface $block): array
    {
        if (!$gcMetadata = $block->getData(MetadataInterface::GC_METADATA)) {
            return [];
        }

        $this->rowContentBuilder->execute($gcMetadata, (int) $block->getStoreId());
        return $this->rowContentBuilder->getDataStorage()->getData();
    }
}
