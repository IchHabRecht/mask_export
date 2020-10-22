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

use IchHabRecht\MaskExport\FlagResolver\PhpFileFlag\ClosureFunctionFlag;
use IchHabRecht\MaskExport\FlagResolver\PhpFileFlag\DefinedTypo3ModeFlag;
use IchHabRecht\MaskExport\FlagResolver\PhpFileFlag\PhpStartTagFlag;

class PhpFileFlagResolver extends AbstractFlagResolver
{
    protected $flags = [
        ClosureFunctionFlag::class => [],
        DefinedTypo3ModeFlag::class => [
            'after' => [
                ClosureFunctionFlag::class,
            ],
        ],
        PhpStartTagFlag::class => [
            'after' => [
                ClosureFunctionFlag::class,
                DefinedTypo3ModeFlag::class,
            ],
        ],
    ];
}
