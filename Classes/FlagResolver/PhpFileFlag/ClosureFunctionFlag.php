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

class ClosureFunctionFlag implements FlagInterface
{
    /**
     * @param int $flags
     * @return bool
     */
    public function isEnabled($flags)
    {
        return ($flags & PhpAwareInterface::PHPFILE_CLOSURE_FUNCTION) === PhpAwareInterface::PHPFILE_CLOSURE_FUNCTION;
    }

    public function execute($content)
    {
        return <<<EOS
call_user_func(static function () {

{$content}
});

EOS;
    }
}
