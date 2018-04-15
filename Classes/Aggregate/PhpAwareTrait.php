<?php
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

trait PhpAwareTrait
{
    /**
     * @var array
     */
    protected $phpFiles = [];

    /**
     * @return array
     */
    public function getPhpFiles()
    {
        return $this->phpFiles;
    }

    /**
     * @param string $fileIdentifier
     * @param string $content
     */
    protected function addPhpFile($fileIdentifier, $content)
    {
        $this->phpFiles[$fileIdentifier] = $content;
    }

    /**
     * @param string $fileIdentifier
     * @param string $content
     */
    protected function appendPhpFile($fileIdentifier, $content)
    {
        if (!isset($this->phpFiles[$fileIdentifier])) {
            $this->phpFiles[$fileIdentifier] = '';
        }

        $this->phpFiles[$fileIdentifier] .= $content;
    }
}
