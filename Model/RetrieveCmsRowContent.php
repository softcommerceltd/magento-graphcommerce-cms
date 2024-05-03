<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * @inheritDoc
 */
class RetrieveCmsRowContent implements RetrieveCmsRowContentInterface, MetadataInterface
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
     * @param BlockInterface|PageInterface $subject
     * @return array
     */
    public function execute(BlockInterface|PageInterface $subject): array
    {
        $metadata = $subject->getData(MetadataInterface::GC_METADATA);

        if (is_array($metadata)) {
            return $metadata;
        }

        try {
            $metadata = $this->serializer->unserialize($metadata);
        } catch (\InvalidArgumentException) {
            return [];
        }

        $result = [];
        $i = 0;
        foreach ($metadata[self::ROW_CONTENT] ?? [] as $item) {
            $typeId = $item[self::CONTENT_TYPE_ID] ?? null;
            if (!$typeId || !$content = $item[self::CONTENT] ?? []) {
                continue;
            }

            if (!isset($content[self::IDENTIFIER])) {
                $content[self::IDENTIFIER] = $typeId . '_' . $i++;
            }
            $result[] = $content;
        }

        return $result;
    }
}
