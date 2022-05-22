<?php

declare(strict_types=1);

namespace IchHabRecht\MaskExport\Aggregate;

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

trait PlainTextFileAwareTrait
{
    /**
     * @var array
     */
    protected $plainTextFiles = [];

    public function getPlainTextFiles(): array
    {
        return $this->plainTextFiles;
    }

    protected function addPlainTextFile(string $fileIdentifier, string $content): void
    {
        $this->plainTextFiles[$fileIdentifier] = $content;
    }

    protected function appendPlainTextFile(string $fileIdentifier, string $content): void
    {
        if (!isset($this->plainTextFiles[$fileIdentifier])) {
            $this->plainTextFiles[$fileIdentifier] = '';
        }

        $this->plainTextFiles[$fileIdentifier] .= $content;
    }
}
