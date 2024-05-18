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
class AssetBuilder extends AbstractBuilder implements ProcessorInterface, MetadataInterface
{
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

            $result[self::MIME_TYPE] = $assetData->getContentType();
            $result[self::SIZE] = $assetData->getSize();
            $result[self::URL] = $this->getMediaUrl($assetData->getPath(), 0);
        }

       $this->getDataStorage()->setData($result);
    }
}
