# GraphCommerce headless CMS extension for Magento 2
The core purpose of this module is to provide integration of PageBuilder Content Management System for [GraphCommerce® headless theme](https://www.graphcommerce.org/), eventually replacing original HyGraph with its own CMS.
This module extends PageBuilder's functionality by introducing custom components that are used for building page content.

## Features
- Build rich page content by using Magento's native PageBuilder;
- Create media assets that support both images, SVG and video formats;
- Both CMS Pages and Blocks are supported;
- Add your own custom PageBuilder components;

## Supported PageBuilder Components
- Use `cmsRowHeroBanner` component to build `RowHeroBanner` content as seen in demo;
- Use `cmsRowLinks` component to build `RowLinks` content;
- Use `cmsRowProduct` component to build `RowProduct` catalog with ability to specify products by either category, SKU or custom rules;
- Use `cmsRowSpecialBanner` component to build `RowSpecialBanner` content;
- Use `cmsRowQuote` component to build `RowQuote` rich text content;

## Supported PageBuilder Elements
- `gc-asset` - used to provide images and video files;
- `gc-heading` - used to provide a heading for components that require a title.
- `gc-page-links` - used to provide links that support either a direct, product, category or CMS page URL;
- `gc-products` - used to provide product listing defined by either a category, SKUs or a custom rule;
- `gc-richtext` - used to provide content build with the help of wysiwyg;

## Compatibility
- Open Source >= 2.4.4

## Installation
Using composer

```
composer require softcommerce/module-graphcommerce-cms
```

## Post Installation

```sh
# Enable the module
bin/magento module:enable SoftCommerce_GraphCommerceCms
bin/magento setup:upgrade
```

In production mode:
```sh
# compile & generate static files
bin/magento deploy:mode:set production
```

In development mode:
```
bin/magento setup:di:compile
```

## Support
Soft Commerce Ltd <br />
support@softcommerce.io

## License
Each source file included in this package is licensed under OSL 3.0.

[Open Software License (OSL 3.0)](https://opensource.org/licenses/osl-3.0.php).
Please see `LICENSE.txt` for full details of the OSL 3.0 license.

## Thanks for dropping by

<p align="center">
    <a href="https://softcommerce.io" target="_blank">
        <img src="https://softcommerce.co.uk/pub/media/banner/logo.svg" width="200" alt="Soft Commerce Ltd" />
    </a>
    <br />
    <a href="https://softcommerce.io" target="_blank">https://softcommerce.io/</a>
</p>
