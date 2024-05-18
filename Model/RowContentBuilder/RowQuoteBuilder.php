<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\RowContentBuilder;

use SoftCommerce\Core\Framework\Processor\ProcessorInterface;
use SoftCommerce\GraphCommerceCms\Model\MetadataInterface;

/**
 * @inheritDoc
 */
class RowQuoteBuilder extends AbstractBuilder implements ProcessorInterface, MetadataInterface
{
    /**
     * @var string[]
     */
    private array $metaDataMapping = [
        self::GC_RICHTEXT => self::QUOTE
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
            $responseData = $response[self::CHILDREN] ?? null;

            if (!$typeId
                || !$responseData
                || !$metadata = $this->metaDataMapping[$typeId] ?? null
            ) {
                continue;
            }

            try {
                $data = $this->buildData($typeId, $responseData);
            } catch (\Exception) {
                $data = [];
            }

            if ($data) {
                $result[$metadata] = $data;
            }
        }

        if ($result) {
            $result[self::GQL_ID] = $this->getUniqueId(self::CMS_ROW_QUOTE);
            $result[self::TYPE_ID] = self::CMS_ROW_QUOTE;
            $context->getDataStorage()->addData($result);
        }
    }
}
