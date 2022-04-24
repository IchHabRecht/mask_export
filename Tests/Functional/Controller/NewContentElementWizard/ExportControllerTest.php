<?php

declare(strict_types=1);

namespace IchHabRecht\MaskExport\Tests\Functional\Controller\NewContentElementWizard;

/*
 * This file is part of the TYPO3 extension mask_export.
 *
 * (c) 2018 Nicole Cordes <typo3@cordes.co>, CPS-IT GmbH
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
    public function hiddenContentElementsIgnoredInExport()
    {
        $this->assertArrayHasKey('Configuration/TsConfig/Page/NewContentElementWizard.tsconfig', $this->files);
        $this->assertStringNotContainsString(
            'CType = maskexampleexport_hidden-element',
            $this->files['Configuration/TsConfig/Page/NewContentElementWizard.tsconfig']
        );
    }
}
