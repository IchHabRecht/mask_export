<?php

declare(strict_types=1);

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
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExportControllerTest extends AbstractExportControllerTestCase
{
    /**
     * @test
     */
    public function ensureContentElementIconFromPreviewFolderInExport()
    {
        $this->assertArrayHasKey('Resources/Public/Icons/Content/simple-element.png', $this->files);

        $this->assertStringEqualsFile(
            __DIR__ . '/../../Fixtures/Templates/Preview/simple-element.png',
            $this->files['Resources/Public/Icons/Content/simple-element.png']
        );
    }

    /**
     * @test
     */
    public function ensureContentElementIconFromFallbackPathInExport()
    {
        $this->assertArrayHasKey('Resources/Public/Icons/Content/nested-content-elements.png', $this->files);

        $this->assertStringEqualsFile(
            __DIR__ . '/../../Fixtures/Templates/Preview/ce_nested-content-elements.png',
            $this->files['Resources/Public/Icons/Content/nested-content-elements.png']
        );
    }

    /**
     * @test
     */
    public function extensionIconIsUsedAsDefaultContentElementIcon()
    {
        $this->assertArrayHasKey('Resources/Public/Icons/Content/default-extension-icon.svg', $this->files);

        $this->assertStringEqualsFile(
            __DIR__ . '/../../../../Resources/Public/Icons/Extension.svg',
            $this->files['Resources/Public/Icons/Content/default-extension-icon.svg']
        );
    }

    /**
     * @test
     */
    public function contentElementsHaveRegisteredIconIdentifier()
    {
        $maskConfiguration = json_decode(file_get_contents(__DIR__ . '/../../Fixtures/Configuration/mask-default.json'), true);
        $this->assertNotEmpty($maskConfiguration['tt_content']['elements']);

        $this->installExtension();

        $typeIconConfiguration = $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'];
        $this->assertNotEmpty($typeIconConfiguration);

        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);

        foreach ($maskConfiguration['tt_content']['elements'] as $key => $element) {
            if (!empty($element['hidden'])) {
                continue;
            }

            $iconIdentifier = 'tx_maskexampleexport_' . $key;

            $this->assertTrue($iconRegistry->isRegistered($iconIdentifier));
            $this->assertSame($iconIdentifier, $typeIconConfiguration['maskexampleexport_' . $key]);
        }
    }

    /**
     * @test
     */
    public function itemsLabelsAreMovedToLocallangFile()
    {
        $this->assertArrayHasKey('Configuration/TCA/Overrides/tt_content.php', $this->files);
        $this->assertArrayHasKey('Resources/Private/Language/locallang_db.xlf', $this->files);

        $labels = [];
        preg_match_all(
            '#\'LLL:EXT:([^\']+\.I\.[^\']+)\',#',
            $this->files['Configuration/TCA/Overrides/tt_content.php'],
            $labels,
            PREG_SET_ORDER
        );

        $this->assertNotEmpty($labels);

        foreach ($labels as $label) {
            $id = array_pop(explode(':', $label[1]));

            $this->assertStringContainsString(
                '<trans-unit id="' . $id . '">',
                $this->files['Resources/Private/Language/locallang_db.xlf']
            );
        }
    }
}
