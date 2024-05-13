<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\Resolver;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Widget\Model\Template\FilterEmulate;
use SoftCommerce\GraphCommerceCms\Model\MetadataInterface;
use SoftCommerce\GraphCommerceCms\Model\RowContentBuilderInterface;

/**
 * @inheritDoc
 */
class CmsPageList implements ResolverInterface
{
    /**
     * @var FilterEmulate
     */
    private FilterEmulate $widgetFilter;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var RowContentBuilderInterface
     */
    private RowContentBuilderInterface $rowContentBuilder;

    /**
     * @param FilterEmulate $widgetFilter
     * @param CollectionFactory $collectionFactory
     * @param RowContentBuilderInterface $rowContentBuilder
     */
    public function __construct(
        FilterEmulate $widgetFilter,
        CollectionFactory $collectionFactory,
        RowContentBuilderInterface $rowContentBuilder
    ) {
        $this->widgetFilter = $widgetFilter;
        $this->collectionFactory = $collectionFactory;
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
        $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();
        $pageListData = $this->getPageListData($storeId, $args['identifiers'] ?? []);

        return [
            'items' => $pageListData,
        ];
    }

    /**
     * @param array $identifiers
     * @param int $storeId
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getPageListData(int $storeId, array $identifiers = []): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addStoreFilter($storeId);

        if ($identifiers) {
            $collection->addFieldToFilter(PageInterface::IDENTIFIER, ['in', $identifiers]);
        }

        try {
            $result = [];
            foreach($collection->getItems() as $page) {
                $result[$page->getId()] = [
                    PageInterface::TITLE => $page->getTitle(),
                    PageInterface::CONTENT => $this->widgetFilter->filter($page->getContent()),
                    PageInterface::CONTENT_HEADING => $page->getContentHeading(),
                    PageInterface::PAGE_LAYOUT => $page->getPageLayout(),
                    PageInterface::META_TITLE => $page->getMetaTitle(),
                    PageInterface::META_DESCRIPTION => $page->getMetaDescription(),
                    PageInterface::META_KEYWORDS => $page->getMetaKeywords(),
                    PageInterface::PAGE_ID => $page->getId(),
                    PageInterface::IDENTIFIER => $page->getIdentifier(),
                    MetadataInterface::CMS_ROW_CONTENT => $this->getRowContentData($page),
                ];
            }
        } catch (\Exception $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        return $result;
    }

    /**
     * @param PageInterface $page
     * @return array
     */
    private function getRowContentData(PageInterface $page): array
    {
        if (!$gcMetadata = $page->getData(MetadataInterface::GC_METADATA)) {
            return [];
        }

        $this->rowContentBuilder->execute($gcMetadata, (int) $page->getStoreId());
        return $this->rowContentBuilder->getDataStorage()->getData();
    }
}
