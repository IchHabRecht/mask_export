<?php

declare(strict_types=1);

namespace IchHabRecht\MaskExport\FlagResolver;

/*
 * This file is part of the TYPO3 extension mask_export.
 *
 * (c) 2019 Nicole Cordes <typo3@cordes.co>, biz-design
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractFlagResolver implements FlagResolverInterface
{
    /**
     * @var array
     */
    protected $flags = [];

    public function __construct(DependencyOrderingService $dependencyOrderingService = null)
    {
        if ($dependencyOrderingService === null) {
            $dependencyOrderingService = GeneralUtility::makeInstance(DependencyOrderingService::class);
        }

        $this->flags = $dependencyOrderingService->orderByDependencies($this->flags);
    }

    /**
     * @param array $fileInformation
     * @return array
     */
    public function resolveFlags(array $fileInformation)
    {
        $files = array_map(
            static function ($information) {
                return $information['content'];
            },
            $fileInformation
        );

        foreach ($this->flags as $flagClassName => $_) {
            if (!class_exists($flagClassName)) {
                continue;
            }

            $flag = GeneralUtility::makeInstance($flagClassName);

            foreach ($fileInformation as $file => $information) {
                if ($flag->isEnabled($information['flags'])) {
                    $files[$file] = $flag->execute($files[$file]);
                }
            }
        }

        return $files;
    }
}
