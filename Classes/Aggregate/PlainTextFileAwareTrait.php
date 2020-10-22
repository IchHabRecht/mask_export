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

    /**
     * @return array
     */
    public function getPlainTextFiles()
    {
        return $this->plainTextFiles;
    }

    /**
     * @param string $fileIdentifier
     * @param string $content
     */
    protected function addPlainTextFile($fileIdentifier, $content)
    {
        $this->plainTextFiles[$fileIdentifier] = $content;
    }

    /**
     * @param string $fileIdentifier
     * @param string $content
     */
    protected function appendPlainTextFile($fileIdentifier, $content)
    {
        if (!isset($this->plainTextFiles[$fileIdentifier])) {
            $this->plainTextFiles[$fileIdentifier] = '';
        }

        $this->plainTextFiles[$fileIdentifier] .= $content;
    }
}
