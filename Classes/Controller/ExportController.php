<?php
namespace CPSIT\MaskExport\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Nicole Cordes <typo3@cordes.co>, CPS-IT GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use CPSIT\MaskExport\Aggregate\AggregateCollection;
use CPSIT\MaskExport\Aggregate\BackendPreviewAggregate;
use CPSIT\MaskExport\Aggregate\ContentRenderingAggregate;
use CPSIT\MaskExport\Aggregate\ExtensionConfigurationAggregate;
use CPSIT\MaskExport\Aggregate\InlineContentColPosAggregate;
use CPSIT\MaskExport\Aggregate\NewContentElementWizardAggregate;
use CPSIT\MaskExport\Aggregate\TcaAggregate;
use CPSIT\MaskExport\Aggregate\TtContentOverridesAggregate;
use CPSIT\MaskExport\FileCollection\FileCollection;
use CPSIT\MaskExport\FileCollection\LanguageFileCollection;
use CPSIT\MaskExport\FileCollection\PhpFileCollection;
use CPSIT\MaskExport\FileCollection\PlainTextFileCollection;
use CPSIT\MaskExport\FileCollection\SqlFileCollection;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class ExportController extends ActionController
{
    /**
     * @var array
     */
    protected $aggregateClassNames = [
        ExtensionConfigurationAggregate::class,
        TcaAggregate::class,
        TtContentOverridesAggregate::class,
        ContentRenderingAggregate::class,
        NewContentElementWizardAggregate::class,
        InlineContentColPosAggregate::class,
        BackendPreviewAggregate::class,
    ];

    /**
     * @var string
     */
    protected $defaultExtensionName = 'mask_export';

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
     * StorageRepository
     *
     * @var \MASK\Mask\Domain\Repository\StorageRepository
     * @inject
     */
    protected $storageRepository;

    /**
     * action list
     *
     * @param string $extensionName
     */
    public function listAction($extensionName = '')
    {
        $backendUser = $this->getBackendUser();
        if (!empty($extensionName)) {
            $backendUser->uc['mask_export']['extensionName'] = $extensionName;
            $backendUser->writeUC();
        } elseif (!empty($backendUser->uc['mask_export']['extensionName'])) {
            $extensionName = $backendUser->uc['mask_export']['extensionName'];
        } else {
            $extensionName = $this->defaultExtensionName;
        }

        $files = $this->getFiles($extensionName);

        $this->view->assignMultiple(
            [
                'extensionName' => $extensionName,
                'files' => $files,
            ]
        );
    }

    /**
     * @param string $extensionName
     */
    public function downloadAction($extensionName)
    {
        $files = $this->getFiles($extensionName);

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
     * @param string $extensionName
     * @return array
     */
    protected function getFiles($extensionName)
    {
        $maskConfiguration = $this->storageRepository->load();

        $aggregateCollection = GeneralUtility::makeInstance(
            AggregateCollection::class,
            $this->aggregateClassNames,
            $maskConfiguration
        )->getCollection();

        $files = GeneralUtility::makeInstance(
            FileCollection::class,
            $this->fileCollectionClassNames,
            $aggregateCollection
        )->getFiles();

        $files = $this->replaceExtensionInformation($extensionName, $files);
        $files = $this->sortFiles($files);

        return $files;
    }

    /**
     * @param string $extensionKey
     * @param array $files
     * @return array
     */
    protected function replaceExtensionInformation($extensionKey, array $files)
    {
        $newFiles = [];
        foreach ($files as $file => $fileContent) {
            $newFiles[$this->replaceExtensionKey($extensionKey, $file)] = $this->replaceExtensionKey($extensionKey,
                $fileContent);
        }

        return $newFiles;
    }

    /**
     * @param string $extensionKey
     * @param string $string
     * @return string
     */
    protected function replaceExtensionKey($extensionKey, $string)
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
            '/MASK/',
            strtoupper($camelCasedExtensionKey),
            $string
        );
        $string = preg_replace(
            '/(.)mask\\1/',
            '\\1' . $extensionKey . '\\1',
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

        return $string;
    }

    /**
     * @param array $files
     * @return array
     */
    protected function sortFiles(array $files)
    {
        uksort($files, function ($a, $b) {
            if (substr_count($a, '/') === substr_count($b, '/')) {
                return strcasecmp($a, $b);
            } elseif (strpos($a, '/') === false || substr_count($a, '/') < substr_count($b, '/')) {
                return -1;
            }
            return 1;
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
