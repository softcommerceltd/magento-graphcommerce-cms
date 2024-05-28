<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\RowContentBuilder;

use Magento\Catalog\Model\Category\FileInfo;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\MediaContentApi\Api\ExtractAssetsFromContentInterface;
use Magento\Store\Model\StoreManagerInterface;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;
use SoftCommerce\Core\Framework\Processor\ProcessorInterface;
use SoftCommerce\GraphCommerceCms\Model\DomConverter\FromDomToArrayConverterInterface;
use SoftCommerce\GraphCommerceCms\Model\MetadataInterface;

/**
 * @inheritDoc
 */
class AssetBuilder extends AbstractBuilder implements ProcessorInterface, MetadataInterface
{
    /**
     * @var FileInfo
     */
    private FileInfo $fileInfo;

    /**
     * @param FileInfo $fileInfo
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
        FileInfo $fileInfo,
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
        $this->fileInfo = $fileInfo;
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

            if (!$typeId || !$content = $item[self::CONTENT] ?? null) {
                continue;
            }

            if ($typeId !== self::ASSET) {
                $result[$typeId] = $content;
                continue;
            }

            $assetData = current($this->extractAssetsFromContent->execute($content));

            if (!$assetData) {
                continue;
            }

            if (!isset($result[self::WIDTH])) {
                $result[self::WIDTH] = $assetData->getWidth();
            }

            if (!isset($result[self::HEIGHT])) {
                $result[self::HEIGHT] = $assetData->getHeight();
            }

            if (!isset($result[self::ALT])) {
                $result[self::ALT] = $assetData->getTitle();
            }

            /* @todo fix mime type during upload and save phase */
            if ($this->fileInfo->isExist($this->getMediaPath($assetData->getPath()))) {
                $result[self::MIME_TYPE] = $this->fileInfo->getMimeType(
                    $this->getMediaPath($assetData->getPath())
                );
            } else {
                $result[self::MIME_TYPE] = $assetData->getContentType();
            }

            $result[self::SIZE] = $assetData->getSize();
            $result[self::URL] = $this->getMediaUrl($assetData->getPath(), 0);
        }

       $this->getDataStorage()->setData($result);
    }
}
