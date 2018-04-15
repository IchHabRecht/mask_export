<?php
namespace IchHabRecht\MaskExport\Tests\Functional\Controller\NewContentElementWizard;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Nicole Cordes <typo3@cordes.co>
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

use IchHabRecht\MaskExport\Tests\Functional\Controller\AbstractExportControllerTestCase;

class ExportControllerTest extends AbstractExportControllerTestCase
{
    /**
     * @test
     */
    public function hiddenContentElementsIgnoredInExport()
    {
        $this->assertArrayHasKey('Configuration/PageTSconfig/NewContentElementWizard.ts', $this->files);
        $this->assertNotContains(
            'CType = maskexampleexport_hidden-element',
            $this->files['Configuration/PageTSconfig/NewContentElementWizard.ts']
        );
    }
}
