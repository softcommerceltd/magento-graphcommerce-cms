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
class RowHeroBanner extends AbstractBuilder implements ProcessorInterface, MetadataInterface
{
    /**
     * @var string[]
     */
    protected array $metaDataMapping = [
        self::GC_ASSET => self::GQL_HERO_ASSET,
        self::GC_PAGE_LINKS => self::GQL_PAGE_LINKS,
        self::GC_RICHTEXT => self::GQL_COPY
    ];

    /**
     * @var string
     */
    protected string $typeId = self::CMS_ROW_HERO_BANNER;

    /**
     * @inheritDoc
     */
    public function execute(): void
    {
        $context = $this->getContext();

        $result = [];
        foreach ($context->getDomElement()->childNodes as $childNode) {
            $response = $this->domConverter->execute($childNode);
            $responseData = $response[self::CHILDREN] ?? [];
            $typeId = $response[self::TYPE_ID] ?? null;

            if (!$typeId
                || !$responseData
                || !$metadata = $this->metaDataMapping[$typeId] ?? null
            ) {
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
            $result[self::GQL_ID] = $this->getUniqueId($this->getTypeId());
            $result[self::TYPE_ID] = $this->getTypeId();
            $context->getDataStorage()->addData($result);
        }
    }
}
