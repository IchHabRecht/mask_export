<?php
namespace IchHabRecht\MaskExport\Tests\Functional\Controller\Ressources;

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

class IconReferenceTest extends AbstractExportControllerTestCase
{
    protected function setUp()
    {
        if (!defined('ORIGINAL_ROOT')) {
            $this->markTestSkipped('Functional tests must be called through phpunit on CLI');
        }

        $this->setUpWithExtensionConfiguration(
            [
                'mask' => [
                    'preview' => 'EXT:mask_export/Tests/Functional/Fixtures/Templates/Preview/',
                ],
            ]
        );
    }

    /**
     * @test
     */
    public function contentElementIconsAreUsedAsReferences()
    {
        $this->assertArrayHasKey('Resources/Public/Icons/Content/default-extension-icon.png', $this->files);

        $this->assertArrayNotHasKey('Resources/Public/Icons/Content/nested-content-elements.png', $this->files);
        $this->assertArrayNotHasKey('Resources/Public/Icons/Content/simple-element.png', $this->files);

        $this->assertContains(
            '\'source\' => \'EXT:mask_example_exportexampleexport_export/Tests/Functional/Fixtures/Templates/Preview/ce_nested-content-elements.png\'',
            $this->files['ext_localconf.php']
        );
        $this->assertContains(
            '\'source\' => \'EXT:mask_example_exportexampleexport_export/Tests/Functional/Fixtures/Templates/Preview/simple-element.png\'',
            $this->files['ext_localconf.php']
        );
    }
}
