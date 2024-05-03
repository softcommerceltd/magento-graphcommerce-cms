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
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;
use SoftCommerce\Profile\Model\ServiceAbstract\ProcessorInterface;
use SoftCommerce\Profile\Model\ServiceAbstract\Service;

/**
 * @inheritDoc
 */
class RowContentBuilder extends Service implements RowContentBuilderInterface, MetadataInterface
{
    /**
     * @var array
     */
    private array $builders;

    private ?DOMDocument $domDocument = null;

    private ?DOMElement $domElement = null;

    /**
     * @var int|null
     */
    private ?int $storeId = null;

    /**
     * @param DataStorageInterfaceFactory $dataStorageFactory
     * @param MessageStorageInterfaceFactory $messageStorageFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     * @param ProcessorInterface[] $builders
     */
    public function __construct(
        DataStorageInterfaceFactory $dataStorageFactory,
        MessageStorageInterfaceFactory $messageStorageFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = [],
        array $builders = []
    ) {
        $this->builders = $this->initServices($builders, true);
        $this->initTypeInstances($this, $this->builders);
        parent::__construct($dataStorageFactory, $messageStorageFactory, $searchCriteriaBuilder, $data);
    }

    public function execute(string $html, int $storeId): void
    {
        $this->initialize();
        // var_dump('$html', $html);

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
                // var_dump('error :::::: ' . $e->getMessage());
            }
        }

        // var_dump('================ $result', $this->getDataStorage()->getData());

        $this->finalize();
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

        $dataTypeId = null;
        foreach ($attributes as $attribute) {
            if ($attribute->name === self::DATA_CONTENT_TYPE) {
                $dataTypeId = $attribute->value;
            }
        }

        if (!$dataTypeId
            || !$processor = $this->getProcessorInstance($dataTypeId)
        ) {
            return;
        }

        // var_dump('++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++');
        // var_dump('----- $dataTypeId ::::: ' . $dataTypeId . ' -------------');
        // var_dump('----- $processor ::::: ' . get_class($processor));
        // var_dump('++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++');

        $processor->execute();
    }

    /**
     * @param string $typeId
     * @return ProcessorInterface|null
     */
    private function getProcessorInstance(string $typeId): ?ProcessorInterface
    {
        return $this->builders[$typeId] ?? null;
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
