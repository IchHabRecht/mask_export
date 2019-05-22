<?php
namespace IchHabRecht\MaskExport\Controller;

/*
 * This file is part of the TYPO3 extension mask_export.
 *
 * (c) 2016 Nicole Cordes <typo3@cordes.co>, CPS-IT GmbH
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use IchHabRecht\MaskExport\Aggregate\AggregateCollection;
use IchHabRecht\MaskExport\Aggregate\BackendPreviewAggregate;
use IchHabRecht\MaskExport\Aggregate\ContentElementIconAggregate;
use IchHabRecht\MaskExport\Aggregate\ContentRenderingAggregate;
use IchHabRecht\MaskExport\Aggregate\ExtensionConfigurationAggregate;
use IchHabRecht\MaskExport\Aggregate\InlineContentColPosAggregate;
use IchHabRecht\MaskExport\Aggregate\InlineContentCTypeAggregate;
use IchHabRecht\MaskExport\Aggregate\NewContentElementWizardAggregate;
use IchHabRecht\MaskExport\Aggregate\TcaAggregate;
use IchHabRecht\MaskExport\Aggregate\TtContentOverridesAggregate;
use IchHabRecht\MaskExport\FileCollection\FileCollection;
use IchHabRecht\MaskExport\FileCollection\LanguageFileCollection;
use IchHabRecht\MaskExport\FileCollection\PhpFileCollection;
use IchHabRecht\MaskExport\FileCollection\PlainTextFileCollection;
use IchHabRecht\MaskExport\FileCollection\SqlFileCollection;
use MASK\Mask\Domain\Repository\StorageRepository;
use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extensionmanager\Domain\Model\Extension;
use TYPO3\CMS\Extensionmanager\Service\ExtensionManagementService;
use TYPO3\CMS\Extensionmanager\Utility\InstallUtility;

class ExportController extends ActionController
{
    /**
     * @var array
     */
    protected $aggregateClassNames = [
        ExtensionConfigurationAggregate::class,
        TcaAggregate::class,
        ContentElementIconAggregate::class,
        TtContentOverridesAggregate::class,
        ContentRenderingAggregate::class,
        NewContentElementWizardAggregate::class,
        InlineContentColPosAggregate::class,
        InlineContentCTypeAggregate::class,
        BackendPreviewAggregate::class,
    ];

    /**
     * @var string
     */
    protected $defaultExtensionName = 'my_mask_export';

    /**
     * @var array
     */
    protected $fileCollectionClassNames = [
        LanguageFileCollection::class,
        PlainTextFileCollection::class,
        PhpFileCollection::class,
        SqlFileCollection::class,
    ];

    /**
     * @var array
     */
    protected $maskConfiguration;

    public function __construct(StorageRepository $storageRepository)
    {
        parent::__construct();

        $this->maskConfiguration = (array)$storageRepository->load();
    }

    /**
     * @param string $vendorName
     * @param string $extensionName
     */
    public function listAction($vendorName = '', $extensionName = '')
    {
        $extensionName = $extensionName ?: $this->getExtensionName();
        $vendorName = $vendorName ?: $this->getVendorName();

        $files = $this->getFiles($vendorName, $extensionName);

        $this->view->assignMultiple(
            [
                'composerMode' => Bootstrap::usesComposerClassLoading(),
                'vendorName' => $vendorName,
                'extensionName' => $extensionName,
                'files' => $files,
            ]
        );
    }

    /**
     * @param string $vendorName
     * @param string $extensionName
     */
    public function saveAction($vendorName = '', $extensionName = '')
    {
        if (empty($vendorName)) {
            $vendorName = $this->getVendorName();
        } else {
            $vendorName = str_replace('-', '_', $vendorName);
            if (strpos($vendorName, '_') !== false) {
                $vendorName = GeneralUtility::underscoredToUpperCamelCase($vendorName);
            }
        }
        $extensionName = $extensionName ?: $this->getExtensionName();

        $backendUser = $this->getBackendUser();
        $backendUser->uc['mask_export']['vendorName'] = $vendorName;
        $backendUser->uc['mask_export']['extensionName'] = $extensionName;
        $backendUser->writeUC();

        $action = 'list';
        if ($this->request->hasArgument('submit')) {
            $submit = strtolower($this->request->getArgument('submit'));
            if (in_array($submit, ['download', 'install'], true)) {
                $action = $submit;
            }
        }

        $this->redirect($action, null, null, ['vendorName' => $vendorName, 'extensionName' => $extensionName]);
    }

    /**
     * @param string $vendorName
     * @param string $extensionName
     */
    public function downloadAction($vendorName, $extensionName)
    {
        $files = $this->getFiles($vendorName, $extensionName);

        $zipFile = tempnam(sys_get_temp_dir(), 'zip');

        $zipArchive = new \ZipArchive();
        $zipArchive->open($zipFile, \ZipArchive::OVERWRITE);

        foreach ($files as $file => $content) {
            $zipArchive->addFromString($file, $content);
        }

        $zipArchive->close();

        header('Content-Type: application/zip');
        header('Content-Length: ' . filesize($zipFile));
        header('Content-Disposition: attachment; filename="' . $extensionName . '_0.1.0_' . date('YmdHi', $GLOBALS['EXEC_TIME']) . '.zip"');

        readfile($zipFile);
        unlink($zipFile);
        exit;
    }

    /**
     * @param string $vendorName
     * @param string $extensionName
     */
    public function installAction($vendorName, $extensionName)
    {
        $paths = Extension::returnInstallPaths();
        if (empty($paths['Local']) || !file_exists($paths['Local'])) {
            throw new \RuntimeException('Local extension install path is missing', 1500061028);
        }

        $extensionPath = $paths['Local'] . $extensionName;
        $files = $this->getFiles($vendorName, $extensionName);
        $this->writeExtensionFilesToPath($files, $extensionPath);

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        if (!Bootstrap::usesComposerClassLoading()) {
            $managementService = $objectManager->get(ExtensionManagementService::class);
            $managementService->reloadPackageInformation($extensionName);
            $extension = $managementService->getExtension($extensionName);
            $installInformation = $managementService->installExtension($extension);

            if (is_array($installInformation)) {
                $this->addFlashMessage(
                    '',
                    'Extension ' . $extensionName . ' was successfully installed',
                    AbstractMessage::OK
                );
            } else {
                $this->addFlashMessage(
                    'An error occurred during the installation of ' . $extensionName,
                    'Error',
                    AbstractMessage::ERROR
                );
            }
        } else {
            $installUtility = $objectManager->get(InstallUtility::class);
            $installUtility->reloadCaches();

            $this->addFlashMessage(
                '',
                'Extension files of ' . $extensionName . ' were written successfully',
                AbstractMessage::OK
            );
        }

        $this->redirect('list');
    }

    /**
     * @return string
     */
    protected function getExtensionName()
    {
        $extensionName = $this->defaultExtensionName;

        if (!empty($this->maskConfiguration['mask_export']['extensionName'])) {
            $extensionName = $this->maskConfiguration['mask_export']['extensionName'];
        } else {
            $backendUser = $this->getBackendUser();
            if (!empty($backendUser->uc['mask_export']['extensionName'])) {
                $extensionName = $backendUser->uc['mask_export']['extensionName'];
            }
        }

        return $extensionName;
    }

    /**
     * @return string
     */
    protected function getVendorName()
    {
        $vendorName = GeneralUtility::underscoredToUpperCamelCase($this->defaultExtensionName);

        if (!empty($this->maskConfiguration['mask_export']['vendorName'])) {
            $vendorName = $this->maskConfiguration['mask_export']['vendorName'];
        } else {
            $backendUser = $this->getBackendUser();
            if (!empty($backendUser->uc['mask_export']['vendorName'])) {
                $vendorName = $backendUser->uc['mask_export']['vendorName'];
            }
        }

        return $vendorName;
    }

    /**
     * @param string $vendorName
     * @param string $extensionName
     * @return array
     */
    protected function getFiles($vendorName, $extensionName)
    {
        $this->maskConfiguration['mask_export'] = [
            'extensionName' => $extensionName,
            'vendorName' => $vendorName,
        ];

        $aggregateCollection = GeneralUtility::makeInstance(
            AggregateCollection::class,
            $this->aggregateClassNames,
            $this->maskConfiguration
        )->getCollection();

        $files = GeneralUtility::makeInstance(
            FileCollection::class,
            $this->fileCollectionClassNames,
            $aggregateCollection
        )->getFiles();

        $files = $this->replaceExtensionInformation($vendorName, $extensionName, $files);
        $files = $this->sortFiles($files);

        return $files;
    }

    /**
     * @param array $files
     * @param string $extensionPath
     */
    protected function writeExtensionFilesToPath(array $files, $extensionPath)
    {
        if (file_exists($extensionPath)) {
            $finder = new Finder();
            $finder
                ->directories()
                ->ignoreDotFiles(true)
                ->ignoreVCS(true)
                ->depth(0)
                ->in($extensionPath);

            foreach ($finder as $directory) {
                $directoryPath = $directory->getRealPath();

                if (file_exists($directoryPath)) {
                    GeneralUtility::rmdir($directoryPath, true);
                }
            }
        }

        foreach ($files as $file => $content) {
            $absoluteFile = $extensionPath . '/' . $file;
            if (!file_exists(dirname($absoluteFile))) {
                GeneralUtility::mkdir_deep(dirname($absoluteFile));
            }
            GeneralUtility::writeFile($absoluteFile, $content, true);
        }
    }

    /**
     * @param string $vendorName
     * @param string $extensionKey
     * @param array $files
     * @return array
     */
    protected function replaceExtensionInformation($vendorName, $extensionKey, array $files)
    {
        $newFiles = [];
        foreach ($files as $file => $fileContent) {
            $newFiles[$this->replaceExtensionKey($vendorName, $extensionKey, $file)] = $this->replaceExtensionKey(
                $vendorName,
                $extensionKey,
                $fileContent
            );
        }

        return $newFiles;
    }

    /**
     * @param string $vendorName
     * @param string $extensionKey
     * @param string $string
     * @return string
     */
    protected function replaceExtensionKey($vendorName, $extensionKey, $string)
    {
        $camelCasedExtensionKey = GeneralUtility::underscoredToUpperCamelCase($extensionKey);
        $lowercaseExtensionKey = strtolower($camelCasedExtensionKey);

        $string = preg_replace(
            '/(\s+|\'|,|.)(tx_)?mask(_|\.)/',
            '\\1\\2' . $lowercaseExtensionKey . '\\3',
            $string
        );
        $string = preg_replace(
            '/(.)Mask\\1/',
            '\\1' . $camelCasedExtensionKey . '\\1',
            $string
        );
        $string = preg_replace(
            '/(.)mask\\1/',
            '\\1' . $extensionKey . '\\1',
            $string
        );
        $string = preg_replace(
            '/MASK/',
            $vendorName,
            $string
        );
        $string = preg_replace(
            '/([>(=])mask([<)\/])/',
            '\\1' . $extensionKey . '\\2',
            $string
        );
        $string = preg_replace(
            '/EXT:mask/',
            'EXT:' . $extensionKey,
            $string
        );
        $string = preg_replace(
            '/(")mask(\/)/',
            '"' . strtolower($vendorName) . '/',
            $string
        );
        $string = preg_replace(
            '/(\/)mask(")/',
            '/' . str_replace('_', '-', $extensionKey) . '"',
            $string
        );
        $string = preg_replace(
            '/\${(m)ask}/i',
            '\\1ask',
            $string
        );

        return $string;
    }

    /**
     * @param array $files
     * @return array
     */
    protected function sortFiles(array $files)
    {
        uksort($files, function ($a, $b) {
            if (substr_count($a, '/') === 0 && substr_count($b, '/') > 0) {
                return -1;
            } elseif (substr_count($a, '/') > 0 && substr_count($b, '/') === 0) {
                return 1;
            }

            if (strpos($b, dirname($a)) === 0 || strpos($a, dirname($b)) === 0) {
                if (substr_count($a, '/') > substr_count($b, '/')) {
                    return 1;
                } elseif (substr_count($a, '/') < substr_count($b, '/')) {
                    return -1;
                }
            }

            return strcasecmp($a, $b);
        });

        return $files;
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
