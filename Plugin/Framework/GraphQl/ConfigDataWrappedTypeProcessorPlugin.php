<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Plugin\Framework\GraphQl;

use Magento\Framework\GraphQl\Config\Data\WrappedTypeProcessor;
use Magento\Framework\GraphQl\Config\Element\Argument;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Config\Element\FieldInterface;
use Magento\Framework\GraphQl\Schema\TypeFactory;
use Magento\Framework\GraphQl\Schema\TypeInterface;

/**
 * @plugin ConfigDataWrappedTypeProcessorPlugin
 * Used to allow non-nullable types within a list
 */
class ConfigDataWrappedTypeProcessorPlugin
{
    /**
     * @var TypeFactory
     */
    private TypeFactory $typeFactory;

    /**
     * @param TypeFactory $typeFactory
     */
    public function __construct(TypeFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }

    /**
     * @param WrappedTypeProcessor $subject
     * @param callable $proceed
     * @param FieldInterface $field
     * @param TypeInterface|null $object
     * @return TypeInterface
     */
    public function aroundProcessWrappedType(
        WrappedTypeProcessor $subject,
        callable $proceed,
        FieldInterface $field,
        TypeInterface $object = null
    ): TypeInterface
    {
        return $this->processIsNullable($field, $this->processIsList($field, $object));
    }

    /**
     * @param FieldInterface $field
     * @param TypeInterface|null $object
     * @return TypeInterface
     */
    private function processIsNullable(FieldInterface $field, TypeInterface $object = null) : TypeInterface
    {
        if ($field->isRequired()) {
            return $this->typeFactory->createNonNull($object);
        }
        return $object;
    }

    /**
     * @param FieldInterface $field
     * @param TypeInterface|null $object
     * @return TypeInterface
     */
    private function processIsList(FieldInterface $field, TypeInterface $object = null) : TypeInterface
    {
        if ($field->isList()) {
            if ($field instanceof Argument) {
                if ($field->areItemsRequired()) {
                    $object = $this->typeFactory->createNonNull($object);
                }
            } elseif ($field instanceof Field) {
                if ($field->isRequired()) {
                    $object = $this->typeFactory->createNonNull($object);
                }
            }

            return $this->typeFactory->createList($object);
        }

        return $object;
    }
}
