<?php

declare(strict_types=1);

namespace IchHabRecht\MaskExport\Tests\Functional\Controller\Configuration;

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
    public function ensureTypo3DependencyInExtEmConf()
    {
        $this->assertArrayHasKey('ext_emconf.php', $this->files);

        switch (TYPO3_branch) {
            case '9.5':
                $expectedVersionConstraint = '9.5.0-9.5.99';
                break;
            case '10.4':
                $expectedVersionConstraint = '10.4.0-10.4.99';
                break;
            case '11.5':
                $expectedVersionConstraint = '11.5.0-11.5.99';
                break;
            default:
                throw new \UnexpectedValueException('Missing test configuration for "' . TYPO3_branch . '" in ensureTypo3DependencyInExtEmConf', 1506012559);
        }

        $this->assertStringContainsString(
            '\'typo3\' => \'' . $expectedVersionConstraint . '\',',
            $this->files['ext_emconf.php']
        );
    }

    /**
     * @test
     */
    public function ensureExtensionNameIsReplacedInComposerJson()
    {
        $this->assertArrayHasKey('composer.json', $this->files);

        switch (TYPO3_branch) {
            case '9.5':
                $expectedVersionConstraint = '^9.5';
                break;
            case '10.4':
                $expectedVersionConstraint = '^10.4';
                break;
            case '11.5':
                $expectedVersionConstraint = '^11.5';
                break;
            default:
                throw new \UnexpectedValueException('Missing test configuration for "' . TYPO3_branch . '" in ensureTypo3DependencyInExtEmConf', 1526087286);
        }

        $this->assertStringContainsString(
            '"name": "ichhabrecht/mask-example-export",',
            $this->files['composer.json']
        );

        $this->assertStringContainsString(
            '"typo3/cms-core": "' . $expectedVersionConstraint . '",',
            $this->files['composer.json']
        );

        $this->assertStringContainsString(
            '"typo3-ter/mask-example-export": "self.version"',
            $this->files['composer.json']
        );

        $this->assertStringContainsString(
            '"IchHabRecht\\\\MaskExampleExport\\\\": "Classes/"',
            $this->files['composer.json']
        );

        $this->assertStringContainsString(
            '"extension-key": "mask_example_export"',
            $this->files['composer.json']
        );
    }

    /**
     * @test
     */
    public function ensureMaskConfigurationIsNotChanged()
    {
        $this->assertArrayHasKey('Configuration/Mask/mask.json', $this->files);

        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixtures/Configuration/mask-default.json',
            $this->files['Configuration/Mask/mask.json']
        );
    }

    /*
     * @test
     */
    public function ensureMaskConfigurationIsNotExported()
    {
        $this->assertArrayHasKey('ext_tables.php', $this->files);
        $this->assertArrayHasKey('ext_tables.sql', $this->files);

        $this->assertStringNotContainsString(
            'maskexampleexport_export',
            $this->files['ext_tables.php']
        );
        $this->assertStringNotContainsString(
            'maskexampleexport_export',
            $this->files['ext_tables.sql']
        );
    }
}
