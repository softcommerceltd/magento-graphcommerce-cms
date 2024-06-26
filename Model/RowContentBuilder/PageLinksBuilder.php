<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\RowContentBuilder;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\MediaContentApi\Api\ExtractAssetsFromContentInterface;
use Magento\Store\Model\StoreManagerInterface;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;
use SoftCommerce\Core\Framework\Processor\ProcessorInterface;
use SoftCommerce\GraphCommerceCms\Model\Config\SystemConfigInterface;
use SoftCommerce\GraphCommerceCms\Model\DomConverter\FromDomToArrayConverterInterface;
use SoftCommerce\GraphCommerceCms\Model\MetadataInterface;
use SoftCommerce\GraphCommerceCms\Model\UrlRewrites\GetUrlByEntityIdInterface;
use function pathinfo;
use function preg_match;
use function str_contains;
use function str_replace;

/**
 * @inheritDoc
 */
class PageLinksBuilder extends AbstractBuilder implements ProcessorInterface, MetadataInterface
{
    private const SEARCH_PATTERN = "/{{widget (id_path|page_id)=['\"](category|product|)\/?(\d+)['\"]}}/";

    /**
     * @var GetUrlByEntityIdInterface
     */
    private GetUrlByEntityIdInterface $getUrlByEntityId;

    /**
     * @var SystemConfigInterface
     */
    private SystemConfigInterface $systemConfig;

    /**
     * @var string[]
     */
    protected array $metadataMapping = [
        self::TITLE => self::TITLE,
        self::LINK => self::URL,
        self::CONTENT => self::DESCRIPTION,
        self::ASSET => self::GQL_ASSET,
        self::MUI_ICON => self::GQL_ICON
    ];

    /**
     * @var string
     */
    protected string $typeId = self::GQL_PAGE_LINKS;

    /**
     * @param GetUrlByEntityIdInterface $getUrlByEntityId
     * @param SystemConfigInterface $systemConfig
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
        GetUrlByEntityIdInterface $getUrlByEntityId,
        SystemConfigInterface $systemConfig,
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
        $this->getUrlByEntityId = $getUrlByEntityId;
        $this->systemConfig = $systemConfig;
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
     * @param array $data
     * @inheritDoc
     */
    public function execute(array $data = []): void
    {
        $this->initialize();

        $result = [];
        foreach ($data as $item) {
            $typeId = $item[self::TYPE_ID] ?? null;

            if (!$typeId || !$metadata = $this->metadataMapping[$typeId] ?? null) {
                continue;
            }

            if ($this->getProcessorInstance($typeId)) {
                try {
                    $data = $this->buildData($typeId, $item[self::CHILDREN] ?? [$item]);
                } catch (\Exception) {
                    $data = [];
                }

                if ($data) {
                    $result[$metadata] = $data;
                }
                continue;
            }

            if (!$content = $item[self::CONTENT] ?? []) {
                continue;
            }

            if ($typeId === self::MUI_ICON) {
                $content = str_replace(
                    ' ',
                    '',
                    ucwords(
                        str_replace(
                            [' ', '_'],
                            ' ',
                            ucwords($content)
                        )
                    )
                );
            }

            if ($typeId !== self::LINK || !str_contains($content, '{{widget')) {
                $result[$metadata] = $content;
                continue;
            }

            if ($url = $this->getEntityUrl($content)) {
                $result[$metadata] = $url;
            }
        }

        if ($result) {
            if (!isset($result[self::TITLE])) {
                $result[self::TITLE] = '';
            }

            $result[self::GQL_ID] = $this->getUniqueId($this->getTypeId());
            $result[self::TYPE_ID] = $this->getTypeId();
        }

        $this->getDataStorage()->setData($result);
    }

    /**
     * @param string $content
     * @return string|null
     * @throws LocalizedException
     */
    private function getEntityUrl(string $content): ?string
    {
        preg_match(self::SEARCH_PATTERN, $content, $matches);

        if (!isset($matches[2]) || !$entityId = $matches[3] ?? null) {
            return null;
        }

        $entityType = empty($matches[2]) && ($matches[1] ?? '') === 'page_id'
            ? 'cms-page'
            : $matches[2];

        $resultUrl = $this->getUrlByEntityId->execute(
            $entityType,
            (int) $entityId,
            $this->getContext()->getStoreId()
        );

        $urlSuffix = match ($entityType) {
            'category' => $this->systemConfig->getCategoryUrlSuffix($this->getContext()->getStoreId()),
            'product' => $this->systemConfig->getProductUrlSuffix($this->getContext()->getStoreId()),
            'cms-page' => $this->getCmsPageUrlSuffix($resultUrl),
        };

        if ($urlSuffix) {
            $resultUrl = str_replace(".$urlSuffix", '', $resultUrl);
        }

        if ($entityType === 'product') {
            $resultUrl = $this->systemConfig->getProductRoute($this->getContext()->getStoreId()) . $resultUrl;
        }

        return '/' . ltrim($resultUrl, '/');
    }

    /**
     * @param string $url
     * @return string|null
     */
    private function getCmsPageUrlSuffix(string $url): ?string
    {
        return pathinfo($url)['extension'] ?? null;
    }
}
