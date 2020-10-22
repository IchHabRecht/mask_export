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

use IchHabRecht\MaskExport\Aggregate\PhpAwareInterface;
use IchHabRecht\MaskExport\FlagResolver\PhpFileFlagResolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PhpFileCollection extends AbstractFileCollection
{
    /**
     * @var PhpFileFlagResolver
     */
    protected $phpFileFlagResolver;

    public function __construct(array $aggregateCollection, PhpFileFlagResolver $phpFileFlagResolver = null)
    {
        parent::__construct($aggregateCollection);

        $this->phpFileFlagResolver = $phpFileFlagResolver ?: GeneralUtility::makeInstance(PhpFileFlagResolver::class);
    }

    /**
     * @return array
     */
    protected function processAggregateCollection()
    {
        $fileInformation = [];
        foreach ($this->aggregateCollection as $aggregate) {
            if (!$aggregate instanceof PhpAwareInterface) {
                continue;
            }

            $aggregateFiles = $aggregate->getPhpFiles();
            foreach ($aggregateFiles as $file => $information) {
                if (!isset($fileInformation[$file])) {
                    $fileInformation[$file] = [
                        'content' => '',
                        'flags' => 0,
                    ];
                }

                $fileInformation[$file]['content'] .= $information['content'];
                $fileInformation[$file]['flags'] |= $information['flags'];
            }
        }

        return $this->phpFileFlagResolver->resolveFlags($fileInformation);
    }
}
