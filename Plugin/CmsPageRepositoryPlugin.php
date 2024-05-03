<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Plugin;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\Data\PageSearchResultsInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Serialize\SerializerInterface;
use SoftCommerce\GraphCommerceCms\Model\MetadataInterface;

/**
 * @inheritDoc
 */
class CmsPageRepositoryPlugin
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param PageRepositoryInterface $subject
     * @param PageInterface $page
     * @return PageInterface[]
     */
    public function beforeSave(PageRepositoryInterface $subject, PageInterface $page): array
    {
        $this->encodeGcMetadata($page);

        return [$page];
    }

    /**
     * @param PageRepositoryInterface $subject
     * @param PageInterface $result
     * @return PageInterface
     */
    public function afterGetById(PageRepositoryInterface $subject, PageInterface $result): PageInterface
    {
        // $this->decodeGcMetadata($result);

        return $result;
    }

    /**
     * @param PageRepositoryInterface $subject
     * @param PageSearchResultsInterface $searchResults
     * @return PageSearchResultsInterface
     */
    public function afterGetList(
        PageRepositoryInterface $subject,
        PageSearchResultsInterface $searchResults
    ): PageSearchResultsInterface
    {
        foreach ($searchResults->getItems() as $item) {
            // $this->decodeGcMetadata($item);
        }

        return $searchResults;
    }

    /**
     * @param PageInterface $page
     * @return void
     */
    private function encodeGcMetadata(PageInterface $page): void
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/cms-save-t.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->debug(print_r([
            '$page->getData' => $page->getData()
        ], true), []);

        return;

        $data = $page->getData(MetadataInterface::GC_METADATA);
        if (!$data || !is_array($data)) {
            return;
        }

        foreach ($data as $typeId => $items) {
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $index => $item) {
                if (!isset($item[MetadataInterface::CONTENT])
                    || is_array($item[MetadataInterface::CONTENT])
                ) {
                    continue;
                }

                try {
                    $content = $this->serializer->unserialize($item[MetadataInterface::CONTENT]);
                } catch (\InvalidArgumentException) {
                    continue;
                }

                $data[$typeId][$index][MetadataInterface::CONTENT] = $content;
            }
        }

        try {
            $metadata = $this->serializer->serialize($data);
        } catch (\InvalidArgumentException) {
            return;
        }

        $page->setData(MetadataInterface::GC_METADATA, $metadata);
    }

    /**
     * @param PageInterface $page
     * @return void
     */
    private function decodeGcMetadata(PageInterface $page): void
    {
        $data = $page->getData(MetadataInterface::GC_METADATA);
        if (!$data || is_array($data)) {
            return;
        }

        try {
            $metadata = $this->serializer->unserialize($data);
        } catch (\InvalidArgumentException) {
            return;
        }

        foreach ($metadata as $typeId => $items) {
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $index => $item) {
                if (!isset($item[MetadataInterface::CONTENT])
                    || !is_array($item[MetadataInterface::CONTENT])
                ) {
                    continue;
                }

                try {
                    $content = $this->serializer->serialize($item[MetadataInterface::CONTENT]);
                } catch (\InvalidArgumentException) {
                    continue;
                }

                $metadata[$typeId][$index][MetadataInterface::CONTENT] = $content;
            }
        }

        $page->setData(MetadataInterface::GC_METADATA, $metadata);
    }
}
