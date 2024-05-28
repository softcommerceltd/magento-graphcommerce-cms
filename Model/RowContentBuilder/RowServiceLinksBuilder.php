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
class RowServiceLinksBuilder extends AbstractBuilder implements ProcessorInterface, MetadataInterface
{
    /**
     * @var string[]
     */
    protected array $metaDataMapping = [
        self::GC_PAGE_LINKS => self::GQL_PAGE_LINKS,
        self::GC_HEADING => self::GQL_ROW_SERVICE_TITLE,
        self::GC_RICHTEXT => self::GQL_ROW_SERVICE_COPY
    ];

    /**
     * @var string
     */
    protected string $typeId = self::CMS_ROW_SERVICE_LINKS;

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
                $data = $this->buildData($typeId, $response[self::CHILDREN] ?? []);
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
            $result[self::GQL_ID] = $this->getUniqueId($this->getTypeId());
            $result[self::TYPE_ID] = $this->getTypeId();
            $context->getDataStorage()->addData($result);
        }
    }
}
