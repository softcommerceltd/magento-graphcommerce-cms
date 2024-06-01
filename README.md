# GraphCommerce headless CMS extension for Magento 2
The core purpose of this module is to provide integration of PageBuilder Content Management System for [GraphCommerce® headless theme](https://www.graphcommerce.org/), eventually replacing original HyGraph with its own CMS.
This module extends PageBuilder's functionality by introducing custom components that are used for building page content.

## Features
- Build rich page content by using Magento's native PageBuilder;
- Create media asset elements that support both images, SVG and video formats;
- CMS Pages, Blocks, Widgets, Categories and Products are supported;
- Add your own custom PageBuilder components;

## Supported PageBuilder Components
- Use `cmsRowHeroBanner` component to build elements with banner elements;
- Use `CmsRowLinks` component to build link elements that support linking to categories, products and custom URL paths. 
- Use `CmsRowProduct` component to build catalog elements with ability to specify products by either category, SKU or custom rules;
- Use `CmsRowSpecialBanner` component to build banner content elements;
- Use `CmsRowQuote` component to build rich text quotes;
- Use `CmsRowText` component to build rich text content;
- Use `CmsRowServiceLinks` component to build service links with the support of MUI icons;

## Supported PageBuilder Elements
- `gc-asset` - used to serve images and video files;
- `gc-heading` - used to provide a heading for components that require titles.
- `gc-page-links` - used to provide links that support either a direct, product, category or CMS page URL;
- `gc-products` - used to provide product listing defined by either a category, SKUs or a custom rule;
- `gc-richtext` - used to provide content built with the wysiwyg as an AST format;

## Compatibility
- Open Source >= 2.4.4

## Demo
https://graphcommerce-cms.softcommerce.dev/

## Magento Extension Installation
Using composer

```sh
# GH source: https://github.com/softcommerceltd/magento-graphcommerce-cms
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

## GraphCommerce Node Package Installation
Using YARN [recommended]

```sh
# GH source: https://github.com/softcommerceltd/graphcommerce-magento-cms
yarn add @softcommerce/graphcommerce-magento-cms
```
Using NPM

```sh
# GH source: https://github.com/softcommerceltd/graphcommerce-magento-cms
npm install @softcommerce/graphcommerce-magento-cms
```

## Magento Demo [sample] Project Installation

Install latest magento instance

```sh
# Install Magento
# Source: https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/composer
composer create-project --repository-url=https://repo.magento.com/ magento/project-community-edition magento.graphcommerce
```

Create DB & USER (replace credentials & password as required)
```
CREATE DATABASE magento_graphcommerce;
CREATE USER 'magento_graphcommerce'@'localhost' IDENTIFIED BY 'magento.graphcommerce.pwd';
GRANT ALL PRIVILEGES ON magento_graphcommerce.* TO 'magento_graphcommerce'@'localhost';
GRANT SUPER ON *.* TO magento_graphcommerce@'localhost';
GRANT PROCESS ON *.* TO magento_graphcommerce@localhost;
FLUSH PRIVILEGES;
```

Install magento application

```sh
# Install Magento
# Replace opensearch with elasticsearch if the latter one installed.
# Replace user credentials if required.
# Source: https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/composer
bin/magento setup:install \
--base-url=https://magento.graphcommerce/ \
--db-host=localhost \
--db-name=magento_graphcommerce \
--db-user=magento_graphcommerce \
--db-password=magento.graphcommerce.pwd \
--admin-firstname=Magento \
--admin-lastname=GraphCommerce \
--admin-email=magento-gc@example.com \
--admin-user=magento.graphcommerce \
--admin-password=magento.graphcommerce.pwd \
--language=en_GB \
--currency=GBP \
--timezone=Europe/London \
--use-rewrites=1 \
--search-engine=opensearch \
--opensearch-host=localhost \
--opensearch-index-prefix=magento.graphcommerce \
--opensearch-port=9200
```

Install sample data

```sh
# Install Magento sample data
# Source https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/next-steps/sample-data/composer-packages
bin/magento deploy:mode:set developer
bin/magento sampledata:deploy
# Install GraphCommerce sample data
# GH Source:
# https://github.com/softcommerceltd/magento-graphcommerce-cms-sample-data
# https://github.com/softcommerceltd/magento-graphcommerce-cms-sample-data-media
composer require softcommerce/module-graphcommerce-cms-sample-data
# Update the application
bin/magento setup:upgrade
```

Set the application to production

```sh
# Compile the application code
# and generate static files for production environment
bin/magento deploy:mode:set production
# Run indexing
bin/magento indexer:reindex
```

*** Note: SSL certificate is required. Use free one from https://letsencrypt.org/

## GraphCommerce PWA Demo Project

Both Magento & GraphCommerce plugins require some modifications to GC React project pages.
Use the demo below as a starting point for your project. 

```sh
# Clone demo project
# GH Source: https://github.com/softcommerceltd/magento-graphcommerce-pwa
git clone -b main --single-branch git@github.com:softcommerceltd/magento-graphcommerce-pwa.git
```

Create DB & USER (replace credentials & password as required)
```
Configre graphcommerce.config.js to include magento canonical and endpoint URLs as required. 
Change images hostname to your prefered domain in next.config.js file.
```

## License

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
