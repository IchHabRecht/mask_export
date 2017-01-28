<?php
namespace CPSIT\MaskExport\Tests\Functional\Controller\BackendPreview;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Nicole Cordes <typo3@cordes.co>, CPS-IT GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once __DIR__ . '/../AbstractExportControllerTestCase.php';

use CPSIT\MaskExport\Tests\Functional\Controller\AbstractExportControllerTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExportControllerTest extends AbstractExportControllerTestCase
{
    /**
     * @test
     */
    public function checkFluidTemplatePathInBackendPreview()
    {
        $this->assertArrayHasKey('Classes/Hooks/PageLayoutViewDrawItem.php', $this->files);

        // Get templatePath from file
        $matches = [];
        preg_match(
            '#\\$templatePath = GeneralUtility::getFileAbsFileName\\(([^)]+)\\);#',
            $this->files['Classes/Hooks/PageLayoutViewDrawItem.php'],
            $matches
        );

        $this->assertCount(2, $matches);

        $templateRootPath = str_replace(
            [
                '\'',
                ' . ',
                '$this->rootPath',
            ],
            [
                '',
                '',
                'Resources/Private/Backend/',
            ],
            $matches[1]
        );

        // Fetch supported content types from file
        $matches = [];
        preg_match(
            '#protected \\$supportedContentTypes = ([^;]+);#',
            $this->files['Classes/Hooks/PageLayoutViewDrawItem.php'],
            $matches
        );

        $this->assertCount(2, $matches);

        // Get templateName from content type and check for file
        $supportedContentTypes = eval('return ' . $matches[1] . ';');
        foreach ($supportedContentTypes as $contentType => $_) {
            $contentType = explode('_', $contentType, 2);
            $templateKey = GeneralUtility::underscoredToUpperCamelCase($contentType[1]);
            $templatePath = str_replace('$templateKey', $templateKey, $templateRootPath);

            $this->assertArrayHasKey($templatePath, $this->files);
        }
    }

    /**
     * @test
     */
    public function validateFluidTemplateForSelectboxFields()
    {
        $this->assertArrayHasKey('Resources/Private/Backend/Templates/Content/Simple-element.html', $this->files);
        $this->assertContains(
            '{processedRow.tx_maskexampleexport_simpleselectboxsingle.0} (raw={row.tx_maskexampleexport_simpleselectboxsingle})<br>',
            $this->files['Resources/Private/Backend/Templates/Content/Simple-element.html']
        );
        $this->assertContains(
            '<f:for each="{processedRow.tx_maskexampleexport_simpleselectboxmulti}" as="item">',
            $this->files['Resources/Private/Backend/Templates/Content/Simple-element.html']
        );
    }
}
