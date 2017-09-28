<?php
namespace CPSIT\MaskExport\Tests\Functional\Controller\Configuration;

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
