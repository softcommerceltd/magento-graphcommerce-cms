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
class RichTextBuilder extends AbstractBuilder implements ProcessorInterface, MetadataInterface
{
    /**
     * @var array|string[]
     */
    private array $nodeTypeMapping = [
        'h1' => 'heading-one',
        'h2' => 'heading-two',
        'h3' => 'heading-three',
        'h4' => 'heading-four',
        'h5' => 'heading-five',
        'h6' => 'heading-six',
        'p' => 'paragraph',
        'em' => 'italic',
        'u' => 'underline',
        'span' => 'span',
        'strong' => 'bold'
    ];

    /**
     * @param array $data
     * @inheritDoc
     */
    public function execute(array $data = []): void
    {
        $result = [];
        foreach ($data as $item) {
            if (!$nodeName = $item[self::NODE_NAME] ?? null) {
                continue;
            }

            $type = $this->nodeTypeMapping[$nodeName] ?? 'paragraph';

            if ($children = $this->buildTextData($item)) {
                $result[] = [
                    self::TYPE => $type,
                    self::CHILDREN => $children
                ];
            }
        }

        $this->getDataStorage()->setData(
            [self::CHILDREN => $result],
            self::GQL_RAW
        );
    }

    /**
     * @param array $data
     * @param string|null $nodeTypeName
     * @return array|array[]
     */
    private function buildTextData(array $data, ?string $nodeTypeName = null): array
    {
        $result = [];
        if (isset($data[self::CONTENT])
            && !is_array($data[self::CONTENT])
        ) {
            $nodeName = $data[self::NODE_NAME] ?? null;
            if (isset($this->nodeTypeMapping[$nodeName])) {
                $resultItem[$this->nodeTypeMapping[$nodeName]] = true;
            }

            if (null !== $nodeTypeName) {
                $resultItem[$nodeTypeName] = true;
            }

            $resultItem[self::TEXT] = $data[self::CONTENT];
            return [$resultItem];
        }

        foreach ($data[self::CHILDREN] ?? [] as $item) {
            $nodeName = $item[self::NODE_NAME] ?? null;
            $prevNodeTypeName = null;

            if (isset($this->nodeTypeMapping[$nodeName])) {
                $prevNodeTypeName = $nodeTypeName;
                $nodeTypeName = $this->nodeTypeMapping[$nodeName];
            } else {
                $nodeTypeName = null;
            }

            $itemData = $this->buildTextData($item, $nodeTypeName);
            if (!$itemData) {
                continue;
            }

            if ($prevNodeTypeName && $prevNodeTypeName !== $nodeTypeName) {
                foreach (array_keys($itemData) as $index) {
                    $itemData[$index][$prevNodeTypeName] = true;
                }
            }

            $result = array_merge($result, $itemData);
        }

        return $result;
    }
}
