<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\RowContentBuilder;

use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\Core\Framework\Processor\ProcessorInterface;
use SoftCommerce\GraphCommerceCms\Model\MetadataInterface;

/**
 * @inheritDoc
 */
class RowLinksBuilder extends AbstractBuilder implements ProcessorInterface, MetadataInterface
{
    /**
     * @var string[]
     */
    private array $metaDataMapping = [
        self::GC_HEADING => self::TITLE,
        self::GC_LINKS_VARIANT => self::GQL_LINKS_VARIANT,
        self::GC_PAGE_LINKS => self::GQL_PAGE_LINKS,
        self::GC_RICHTEXT => self::GQL_ROW_LINKS_COPY
    ];

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

            if (isset($response[self::CONTENT])) {
                $result[$metadata] = $response[self::CONTENT] ?: null;
                continue;
            }

            if (empty($response[self::CHILDREN])) {
                continue;
            }

            try {
                $data = $this->buildData($typeId, $response[self::CHILDREN]);
            } catch (\Exception) {
                continue;
            }

            if ($data) {
                if (isset($data[self::GQL_RAW])) {
                    $result[$metadata] = $data;
                } else {
                    $result[$metadata][] = $data;
                }
            }
        }

        if ($result) {
            $result[self::GQL_ID] = $this->getUniqueId(self::CMS_ROW_LINKS);
            $result[self::TYPE_ID] = self::CMS_ROW_LINKS;
            $context->getDataStorage()->addData($result);
        }
    }

    /**
     * @param string $typeId
     * @param array $data
     * @return array
     * @throws LocalizedException
     */
    private function buildData2(string $typeId, array $data): array
    {
        if (empty($data[self::CHILDREN])
            || !$processor = $this->getProcessorInstance($typeId)
        ) {
            return [];
        }

        $processor->execute($data[self::CHILDREN]);
        return $processor->getDataStorage()->getData();
    }
}
