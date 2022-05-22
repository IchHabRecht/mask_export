<?php

declare(strict_types=1);

namespace IchHabRecht\MaskExport\FlagResolver\PhpFileFlag;

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

use IchHabRecht\MaskExport\Aggregate\PhpAwareInterface;
use IchHabRecht\MaskExport\FlagResolver\FlagInterface;

class DefinedTypo3ModeFlag implements FlagInterface
{
    public function isEnabled(int $flags): bool
    {
        return ($flags & PhpAwareInterface::PHPFILE_DEFINED_TYPO3_MODE) === PhpAwareInterface::PHPFILE_DEFINED_TYPO3_MODE;
    }

    public function execute(string $content): string
    {
        return <<<EOS
defined('TYPO3_MODE') || die();

{$content}

EOS;
    }
}
