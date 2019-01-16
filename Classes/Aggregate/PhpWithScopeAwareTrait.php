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

trait PhpWithScopeAwareTrait
{

    /**
     * @var array
     */
    protected $phpFilesWithScope = [];

    /**
     * @return array
     */
    public function getPhpFilesWithScope()
    {
        return $this->phpFilesWithScope;
    }

    /**
     * @param string $fileIdentifier
     * @param string $content
     */
    protected function addPhpFileWithScope($fileIdentifier, $content)
    {
        $this->phpFilesWithScope[$fileIdentifier] = $content;
    }


    /**
     * @param string $fileIdentifier
     * @param string $content
     */
    protected function appendPhpFileWithScope($fileIdentifier, $content)
    {
        if (!isset($this->appendPhpFileWithScope[$fileIdentifier])) {
            $this->phpFilesWithScope[$fileIdentifier] = '';
        }

        $this->phpFilesWithScope[$fileIdentifier] .= $content;
    }
}
