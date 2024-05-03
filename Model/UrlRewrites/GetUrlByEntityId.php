<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\UrlRewrites;

use Magento\Framework\UrlInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * @inheritDoc
 */
class GetUrlByEntityId implements GetUrlByEntityIdInterface
{
    /**
     * @var array
     */
    private array $dataInMemory = [];

    /**
     * @var UrlFinderInterface
     */
    private UrlFinderInterface $urlFinder;

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        UrlInterface $urlBuilder
    ) {
        $this->urlFinder = $urlFinder;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $entity, int $entityId, int $storeId, bool $useBaseUrl = false): string
    {
        if (!isset($this->dataInMemory[$entity][$entityId][$storeId])) {
            $rewrite = $this->urlFinder->findOneByData(
                [
                    UrlRewrite::ENTITY_ID => $entityId,
                    UrlRewrite::ENTITY_TYPE => $entity,
                    UrlRewrite::STORE_ID => $storeId,
                ]
            );

            if ($rewrite) {
                $this->dataInMemory[$entity][$entityId][$storeId] = false !== $useBaseUrl
                    ? $this->urlBuilder->getDirectUrl($rewrite->getRequestPath())
                    : $rewrite->getRequestPath();
            }
        }

        return $this->dataInMemory[$entity][$entityId][$storeId] ?? '';
    }
}
