<?php
namespace CPSIT\MaskExport\Tests\Functional\Controller\TypoScript;

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

class ExportControllerTest extends AbstractExportControllerTestCase
{
    /**
     * @test
     */
    public function checkFluidTemplatePathsInTypoScript()
    {
        $this->assertArrayHasKey('Configuration/TypoScript/setup.ts', $this->files);

        // Fetch all templateRootPaths configurations
        $templatePaths = [];
        preg_match_all(
            '#templateRootPaths\\.0 = EXT:mask_example_export/(.+)$#m',
            $this->files['Configuration/TypoScript/setup.ts'],
            $templatePaths,
            PREG_SET_ORDER
        );

        $this->assertNotEmpty($templatePaths);

        // Fetch all templateName configurations
        $templateNames = [];
        preg_match_all(
            '#templateName = (.+)$#m',
            $this->files['Configuration/TypoScript/setup.ts'],
            $templateNames,
            PREG_SET_ORDER
        );

        $this->assertCount(count($templatePaths), $templateNames);

        // Combine templateRootPaths and templateName and check for file
        foreach ($templatePaths as $key => $templatePathArray) {
            $templatePath = $templatePathArray[1] . $templateNames[$key][1] . '.html';

            $this->assertArrayHasKey($templatePath, $this->files);
        }
    }

    /**
     * @test
     */
    public function ensureConstantsAreInitializedInTypoScript()
    {
        $this->assertArrayHasKey('Configuration/TypoScript/constants.ts', $this->files);
        $this->assertArrayHasKey('Configuration/TypoScript/setup.ts', $this->files);

        $constants = [];
        preg_match_all(
            '#{\$([^}]+)}#',
            $this->files['Configuration/TypoScript/setup.ts'],
            $constants
        );

        $this->assertNotEmpty($constants);

        foreach (array_unique($constants[1]) as $constant) {
            $this->assertContains($constant, $this->files['Configuration/TypoScript/constants.ts']);
        }
    }
}
