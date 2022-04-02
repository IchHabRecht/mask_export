<?php

declare(strict_types=1);

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
use TYPO3\CMS\Core\Core\Environment;
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
        $this->maskConfiguration = (array)$storageRepository->load();
    }

    /**
     * @param string $vendorName
     * @param string $extensionName
     * @param array $elements
     */
    public function listAction($vendorName = '', $extensionName = '', $elements = [])
    {
        $extensionName = $extensionName ?: $this->getExtensionName();
        $vendorName = $vendorName ?: $this->getVendorName();
        $elements = $elements ?: $this->getElements();

        $files = $this->getFiles($vendorName, $extensionName, $elements);

        $this->view->assignMultiple(
            [
                'composerMode' => Environment::isComposerMode(),
                'vendorName' => $vendorName,
                'extensionName' => $extensionName,
                'availableElements' => $this->maskConfiguration['tt_content']['elements'] ?? [],
                'includedElements' => array_combine($elements, $elements),
                'files' => $files,
            ]
        );
    }

    /**
     * @param string $vendorName
     * @param string $extensionName
     * @param array $elements
     */
    public function saveAction($vendorName = '', $extensionName = '', $elements = [])
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
        $backendUser->uc['mask_export']['elements'] = implode(',', $elements);
        $backendUser->writeUC();

        $action = 'list';
        if ($this->request->hasArgument('submit')) {
            $submit = strtolower($this->request->getArgument('submit'));
            if (in_array($submit, ['download', 'install'], true)) {
                $action = $submit;
            }
        }

        $this->forward($action, null, null, ['vendorName' => $vendorName, 'extensionName' => $extensionName, 'elements' => $elements]);
    }

    /**
     * @param string $vendorName
     * @param string $extensionName
     * @param array $elements
     */
    public function downloadAction($vendorName, $extensionName, $elements)
    {
        $files = $this->getFiles($vendorName, $extensionName, $elements);

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
     * @param array $elements
     */
    public function installAction($vendorName, $extensionName, $elements)
    {
        $paths = Extension::returnInstallPaths();
        if (empty($paths['Local']) || !file_exists($paths['Local'])) {
            throw new \RuntimeException('Local extension install path is missing', 1500061028);
        }

        $extensionPath = $paths['Local'] . $extensionName;
        $files = $this->getFiles($vendorName, $extensionName, $elements);
        $this->writeExtensionFilesToPath($files, $extensionPath);

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        if (!Environment::isComposerMode()) {
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

        if (!empty($this->maskConfiguration['mask_export']['elements']['configuration']['label'])) {
            $extensionName = $this->maskConfiguration['mask_export']['elements']['configuration']['label'];
        } elseif (!empty($this->maskConfiguration['mask_export']['extensionName'])) {
            // Backwards compatibility for mask_export < 4.0
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

        if (!empty($this->maskConfiguration['mask_export']['elements']['configuration']['shortLabel'])) {
            $vendorName = $this->maskConfiguration['mask_export']['elements']['configuration']['shortLabel'];
        } elseif (!empty($this->maskConfiguration['mask_export']['vendorName'])) {
            // Backwards compatibility for mask_export < 4.0
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
     * @return string[]
     */
    protected function getElements()
    {
        $elements = [];

        if (!empty($this->maskConfiguration['mask_export']['elements']['configuration']['columns'])) {
            $elements = $this->maskConfiguration['mask_export']['elements']['configuration']['columns'];
        } elseif (!empty($this->maskConfiguration['mask_export']['includedElements'])) {
            // Backwards compatibility for mask_export < 4.0
            $elements = $this->maskConfiguration['mask_export']['includedElements'];
        } else {
            $backendUser = $this->getBackendUser();
            if (!empty($backendUser->uc['mask_export']['elements'])) {
                $elements = explode(',', $backendUser->uc['mask_export']['elements']);
            }
        }

        return $elements;
    }

    /**
     * @param string $vendorName
     * @param string $extensionName
     * @param array $elements
     * @return array
     */
    protected function getFiles($vendorName, $extensionName, $elements)
    {
        $aggregatedMaskConfiguration = $this->prepareConfiguration($vendorName, $extensionName, $elements);

        $aggregateCollection = GeneralUtility::makeInstance(
            AggregateCollection::class,
            $this->aggregateClassNames,
            $aggregatedMaskConfiguration
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

    protected function prepareConfiguration(string $vendorName, string $extensionName, array $elements)
    {
        $aggregatedConfiguration = $this->maskConfiguration;

        $aggregatedConfiguration['mask_export'] = [
            'elements' => [
                'configuration' => [
                    'key' => 'configuration',
                    'label' => $extensionName,
                    'shortLabel' => $vendorName,
                    'columns' => $elements,
                ],
            ],
        ];

        if (empty($aggregatedConfiguration['tt_content']['elements'])
            || empty($elements)
        ) {
            return $aggregatedConfiguration;
        }

        // Use selected elements only
        $aggregatedConfiguration['tt_content']['elements'] = array_intersect_key(
            $aggregatedConfiguration['tt_content']['elements'],
            array_flip($elements)
        );

        // Find all used fields in elements and foreign tables
        $columns = [];
        $closure = null;
        $closure = static function ($value) use ($aggregatedConfiguration, &$columns, &$closure) {
            foreach (($value['columns'] ?? []) as $field) {
                $columns[] = $field;
                if (!empty($aggregatedConfiguration[$field]['tca'])) {
                    array_map($closure, [['columns' => array_keys($aggregatedConfiguration[$field]['tca'])]]);
                }
            }
        };

        array_map($closure, $aggregatedConfiguration['tt_content']['elements']);

        $columns = array_combine($columns, $columns);

        // Remove unused fields from configuration
        foreach ($aggregatedConfiguration as $table => &$configuration) {
            if (!empty($configuration['sql'])) {
                $configuration['sql'] = array_intersect_key(
                    $configuration['sql'],
                    $columns
                );
                if (empty($configuration['sql'])) {
                    unset($configuration['sql']);
                }
            }

            if (!empty($configuration['tca'])) {
                $configuration['tca'] = array_intersect_key(
                    $configuration['tca'],
                    $columns
                );
                if (empty($configuration['tca'])) {
                    unset($configuration['tca']);
                }
            }

            if (empty($configuration)) {
                unset($aggregatedConfiguration[$table]);
            }
        }

        return $aggregatedConfiguration;
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
        uksort($files, static function ($a, $b) {
            if (substr_count($a, '/') === 0 && substr_count($b, '/') > 0) {
                return -1;
            }

            if (substr_count($a, '/') > 0 && substr_count($b, '/') === 0) {
                return 1;
            }

            if (strpos($b, dirname($a)) === 0 || strpos($a, dirname($b)) === 0) {
                if (substr_count($a, '/') > substr_count($b, '/')) {
                    return 1;
                }

                if (substr_count($a, '/') < substr_count($b, '/')) {
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
