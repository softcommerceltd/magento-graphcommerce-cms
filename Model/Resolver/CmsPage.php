<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\Resolver;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Widget\Model\Template\FilterEmulate;
use SoftCommerce\GraphCommerceCms\Model\MetadataInterface;
use SoftCommerce\GraphCommerceCms\Model\RowContentBuilderInterface;

/**
 * CMS page field resolver, used for GraphQL request processing
 */
class CmsPage implements ResolverInterface
{
    /**
     * @var FilterEmulate
     */
    private FilterEmulate $widgetFilter;

    /**
     * @var GetPageByIdentifierInterface
     */
    private GetPageByIdentifierInterface $pageByIdentifier;

    /**
     * @var RowContentBuilderInterface
     */
    private RowContentBuilderInterface $rowContentBuilder;

    /**
     * @var PageRepositoryInterface
     */
    private PageRepositoryInterface $pageRepository;

    /**
     * @param FilterEmulate $widgetFilter
     * @param GetPageByIdentifierInterface $getPageByIdentifier
     * @param RowContentBuilderInterface $rowContentBuilder
     * @param PageRepositoryInterface $pageRepository
     */
    public function __construct(
        FilterEmulate $widgetFilter,
        GetPageByIdentifierInterface $getPageByIdentifier,
        RowContentBuilderInterface $rowContentBuilder,
        PageRepositoryInterface $pageRepository
    ) {
        $this->widgetFilter = $widgetFilter;
        $this->pageByIdentifier = $getPageByIdentifier;
        $this->rowContentBuilder = $rowContentBuilder;
        $this->pageRepository = $pageRepository;
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
    ): array
    {
        if (!isset($args['id']) && !isset($args['identifier'])) {
            throw new GraphQlInputException(
                __('"Page id/identifier should be specified')
            );
        }

        $pageData = [];

        try {
            if (isset($args['id'])) {
                $pageData = $this->getDataByPageId((int)$args['id']);
            } elseif (isset($args['identifier'])) {
                $pageData = $this->getDataByPageIdentifier(
                    (string) $args['identifier'],
                    (int) $context->getExtensionAttributes()->getStore()->getId()
                );
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        return $pageData;
    }

    /**
     * @param int $pageId
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function getDataByPageId(int $pageId): array
    {
        return $this->generatePageData(
            $this->pageRepository->getById($pageId)
        );
    }

    /**
     * @param string $pageIdentifier
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    private function getDataByPageIdentifier(string $pageIdentifier, int $storeId): array
    {
        return $this->generatePageData(
            $this->pageByIdentifier->execute($pageIdentifier, $storeId)
        );
    }

    /**
     * @param PageInterface $page
     * @return array
     * @throws NoSuchEntityException
     */
    private function generatePageData(PageInterface $page): array
    {
        if (false === $page->isActive()) {
            throw new NoSuchEntityException();
        }

        $renderedContent = $this->widgetFilter->filter($page->getContent());

        return [
            'url_key' => $page->getIdentifier(),
            PageInterface::TITLE => $page->getTitle(),
            PageInterface::CONTENT => $renderedContent,
            PageInterface::CONTENT_HEADING => $page->getContentHeading(),
            PageInterface::PAGE_LAYOUT => $page->getPageLayout(),
            PageInterface::META_TITLE => $page->getMetaTitle(),
            PageInterface::META_DESCRIPTION => $page->getMetaDescription(),
            PageInterface::META_KEYWORDS => $page->getMetaKeywords(),
            PageInterface::PAGE_ID => $page->getId(),
            PageInterface::IDENTIFIER => $page->getIdentifier(),
            MetadataInterface::CMS_ROW_CONTENT => $this->getRowContentData($page)
        ];
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
