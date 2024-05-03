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
use Magento\Framework\UrlInterface;
use Magento\MediaContentApi\Api\ExtractAssetsFromContentInterface;
use Magento\Store\Model\StoreManagerInterface;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;
use SoftCommerce\GraphCommerceCms\Model\DomConverter\FromDomToArrayConverterInterface;
use SoftCommerce\GraphCommerceCms\Model\RowContentBuilderInterface;
use SoftCommerce\Profile\Model\ServiceAbstract\ProcessorInterface;
use SoftCommerce\Profile\Model\ServiceAbstract\Service;
use function uniqid;

/**
 * @inheritDoc
 */
abstract class AbstractBuilder extends Service
{
    protected const WIDGET_PATTERN = '/(.*?){{widget(.*?)}}/si';

    /**
     * @var ProcessorInterface[]
     */
    protected array $builders = [];

    /**
     * @var RowContentBuilderInterface|null
     */
    protected $context;

    /**
     * @var FromDomToArrayConverterInterface
     */
    protected FromDomToArrayConverterInterface $domConverter;

    /**
     * @var ExtractAssetsFromContentInterface
     */
    protected ExtractAssetsFromContentInterface $extractAssetsFromContent;

    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @param FromDomToArrayConverterInterface $domConverter
     * @param ExtractAssetsFromContentInterface $extractAssetsFromContent
     * @param SerializerInterface $serializer
     * @param StoreManagerInterface $storeManager
     * @param DataStorageInterfaceFactory $dataStorageFactory
     * @param MessageStorageInterfaceFactory $messageStorageFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     * @param array $builders
     */
    public function __construct(
        FromDomToArrayConverterInterface $domConverter,
        ExtractAssetsFromContentInterface $extractAssetsFromContent,
        SerializerInterface $serializer,
        StoreManagerInterface $storeManager,
        DataStorageInterfaceFactory $dataStorageFactory,
        MessageStorageInterfaceFactory $messageStorageFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = [],
        array $builders = []
    ) {
        $this->domConverter = $domConverter;
        $this->extractAssetsFromContent = $extractAssetsFromContent;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
        $this->builders = $this->initServices($builders, true);
        parent::__construct($dataStorageFactory, $messageStorageFactory, $searchCriteriaBuilder, $data);
    }

    /**
     * @return RowContentBuilderInterface
     * @throws LocalizedException
     */
    public function getContext(): RowContentBuilderInterface
    {
        if (null === $this->context) {
            throw new LocalizedException(__('Context object is not set.'));
        }

        return $this->context;
    }

    /**
     * @param string $typeId
     * @return ProcessorInterface|null
     */
    protected function getProcessorInstance(string $typeId): ?ProcessorInterface
    {
        return $this->builders[$typeId] ?? null;
    }

    /**
     * @param string $path
     * @param int|null $storeId
     * @return string|null
     */
    protected function getMediaUrl(string $path, ?int $storeId = null): ?string
    {
        try {
            $store = $this->storeManager->getStore($storeId);
        } catch (\Exception) {
            return null;
        }

        $path = trim($path, '/');
        return $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $path;
    }

    /**
     * @param string|null $prefix
     * @return string
     */
    protected function getUniqueId(?string $prefix = null): string
    {
        return uniqid($prefix ? $prefix . '_' : '');
    }

    /**
     * @inheritDoc
     */
    public function init($context): static
    {
        foreach ($this->builders as $builder) {
            $builder->init($context);
        }

        return parent::init($context);
    }

    /**
     * @param string $valueData
     * @return array
     */
    protected function parseJsonValueData(string $valueData): array
    {
        try {
            $valueData = $this->serializer->unserialize($valueData);
        } catch (\InvalidArgumentException $e) {
            $valueData = [];
        }

        return $valueData;
    }

    /**
     * @param string $typeId
     * @param array $data
     * @return array
     * @throws LocalizedException
     */
    protected function buildData(string $typeId, array $data): array
    {
        if (!$processor = $this->getProcessorInstance($typeId)) {
            return [];
        }

        $processor->execute($data);
        return $processor->getDataStorage()->getData();
    }
}
