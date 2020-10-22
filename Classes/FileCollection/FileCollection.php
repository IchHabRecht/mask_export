<?php

declare(strict_types=1);

namespace IchHabRecht\MaskExport\FileCollection;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileCollection
{
    /**
     * @var array
     */
    private $aggregateCollection;

    /**
     * @var array
     */
    private $fileCollectionClassNames;

    /**
     * @var array
     */
    protected $files;

    public function __construct(array $fileCollectionClassNames, array $aggregateCollection)
    {
        $this->fileCollectionClassNames = $fileCollectionClassNames;
        $this->aggregateCollection = $aggregateCollection;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        if (null === $this->files) {
            $this->initializeFiles();
        }

        return $this->files;
    }

    /**
     * Initializes aggregate objects
     */
    protected function initializeFiles()
    {
        $this->files = [];
        foreach ($this->fileCollectionClassNames as $className) {
            if (class_exists($className)) {
                $fileCollection = GeneralUtility::makeInstance($className, $this->aggregateCollection);
                $this->files = array_merge($this->files, $fileCollection->getFiles());
            }
        }
    }
}
