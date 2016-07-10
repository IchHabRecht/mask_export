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
use CPSIT\MaskExport\Aggregate\ExtensionConfigurationAggregate;
use CPSIT\MaskExport\Aggregate\NewContentElementWizardAggregate;
use CPSIT\MaskExport\Aggregate\TcaAggregate;
use CPSIT\MaskExport\Aggregate\TtContentOverridesAggregate;
use CPSIT\MaskExport\FileCollection\FileCollection;
use CPSIT\MaskExport\FileCollection\LanguageFileCollection;
use CPSIT\MaskExport\FileCollection\PhpFileCollection;
use CPSIT\MaskExport\FileCollection\PlainTextFileCollection;
use CPSIT\MaskExport\FileCollection\SqlFileCollection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ExportController extends ActionController
{
    /**
     * @var array
     */
    protected $aggregateClassNames = [
        ExtensionConfigurationAggregate::class,
        TcaAggregate::class,
        TtContentOverridesAggregate::class,
        NewContentElementWizardAggregate::class,
    ];

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
     */
    public function listAction()
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

        $files = $this->sortFiles($files);

        $this->view->assign('files', $files);
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
            } else {
                return 1;
            }
        });

        return $files;
    }
}
