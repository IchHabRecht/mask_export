<?php
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
            case '7.6':
                $expectedVersionConstraint = '7.6.0-7.6.99';
                break;
            case '8.7':
                $expectedVersionConstraint = '8.7.0-8.7.99';
                break;
            case '9.0':
                $expectedVersionConstraint = '9.0.0-9.0.99';
                break;
            default:
                throw new \UnexpectedValueException('Missing test configuration in ensureTypo3DependencyInExtEmConf', 1506012559);
        }

        $this->assertContains(
            '\'typo3\' => \'' . $expectedVersionConstraint . '\',',
            $this->files['ext_emconf.php']
        );
    }

    /**
     * @test
     */
    public function ensureMaskConfigurationIsNotChanged()
    {
        $this->assertArrayHasKey('Configuration/Mask/mask.json', $this->files);

        $this->assertStringEqualsFile(
            __DIR__ . '/../../Fixtures/Configuration/mask-default.json',
            $this->files['Configuration/Mask/mask.json']
        );
    }
}
