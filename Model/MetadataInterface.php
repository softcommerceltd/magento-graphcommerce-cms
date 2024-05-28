<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model;

/**
 * Interface MetadataInterface used to
 * provide attribute field names.
 */
interface MetadataInterface
{
    /**
     * FIEND NAMEs used in UI Forms.
     */
    public const GC_METADATA = 'gc_metadata';
    public const CMS_ROW_CONTENT = 'cmsRowContent';
    public const CMS_ROW_HERO_BANNER = 'cmsRowHeroBanner';
    public const CMS_ROW_LINKS = 'cmsRowLinks';
    public const CMS_ROW_PRODUCTS = 'cmsRowProduct';
    public const CMS_ROW_SERVICE_LINKS = 'cmsRowServiceLinks';
    public const CMS_ROW_SPECIAL_BANNER = 'cmsRowSpecialBanner';
    public const CMS_ROW_QUOTE = 'cmsRowQuote';
    public const CMS_BANNER = 'cmsBanner';
    public const CMS_ROW_TEXT = 'cmsRowText';
    public const CONTENT = 'content';

    /**
     * TYPE IDs used in GraphQL queries.
     */
    public const TYPE_ID_CMS_ROW_CONTENT = 'CmsRowContent';
    public const TYPE_ID_CMS_ROW_HERO_BANNER = 'CmsRowHeroBanner';
    public const TYPE_ID_CMS_ROW_LINKS = 'CmsRowLinks';
    public const TYPE_ID_CMS_ROW_PRODUCTS = 'CmsRowProduct';
    public const TYPE_ID_CMS_ROW_SERVICE_LINKS = 'CmsRowServiceLinks';
    public const TYPE_ID_CMS_ROW_SPECIAL_BANNER = 'CmsRowSpecialBanner';
    public const TYPE_ID_CMS_ROW_QUOTE = 'CmsRowQuote';
    public const TYPE_ID_CMS_BANNER = 'CmsBanner';
    public const TYPE_ID_CMS_PAGE_LINK = 'CmsPageLink';
    public const TYPE_ID_CMS_ROW_CONTENT_INTERFACE = 'CmsRowContentInterface';
    public const TYPE_ID_CMS_ROW_TEXT = 'CmsRowText';

    /**
     * ROW LINKS VARIANT
     */
    public const ROW_LINKS_VARIANT_IMAGE_LABEL_SWIPER = 'ImageLabelSwiper';
    public const ROW_LINKS_VARIANT_LOGO_SWIPER = 'LogoSwiper';
    public const ROW_LINKS_VARIANT_INLINE = 'Inline';

    /**
     * ROW PRODUCT VARIANT
     */
    public const ROW_PRODUCT_VARIANT_GRID = 'Grid';
    public const ROW_PRODUCT_VARIANT_SWIPEABLE = 'Swipeable';
    public const ROW_PRODUCT_VARIANT_BACKSTORY = 'Backstory';

    /**
     * Global GraphQL attributes
     */
    public const GQL_ID = 'id';
    public const GQL_IDENTITY = 'identity';
    public const GQL_COPY = 'copy';
    public const GQL_RAW = 'raw';
    public const GQL_TEXT_BOLD = 'bold';
    public const GQL_TEXT_PARAGRAPH = 'paragraph';
    public const GQL_LINKS_VARIANT = 'linksVariant';
    public const GQL_ROW_LINKS_COPY = 'rowLinksCopy';
    public const GQL_PAGE_LINKS = 'pageLinks';
    public const GQL_ASSET = 'asset';
    public const GQL_ICON = 'icon';
    public const GQL_HERO_ASSET = 'heroAsset';
    public const GQL_PRODUCT_COPY = 'productCopy';
    public const GQL_TOPIC = 'topic';
    public const GQL_ROW_PRODUCTS = 'rowProduct';
    public const GQL_ROW_SERVICE_COPY = 'rowServiceCopy';
    public const GQL_ROW_SERVICE_TITLE = 'rowServiceTitle';

    /**
     * NodeElement attributes
     */
    public const DATA_APPEARANCE = 'data-appearance';
    public const DATA_CONTENT_TYPE = 'data-content-type';
    public const DATA_ELEMENT = 'data-element';
    public const NODE_NAME = 'node_name';

    /**
     * PageBuilder Components
     */
    public const GC_HERO_BANNER = 'gc-hero-banner';
    public const GC_ROW_LINKS = 'gc-row-links';
    public const GC_ROW_PRODUCT = 'gc-row-product';
    public const GC_ROW_QUOTE = 'gc-row-quote';
    public const GC_ROW_SPECIAL_BANNER = 'gc-row-special-banner';
    public const GC_ROW_SERVICE_LINKS = 'gc-row-service-links';
    public const GC_ROW_TEXT = 'gc-row-text';

    /**
     * PageBuilder Elements
     */
    public const GC_ASSET = 'gc-asset';
    public const GC_PAGE_LINKS = 'gc-page-links';
    public const GC_RICHTEXT = 'gc-richtext';
    public const GC_HEADING = 'gc-heading';
    public const GC_PRODUCTS = 'gc-products';
    public const GC_LINKS_VARIANT = 'links_variant';

    /**
     * Global Attribute
     */
    public const APPEARANCE = 'appearance';
    public const TYPE = 'type';
    public const TYPE_ID = 'typeId';
    public const CHILDREN = 'children';
    public const LINK = 'link';
    public const TEXT = 'text';
    public const TITLE = 'title';
    public const URL = 'url';
    public const WIDTH = 'width';
    public const HEIGHT = 'height';
    public const MIME_TYPE = 'mimeType';
    public const SIZE = 'size';
    public const ALT = 'alt';
    public const DESCRIPTION = 'description';
    public const QUOTE = 'quote';
    public const IMAGE = 'image';
    public const ASSET = 'asset';
    public const MUI_ICON = 'mui_icon';
    public const VARIANT = 'variant';
}
