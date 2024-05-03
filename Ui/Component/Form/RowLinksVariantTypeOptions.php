<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Ui\Component\Form;

use Magento\Framework\Data\OptionSourceInterface;
use SoftCommerce\GraphCommerceCms\Model\MetadataInterface;

/**
 * @inheritDoc
 */
class RowLinksVariantTypeOptions implements OptionSourceInterface
{
    /**
     * @var array|null
     */
    private ?array $options = null;

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        if (null === $this->options) {
            $this->options = [];
            foreach ($this->getOptions() as $value => $label) {
                $this->options[] = [
                    'value' => $value,
                    'label' => $label
                ];
            }
        }

        return $this->options;
    }

    /**
     * @return array
     */
    private function getOptions(): array
    {
        return [
            MetadataInterface::ROW_LINKS_VARIANT_IMAGE_LABEL_SWIPER => __('Image Label Swiper'),
            MetadataInterface::ROW_LINKS_VARIANT_LOGO_SWIPER => __('Logo Swiper'),
            MetadataInterface::ROW_LINKS_VARIANT_INLINE => __('Inline'),
        ];
    }
}
