<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Model\Resolver;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Widget\Model\Template\FilterEmulate;
use SoftCommerce\GraphCommerceCms\Model\DomConverterInterface;
use SoftCommerce\GraphCommerceCms\Model\RetrieveCmsRowContentInterface;
use SoftCommerce\GraphCommerceCms\Model\RowContentBuilderInterface;

/**
 * CMS page field resolver, used for GraphQL request processing
 */
class CmsPage implements ResolverInterface
{
    /**
     * @var DomConverterInterface
     */
    private DomConverterInterface $domConverter;

    /**
     * @var FilterEmulate
     */
    private FilterEmulate $widgetFilter;

    /**
     * @var GetPageByIdentifierInterface
     */
    private GetPageByIdentifierInterface $pageByIdentifier;

    /**
     * @var RetrieveCmsRowContentInterface
     */
    private RetrieveCmsRowContentInterface $retrieveCmsRowContent;

    /**
     * @var RowContentBuilderInterface
     */
    private RowContentBuilderInterface $rowContentBuilder;

    /**
     * @var PageRepositoryInterface
     */
    private PageRepositoryInterface $pageRepository;

    /**
     * @param DomConverterInterface $domConverter
     * @param FilterEmulate $widgetFilter
     * @param GetPageByIdentifierInterface $getPageByIdentifier
     * @param RetrieveCmsRowContentInterface $retrieveCmsRowContent
     * @param PageRepositoryInterface $pageRepository
     */
    public function __construct(
        DomConverterInterface $domConverter,
        FilterEmulate $widgetFilter,
        GetPageByIdentifierInterface $getPageByIdentifier,
        RetrieveCmsRowContentInterface $retrieveCmsRowContent,
        RowContentBuilderInterface $rowContentBuilder,
        PageRepositoryInterface $pageRepository
    ) {
        $this->domConverter = $domConverter;
        $this->widgetFilter = $widgetFilter;
        $this->pageByIdentifier = $getPageByIdentifier;
        $this->retrieveCmsRowContent = $retrieveCmsRowContent;
        $this->rowContentBuilder = $rowContentBuilder;
        $this->pageRepository = $pageRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array
    {
        if (!isset($args['id']) && !isset($args['identifier'])) {
            throw new GraphQlInputException(
                __('"Page id/identifier should be specified')
            );
        }

        $pageData = [];

        try {
            if (isset($args['id'])) {
                $pageData = $this->getDataByPageId((int)$args['id']);
            } elseif (isset($args['identifier'])) {
                $pageData = $this->getDataByPageIdentifier(
                    (string) $args['identifier'],
                    (int) $context->getExtensionAttributes()->getStore()->getId()
                );
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/graph-ql.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->debug(print_r([
            'store ID' => $context->getExtensionAttributes()->getStore()->getId(),
            '$pageData' => $pageData
        ], true), []);

        return $pageData;
    }

    /**
     * @param int $pageId
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function getDataByPageId(int $pageId): array
    {
        return $this->generatePageData(
            $this->pageRepository->getById($pageId)
        );
    }

    /**
     * @param string $pageIdentifier
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    private function getDataByPageIdentifier(string $pageIdentifier, int $storeId): array
    {
        return $this->generatePageData(
            $this->pageByIdentifier->execute($pageIdentifier, $storeId)
        );
    }

    /**
     * @param PageInterface $page
     * @return array
     * @throws NoSuchEntityException
     */
    private function generatePageData(PageInterface $page): array
    {
        if (false === $page->isActive()) {
            throw new NoSuchEntityException();
        }

        $renderedContent = $this->widgetFilter->filter($page->getContent());

        return [
            'url_key' => $page->getIdentifier(),
            PageInterface::TITLE => $page->getTitle(),
            PageInterface::CONTENT => $renderedContent,
            PageInterface::CONTENT_HEADING => $page->getContentHeading(),
            PageInterface::PAGE_LAYOUT => $page->getPageLayout(),
            PageInterface::META_TITLE => $page->getMetaTitle(),
            PageInterface::META_DESCRIPTION => $page->getMetaDescription(),
            PageInterface::META_KEYWORDS => $page->getMetaKeywords(),
            PageInterface::PAGE_ID => $page->getId(),
            PageInterface::IDENTIFIER => $page->getIdentifier(),
            // MetadataInterface::CMS_ROW_CONTENT => $this->retrieveCmsRowContent->execute($page),
            'cmsRowContent' => $this->getRowContentData($page),
            'cmsRowContent2' => [
                [
                    'typeId' => 'cmsRowHeroBanner',
                    'id' => 'cmsRowHeroBanner_0',
                    'heroAsset' => [
                        'url' => 'https://gc-backend.softcommerce.dev/media/assets/AdobeStock_257697493_Video_4K_Preview.mov',
                        'width' => null,
                        'height' => null,
                        'mimeType' => 'video/mov',
                        'size' => 2839817,
                        'alt' => null
                    ],
                    'pageLinks' => [
                        [
                            'title' => 'Shop Art',
                            'url' => '/men/art',
                            'description' => null,
                            'asset' => null
                        ]
                    ],
                    'copy' => [
                        'raw' => [
                            'children' => [
                                [
                                    'type' => 'paragraph',
                                    'children' => [
                                        [
                                            'text' => 'Enhanced shopping experience'
                                        ]
                                    ]
                                ],
                                [
                                    'type' => 'heading-one',
                                    'children' => [
                                        [
                                            'bold' => true,
                                            'text' => 'through'
                                        ],
                                        [
                                            'text' => ' digital innovation.'
                                        ]
                                    ]
                                ],
                                [
                                    'type' => 'paragraph',
                                    'children' => [
                                        [
                                            'text' => ''
                                        ]
                                    ]
                                ],
                            ]
                        ]
                    ]
                ],
                [
                    'typeId' => 'cmsRowLinks',
                    'id' => 'RowLinks_1',
                    'title' => 'Hot & New',
                    'linksVariant' => 'ImageLabelSwiper',
                    'rowLinksCopy' => [
                        'raw' => [
                            'children' => [
                                [
                                    'type' => 'paragraph',
                                    'children' => [
                                        [
                                            'text' => 'test raw links 1'
                                        ]
                                    ]
                                ],
                                [
                                    'type' => 'heading-two',
                                    'children' => [
                                        [
                                            'bold' => true,
                                            'text' => 'test bold text'
                                        ],
                                        [
                                            'text' => 'test bold text line 2.'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'pageLinks' => [
                        [
                            'id' => 'page_links_id_1',
                            'title' => 'Bedazzle',
                            'url' => '/p/bedazzled-gc-112-sock',
                            'asset' => [
                                'url' => 'https://media.graphassets.com/7UopxWsjQ2q4WQDBbwKw',
                                'width' => 800,
                                'height' => 1200,
                                'mimeType' => 'image/jpeg',
                                'size' => 538666,
                                'alt' => 'Pop'
                            ],
                            'description' => [
                                'raw' => [
                                    'children' => [
                                        [
                                            'type' => 'paragraph',
                                            'children' => [
                                                [
                                                    'text' => 'Nothing says boho-chic like a flowy swirl'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'id' => 'page_links_id_2',
                            'title' => 'Art',
                            'url' => '/men/art',
                            'asset' => [
                                'url' => 'https://media.graphassets.com/9NH4cB2FQIPyIiIxm8Xg',
                                'width' => 800,
                                'height' => 1200,
                                'mimeType' => 'image/jpeg',
                                'size' => 876600,
                                'alt' => 'Art'
                            ],
                            'description' => [
                                'raw' => [
                                    'children' => [
                                        [
                                            'type' => 'paragraph',
                                            'children' => [
                                                [
                                                    'text' => 'The ultimate expressions of individuality'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'id' => 'page_links_id_3',
                            'title' => 'Seamless service',
                            'url' => '/service',
                            'asset' => [
                                'url' => 'https://media.graphassets.com/icVbl5yjTSyKreFul9wl',
                                'width' => null,
                                'height' => null,
                                'mimeType' => 'video/mp4',
                                'size' => 348411,
                                'alt' => 'Service'
                            ],
                            'description' => [
                                'raw' => [
                                    'children' => [
                                        [
                                            'type' => 'paragraph',
                                            'children' => [
                                                [
                                                    'text' => 'Prepare to get your socks knocked off'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'id' => 'page_links_id_4',
                            'title' => 'Black & White',
                            'url' => '/p/super-nummy-gc-74-sock',
                            'asset' => [
                                'url' => 'https://media.graphassets.com/M3vZsRtiQje9FAQvQBtY',
                                'width' => 800,
                                'height' => 1200,
                                'mimeType' => 'image/jpeg',
                                'size' => 291628,
                                'alt' => 'Chromo'
                            ],
                            'description' => [
                                'raw' => [
                                    'children' => [
                                        [
                                            'type' => 'paragraph',
                                            'children' => [
                                                [
                                                    'text' => 'Celebrate the timeless beauty of monochrome fashion'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'id' => 'page_links_id_5',
                            'title' => '70\'s',
                            'url' => '/men/70s',
                            'asset' => [
                                'url' => 'https://media.graphassets.com/ccRsXf0qRNK2Fu7Wqe29',
                                'width' => 800,
                                'height' => 1200,
                                'mimeType' => 'image/jpeg',
                                'size' => 729068,
                                'alt' => '70s'
                            ],
                            'description' => [
                                'raw' => [
                                    'children' => [
                                        [
                                            'type' => 'paragraph',
                                            'children' => [
                                                [
                                                    'text' => 'Tie-dye your way to groovy fashion'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'id' => 'page_links_id_6',
                            'title' => 'Special occasions',
                            'url' => '/p/wearing-my-pjs-gc-226-sock',
                            'asset' => [
                                'url' => 'https://media.graphassets.com/TFK8AnNgSFSMjsA1J1rK',
                                'width' => 800,
                                'height' => 1200,
                                'mimeType' => 'image/jpeg',
                                'size' => 412915,
                                'alt' => 'Special'
                            ],
                            'description' => [
                                'raw' => [
                                    'children' => [
                                        [
                                            'type' => 'paragraph',
                                            'children' => [
                                                [
                                                    'text' => 'Get ready to dazzle with impeccable taste'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'id' => 'page_links_id_7',
                            'title' => 'Modest',
                            'url' => '/women/business',
                            'asset' => [
                                'url' => 'https://media.graphassets.com/QFMMMSwCSKSqH09hElog',
                                'width' => 800,
                                'height' => 1200,
                                'mimeType' => 'image/jpeg',
                                'size' => 495180,
                                'alt' => 'Modest'
                            ],
                            'description' => [
                                'raw' => [
                                    'children' => [
                                        [
                                            'type' => 'paragraph',
                                            'children' => [
                                                [
                                                    'text' => 'Have a \'sole\'-ful celebration'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                ],
                [
                    'typeId' => 'cmsRowProduct',
                    'id' => 'cmsRowProduct_3',
                    'variant' => 'Grid',
                    'identity' => 'home-favorites',
                    'title' => 'Our Season favorites',
                    'pageLinks' => [
                        [
                            'title' => 'View favorites',
                            'url' => '/men/art',
                            'description' => null,
                            'asset' => null
                        ]
                    ],
                    'productCopy' => [
                        'raw' => [
                            'children' => [
                                [
                                    'type' => 'paragraph',
                                    'children' => [
                                        [
                                            'text' => 'Do we need a text here?'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'typeId' => 'cmsRowSpecialBanner',
                    'id' => 'cmsRowSpecialBanner_4',
                    'topic' => 'A peek into history',
                    'asset' => [
                        'url' => 'https://gc-backend.softcommerce.dev/media/images/backgrounds/bg-01-900x980.jpg',
                        'width' => 1532,
                        'height' => 1678,
                        'mimeType' => 'image/jpeg',
                        // 'size' => 1247616,
                        'alt' => 'Modern masters'
                    ],
                    'copy' => [
                        'raw' => [
                            'children' => [
                                [
                                    'type' => 'heading-two',
                                    'children' => [
                                        [
                                            'bold' => true,
                                            'text' => 'Impressionists'
                                        ],
                                        [
                                            'text' => ' and modern'
                                        ],
                                        [
                                            'bold' => true,
                                            'text' => ' masters'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'pageLinks' => [
                        [
                            'title' => 'A complete collection',
                            'url' => '/men/art',
                            'description' => null,
                            'asset' => null
                        ]
                    ]
                ],
                [
                    'typeId' => 'cmsRowQuote',
                    'id' => 'cmsRowQuote_5',
                    'quote' => [
                        'raw' => [
                            'children' => [
                                [
                                    'type' => 'paragraph',
                                    'children' => [
                                        [
                                            'text' => 'Whether you\'re looking for something stylish for a night time party with friends or something professional for a conference call in the boardroom, GraphCommerce has you covered with socks that are both comfortable and versatile. So why settle for boring and plain socks when you can upgrade your footwear game?'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'typeId' => 'cmsRowProduct',
                    'id' => 'cmsRowProduct_6',
                    'variant' => 'Grid',
                    'identity'=> 'home-latest',
                    'title' => 'Latest designs',
                    'pageLinks' => [],
                    'productCopy' => [
                        'raw' => [
                            'children' => [
                                [
                                    'type' => 'paragraph',
                                    'children' => [
                                        [
                                            'text' => ''
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'typeId' => 'cmsRowLinks',
                    'id' => 'RowLinks_7',
                    'title' => 'Brands',
                    'linksVariant' => 'LogoSwiper',
                    'rowLinksCopy' => [
                        'raw' => [
                            'children' => [
                                [
                                    'type' => 'paragraph',
                                    'children' => [
                                        [
                                            ''
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'pageLinks' => [
                        [
                            'id' => 'page_links_id_1',
                            'title' => 'Moma',
                            'url' => '/',
                            'asset' => [
                                'url' => 'https://media.graphassets.com/YjGjS3dRKXP2UWQqX81Q',
                                'width' => 209,
                                'height' => 53,
                                'mimeType' => 'image/svg+xml',
                                'size' => 777,
                                'alt' => 'Moma'
                            ],
                            'description' => [
                                'raw' => [
                                    'children' => [
                                        [
                                            'type' => 'paragraph',
                                            'children' => [
                                                [
                                                    ''
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'id' => 'page_links_id_2',
                            'title' => 'Nike',
                            'url' => '/',
                            'asset' => [
                                'url' => 'https://media.graphassets.com/8tljUi1TbKQHx56KuR7A',
                                'width' => 1000,
                                'height' => 356,
                                'mimeType' => 'image/svg+xml',
                                'size' => 966,
                                'alt' => 'Nike'
                            ],
                            'description' => [
                                'raw' => [
                                    'children' => [
                                        [
                                            'type' => 'paragraph',
                                            'children' => [
                                                [
                                                    'text' => ''
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'id' => 'page_links_id_3',
                            'title' => 'McDonalds',
                            'url' => '/',
                            'asset' => [
                                'url' => 'https://media.graphassets.com/qn5R9MG4Tke7UsbaFT4u',
                                'width' => 296,
                                'height' => 238,
                                'mimeType' => 'image/svg+xml',
                                'size' => 1714,
                                'alt' => 'McDonalds'
                            ],
                            'description' => null
                        ],
                        [
                            'id' => 'page_links_id_4',
                            'title' => 'Marvel',
                            'url' => '/',
                            'asset' => [
                                'url' => 'https://media.graphassets.com/3hnAaPkoRi6vlZVt37O9',
                                'width' => 1001,
                                'height' => 403,
                                'mimeType' => 'image/svg+xml',
                                'size' => 3229,
                                'alt' => 'Marvel'
                            ],
                            'description' => null
                        ]
                    ],
                ],
                [
                    'typeId' => 'cmsRowProduct',
                    'id' => 'cmsRowProduct_8',
                    'variant' => 'Swipeable',
                    'identity'=> 'home-swipable',
                    'title' => 'Power to the Women',
                    'asset' => null,
                    'pageLinks' => [],
                    'productCopy' => null
                ],
                [
                    'typeId' => 'cmsRowProduct',
                    'id' => 'cmsRowProduct_9',
                    'variant' => 'Backstory',
                    'identity'=> 'home-product-backstory',
                    'title' => 'Home Product Backstory',
                    'asset' => [
                        'url' => 'https://media.graphassets.com/vPcBYvRSGyWUAx7MGGmW',
                        'width' => 1192,
                        'height' => 1589,
                        'mimeType' => 'image/jpeg',
                        'size' => 1766866,
                        'alt' => 'The definition of socks'
                    ],
                    'pageLinks' => [],
                    'productCopy' => [
                        'raw' => [
                            'children' => [
                                [
                                    'type' => 'paragraph',
                                    'children' => [
                                        [
                                            'text' => 'Once upon a time, in a world where art and fashion collided, there lived a small team of designers who dreamed of creating the most unique and intricate sock designs. They took inspiration from modern art and combined it with traditional patterns to create one-of-a-kind prints.'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'typeId' => 'cmsRowLinks',
                    'id' => 'RowLinks_10',
                    'title' => 'Customer service',
                    'linksVariant' => 'Inline',
                    'rowLinksCopy' => null,
                    'pageLinks' => [
                        [
                            'id' => 'page_links_id_1',
                            'title' => 'Order',
                            'url' => '/service/order',
                            'asset' => null,
                            'description' => null
                        ],
                        [
                            'id' => 'page_links_id_2',
                            'title' => 'Brand/Sizes',
                            'url' => '/service/brands-and-sizes',
                            'asset' => null,
                            'description' => null
                        ],
                        [
                            'id' => 'page_links_id_3',
                            'title' => 'Newsletter',
                            'url' => '/service/newsletter',
                            'asset' => null,
                            'description' => null
                        ],
                        [
                            'id' => 'page_links_id_4',
                            'title' => 'Payment Information',
                            'url' => '/service/payment-information',
                            'asset' => null,
                            'description' => null
                        ],
                        [
                            'id' => 'page_links_id_5',
                            'title' => 'Returns',
                            'url' => '/service/returns',
                            'asset' => null,
                            'description' => null
                        ],
                        [
                            'id' => 'page_links_id_4',
                            'title' => 'Shipping',
                            'url' => '/service/shipping',
                            'asset' => null,
                            'description' => null
                        ]
                    ],
                ],
                [
                    'typeId' => 'cmsRowLinks',
                    'id' => 'RowLinks_11',
                    'title' => 'About Us',
                    'linksVariant' => 'Inline',
                    'rowLinksCopy' => null,
                    'pageLinks' => [
                        [
                            'id' => 'page_links_id_1',
                            'title' => 'Contact',
                            'url' => '/contacts',
                            'asset' => null,
                            'description' => null
                        ],
                        [
                            'id' => 'page_links_id_2',
                            'title' => 'Careers',
                            'url' => '/careers',
                            'asset' => null,
                            'description' => null
                        ],
                        [
                            'id' => 'page_links_id_3',
                            'title' => 'About Us',
                            'url' => '/about-us',
                            'asset' => null,
                            'description' => null
                        ],
                        [
                            'id' => 'page_links_id_4',
                            'title' => 'Loyalty program',
                            'url' => '/loyalty-program',
                            'asset' => null,
                            'description' => null
                        ],
                        [
                            'id' => 'page_links_id_5',
                            'title' => 'Corporate responsibility',
                            'url' => '/corporate-responsibility',
                            'asset' => null,
                            'description' => null
                        ]
                    ],
                ],
            ]
            /*
            'cmsRowContent' => [
                'cmsRowHeroBanner' => [
                    'id' => 'cmsRowHeroBanner',
                    'heroAsset' => [
                        'url' => 'https://gc-backend.softcommerce.dev/media/video/AdobeStock_450634490_Video_4K_Preview.mov',
                        'width' => null,
                        'height' => null,
                        'mimeType' => 'video/mov',
                        'size' => 2839817,
                        'alt' => null
                    ],
                    'pageLinks' => [
                        [
                            'title' => 'Shop Art',
                            'url' => '/men/art',
                            'description' => null,
                            'asset' => null
                        ]
                    ],
                    'copy' => [
                        'raw' => [
                            'children' => [
                                [
                                    'type' => 'paragraph',
                                    'children' => [
                                        [
                                            'text' => 'Enhanced shopping experience'
                                        ]
                                    ]
                                ],
                                [
                                    'type' => 'heading-one',
                                    'children' => [
                                        [
                                            'bold' => true,
                                            'text' => 'through'
                                        ],
                                        [
                                            'text' => ' digital innovation.'
                                        ]
                                    ]
                                ],
                                [
                                    'type' => 'paragraph',
                                    'children' => [
                                        [
                                            'text' => ''
                                        ]
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            ],
            */
        ];
    }

    /**
     * @param PageInterface $page
     * @return array
     */
    private function getRowContentData(PageInterface $page): array
    {
        $this->rowContentBuilder->execute(
            $page->getData('gc_metadata'),
            (int) $page->getStoreId()
        );

        return $this->rowContentBuilder->getDataStorage()->getData();
    }
}
