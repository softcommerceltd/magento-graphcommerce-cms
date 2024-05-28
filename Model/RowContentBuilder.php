<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model;

use DOMDocument;
use DOMElement;
use DOMException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;
use SoftCommerce\Core\Framework\Processor\ProcessorInterface;
use SoftCommerce\Core\Framework\Processor\Service;

/**
 * @inheritDoc
 */
class RowContentBuilder extends Service implements RowContentBuilderInterface, MetadataInterface
{
    /**
     * @var DOMDocument|null
     */
    private ?DOMDocument $domDocument = null;

    /**
     * @var DOMElement|null
     */
    private ?DOMElement $domElement = null;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var array
     */
    private array $processors;

    /**
     * @var int|null
     */
    private ?int $storeId = null;

    /**
     * @param LoggerInterface $logger
     * @param DataStorageInterfaceFactory $dataStorageFactory
     * @param MessageStorageInterfaceFactory $messageStorageFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     * @param array $processors
     */
    public function __construct(
        LoggerInterface $logger,
        DataStorageInterfaceFactory $dataStorageFactory,
        MessageStorageInterfaceFactory $messageStorageFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = [],
        array $processors = []
    ) {
        $this->logger = $logger;
        $this->processors = $this->initServices($processors);
        $this->initTypeInstances($this, $this->processors);
        parent::__construct($dataStorageFactory, $messageStorageFactory, $searchCriteriaBuilder, $data);
    }

    /**
     * @inheritDoc
     */
    public function execute(string $html, int $storeId): void
    {
        $this->initialize();

        $this->storeId = $storeId;
        $this->domDocument = $this->createDomDocument($html);

        if (!$element = $this->getDomDocument()->getElementsByTagName('body')->item(0)) {
            return;
        }

        foreach ($element->childNodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            try {
                $this->processBuild($node);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getDomDocument(): DOMDocument
    {
        return $this->domDocument;
    }

    /**
     * @inheritDoc
     */
    public function getDomElement(): DOMElement
    {
        return $this->domElement;
    }

    /**
     * @inheritDoc
     */
    public function setDomElement(DOMElement $domElement): void
    {
        $this->domElement = $domElement;
    }

    /**
     * @inheritDoc
     */
    public function getStoreId(): ?int
    {
        return $this->storeId;
    }

    /**
     * @param DOMElement $node
     * @return void
     * @throws LocalizedException
     */
    private function processBuild(DOMElement $node): void
    {
        $this->domElement = $node;

        if (!$attributes = $this->getDomElement()->attributes) {
            return;
        }

        $typeId = null;
        foreach ($attributes as $attribute) {
            if ($attribute->name === self::DATA_CONTENT_TYPE) {
                $typeId = $attribute->value;
            }
        }

        if (!$typeId
            || !$processor = $this->getProcessorInstance($typeId)
        ) {
            return;
        }

        $processor->execute();
    }

    /**
     * @param string $typeId
     * @return ProcessorInterface|null
     */
    private function getProcessorInstance(string $typeId): ?ProcessorInterface
    {
        return $this->processors[$typeId] ?? null;
    }

    /**
     * @param string $html
     * @return DOMDocument
     * @throws DOMException
     */
    private function createDomDocument(string $html): DOMDocument
    {
        $domDocument = new DOMDocument('1.0', 'UTF-8');
        set_error_handler(
            function ($errorNumber, $errorString) {
                throw new DOMException($errorString, $errorNumber);
            }
        );

        $string = mb_encode_numericentity(
            $html,
            [0x80, 0x10FFFF, 0, 0x1FFFFF],
            'UTF-8'
        );

        try {
            libxml_use_internal_errors(true);
            $domDocument->loadHTML(
                "<body>$string</body>",
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );
            libxml_clear_errors();
        } catch (\Exception $e) {
            restore_error_handler();
        }

        restore_error_handler();

        return $domDocument;
    }
}
