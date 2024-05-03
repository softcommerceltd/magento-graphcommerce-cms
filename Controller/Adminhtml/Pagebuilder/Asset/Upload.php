<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\GraphCommerceCms\Controller\Adminhtml\Pagebuilder\Asset;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Magento\MediaGallerySynchronizationApi\Api\SynchronizeFilesInterface;
use Magento\MediaGalleryUiApi\Api\ConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritDoc
 * Used to upload media assets.
 */
class Upload extends Action implements HttpPostActionInterface
{
    private const UPLOAD_DIR = 'assets';

    const ADMIN_RESOURCE = 'Magento_Backend::content';

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $mediaGalleryConfig;

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var UploaderFactory
     */
    private UploaderFactory $uploaderFactory;

    /**
     * @var Images
     */
    private Images $cmsWysiwygImages;

    /**
     * @var IsPathExcludedInterface
     */
    private IsPathExcludedInterface $isPathExcluded;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private Filesystem\Directory\WriteInterface $mediaDirectory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var SynchronizeFilesInterface
     */
    private SynchronizeFilesInterface $synchronizeFiles;

    /**
     * @var string[]
     */
    private array $allowedFileExtensions;

    /**
     * @var string[]
     */
    private array $allowedMimeTypes;

    /**
     * @param ConfigInterface $mediaGalleryConfig
     * @param Filesystem $filesystem
     * @param Images $cmsWysiwygImages
     * @param IsPathExcludedInterface $isPathExcluded
     * @param LoggerInterface $logger
     * @param JsonFactory $resultJsonFactory
     * @param SynchronizeFilesInterface $synchronizeFiles
     * @param UploaderFactory $uploaderFactory
     * @param Context $context
     * @param array $allowedFileExtensions
     * @param array $allowedMimeTypes
     * @throws FileSystemException
     */
    public function __construct(
        ConfigInterface $mediaGalleryConfig,
        Filesystem $filesystem,
        Images $cmsWysiwygImages,
        isPathExcludedInterface $isPathExcluded,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory,
        SynchronizeFilesInterface $synchronizeFiles,
        UploaderFactory $uploaderFactory,
        Context $context,
        array $allowedFileExtensions = [],
        array $allowedMimeTypes = []
    ) {
        $this->mediaGalleryConfig = $mediaGalleryConfig;
        $this->cmsWysiwygImages = $cmsWysiwygImages;
        $this->isPathExcluded = $isPathExcluded;
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->synchronizeFiles = $synchronizeFiles;
        $this->uploaderFactory = $uploaderFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->allowedFileExtensions = $allowedFileExtensions;
        $this->allowedMimeTypes = $allowedMimeTypes;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        try {
            $result = $this->uploadMediaGalleryFile();
            $result = $this->saveMediaGalleryInformation($result);
        } catch (\Exception $e) {
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }

        return $this->resultJsonFactory->create()->setData($result);
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function uploadMediaGalleryFile(): array
    {
        $fieldName = $this->getRequest()->getParam('param_name');
        $fileUploader = $this->uploaderFactory->create(['fileId' => $fieldName]);

        $fileUploader->setFilesDispersion(false);
        $fileUploader->setAllowRenameFiles(false);
        $fileUploader->setAllowedExtensions($this->allowedFileExtensions);
        $fileUploader->setAllowCreateFolders(true);

        if (!$fileUploader->checkMimeType($this->allowedMimeTypes)) {
            throw new LocalizedException(__('File validation failed.'));
        }

        $result = $fileUploader->save(
            $this->mediaDirectory->getAbsolutePath(self::UPLOAD_DIR)
        );

        $result['id'] = $this->cmsWysiwygImages->idEncode($result['file']);
        $result['url'] = $this->getFilePath($result['file'] ?? '');

        return $result;
    }

    /**
     * @param array $result
     * @return array
     * @throws LocalizedException
     */
    private function saveMediaGalleryInformation(array $result): array
    {
        $mediaFolder = $this->mediaDirectory->getAbsolutePath();

        if (!$this->mediaGalleryConfig->isEnabled()
            || !str_starts_with($result['path'] ?? '', $mediaFolder)
        ) {
            return $result;
        }

        $path = $this->mediaDirectory->getRelativePath(
            rtrim($result['path'] ?? '', '/') . '/' . ltrim($result['file'] ?? '', '/')
        );

        if ($this->canSaveMediaInformation($path)) {
            $this->synchronizeFiles->execute([$path]);
        }

        return $result;
    }

    /**
     * @param string $imageName
     * @return string
     */
    private function getFilePath(string $imageName): string
    {
        $baseUrl = $this->_backendUrl->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]);
        $path = rtrim(self::UPLOAD_DIR, '/') . '/' . ltrim($imageName, '/');
        return $baseUrl . $path;
    }

    /**
     * @param string $path
     * @return bool
     */
    private function canSaveMediaInformation(string $path): bool
    {
        try {
            return $path
                && !$this->isPathExcluded->execute($path)
                && preg_match('#\.(' . implode("|", $this->allowedFileExtensions) . ')$# i', $path);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            return false;
        }
    }
}
