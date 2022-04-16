<?php

declare(strict_types=1);

namespace IchHabRecht\MaskExport\Tests\Functional\Controller\TypoScript;

/*
 * This file is part of the TYPO3 extension mask_export.
 *
 * (c) 2017 Nicole Cordes <typo3@cordes.co>, CPS-IT GmbH
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

require_once __DIR__ . '/../AbstractExportControllerTestCase.php';

use IchHabRecht\MaskExport\Tests\Functional\Controller\AbstractExportControllerTestCase;

class ExportControllerTest extends AbstractExportControllerTestCase
{
    /**
     * @test
     */
    public function checkFluidTemplatePathsInTypoScript()
    {
        $this->assertArrayHasKey('Configuration/TypoScript/setup.typoscript', $this->files);

        // Fetch all templateRootPaths configurations
        $templatePaths = [];
        preg_match_all(
            '#templateRootPaths\\.0 = EXT:mask_example_export/(.+)$#m',
            $this->files['Configuration/TypoScript/setup.typoscript'],
            $templatePaths,
            PREG_SET_ORDER
        );

        $this->assertNotEmpty($templatePaths);

        // Fetch all templateName configurations
        $templateNames = [];
        preg_match_all(
            '#templateName = (.+)$#m',
            $this->files['Configuration/TypoScript/setup.typoscript'],
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
        $this->assertArrayHasKey('Configuration/TypoScript/constants.typoscript', $this->files);
        $this->assertArrayHasKey('Configuration/TypoScript/setup.typoscript', $this->files);

        $constants = [];
        preg_match_all(
            '#{\$([^}]+)}#',
            $this->files['Configuration/TypoScript/setup.typoscript'],
            $constants
        );

        $this->assertNotEmpty($constants);

        foreach (array_unique($constants[1]) as $constant) {
            $this->assertStringContainsString($constant, $this->files['Configuration/TypoScript/constants.typoscript']);
        }
    }

    /**
     * @test
     */
    public function ensureDataProcessingWhereClauseIsBuiltCompletely()
    {
        $this->assertArrayHasKey('Configuration/TypoScript/setup.typoscript', $this->files);
        $this->assertStringContainsString(
            'where = tx_maskexampleexport_related_content_parent=###uid### AND deleted=0 AND hidden=0 AND colPos=###colPos### AND CType IN (###CType1###, ###CType2###, ###CType3###)',
            $this->files['Configuration/TypoScript/setup.typoscript']
        );
        $this->assertStringContainsString(
            'where = tx_maskexampleexport_morecontent_parent=###uid### AND deleted=0 AND hidden=0 AND colPos=###colPos###',
            $this->files['Configuration/TypoScript/setup.typoscript']
        );
    }
}
