<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model;

use DOMDocument;
use DOMElement;
use SoftCommerce\Core\Framework\DataStorageInterface;
use SoftCommerce\Core\Framework\MessageStorageInterface;

/**
 * Interface RowContentBuilderInterface used to
 * build row content data.
 */
interface RowContentBuilderInterface
{
    /**
     * @param string $html
     * @param int $storeId
     * @return void
     */
    public function execute(string $html, int $storeId): void;

    /**
     * @return DataStorageInterface
     */
    public function getDataStorage(): DataStorageInterface;

    /**
     * @return MessageStorageInterface
     */
    public function getMessageStorage(): MessageStorageInterface;

    /**
     * @return DOMDocument
     */
    public function getDomDocument(): DOMDocument;

    /**
     * @return DOMElement
     */
    public function getDomElement(): DOMElement;

    /**
     * @param DOMElement $domElement
     * @return void
     */
    public function setDomElement(DOMElement $domElement): void;

    /**
     * @return int|null
     */
    public function getStoreId(): ?int;
}
