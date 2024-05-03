<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\RowContentBuilder;

use SoftCommerce\GraphCommerceCms\Model\MetadataInterface;
use SoftCommerce\Profile\Model\ServiceAbstract\ProcessorInterface;

/**
 * @inheritDoc
 */
class RowSpecialBannerBuilder extends AbstractBuilder implements ProcessorInterface, MetadataInterface
{
    /**
     * @var string[]
     */
    private array $metaDataMapping = [
        self::GC_ASSET => self::GQL_ASSET,
        self::GC_HEADING => self::GQL_TOPIC,
        self::GC_PAGE_LINKS => self::GQL_PAGE_LINKS,
        self::GC_RICHTEXT => self::GQL_COPY
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
                $result[$metadata] = $response[self::CONTENT];
                continue;
            }

            if (!$responseData = $response[self::CHILDREN] ?? []) {
                continue;
            }

            try {
                $data = $this->buildData($typeId, $responseData);
            } catch (\Exception) {
                continue;
            }

            if ($data) {
                if ($typeId === self::GC_PAGE_LINKS) {
                    $result[$metadata][] = $data;
                } else {
                    $result[$metadata] = $data;
                }
            }
        }

        if ($result) {
            $result[self::GQL_ID] = $this->getUniqueId(self::CMS_ROW_SPECIAL_BANNER);
            $result[self::TYPE_ID] = self::CMS_ROW_SPECIAL_BANNER;
            $context->getDataStorage()->addData($result);
        }
    }
}
