<?php
namespace IchHabRecht\MaskExport\Tests\Functional\Controller\BackendPreview;

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

use IchHabRecht\MaskExport\Tests\Functional\Controller\AbstractExportControllerTestCase;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Lang\LanguageService;

class ExportControllerTest extends AbstractExportControllerTestCase
{
    /**
     * @test
     */
    public function checkFluidTemplatePathInBackendPreview()
    {
        $this->assertArrayHasKey('Configuration/PageTSconfig/BackendPreview.ts', $this->files);

        // Get templatePaths from file
        $templatePaths = [];
        preg_match_all(
            '#mod\.web_layout\.tt_content\.preview\.([^ ]+) = [^:]+:[^/]+/(.*)#',
            $this->files['Configuration/PageTSconfig/BackendPreview.ts'],
            $templatePaths,
            PREG_SET_ORDER
        );

        $this->assertNotEmpty($templatePaths);

        // Fetch supported content types from file
        $matches = [];
        preg_match(
            '#protected \\$supportedContentTypes = ([^;]+);#',
            $this->files['Classes/Hooks/PageLayoutViewDrawItem.php'],
            $matches
        );

        $this->assertCount(2, $matches);

        $supportedContentTypes = eval('return ' . $matches[1] . ';');

        $this->assertSame(count($templatePaths), count($supportedContentTypes));

        foreach ($templatePaths as $contentType) {
            $this->assertCount(3, $contentType);
            $this->assertContains($contentType[1], $supportedContentTypes);
            $this->assertArrayHasKey($contentType[2], $this->files);
        }
    }

    /**
     * @test
     */
    public function validateProcessedRowDataFromPageLayoutViewDrawItem()
    {
        $className = 'MASKEXAMPLEEXPORT\MaskExampleExport\\Hooks\\PageLayoutViewDrawItem';
        $this->installExtension();

        $this->assertTrue(class_exists($className));

        // Load database fixtures
        $fixturePath = ORIGINAL_ROOT . 'typo3conf/ext/mask_export/Tests/Functional/Fixtures/Database/';
        $this->importDataSet($fixturePath . 'pages.xml');
        $this->importDataSet($fixturePath . 'tt_content.xml');
        $this->importDataSet($fixturePath . 'sys_file.xml');

        // Load backend user and LanguageService for FormEngine
        $this->setUpBackendUserFromFixture(1);
        $languageService = new LanguageService();
        $languageService->init('default');
        $GLOBALS['LANG'] = $languageService;

        // Get StandaloneView mock
        /** @var \PHPUnit_Framework_MockObject_MockObject|StandaloneView $viewMock */
        $viewMock = $this->getMockBuilder(StandaloneView::class)
            ->setMethods(['render', 'setLayoutRootPaths', 'setPartialRootPaths', 'setTemplatePathAndFilename'])
            ->getMock();
        $viewMock->expects($this->once())->method('render');
        GeneralUtility::addInstance(StandaloneView::class, $viewMock);

        // Call preProcess function on PageLayoutViewDrawItem
        $pageLayoutView = new PageLayoutView();
        $drawItem = true;
        $headerContent = '';
        $itemContent = '';
        $row = BackendUtility::getRecord('tt_content', 1);
        /** @var \PHPUnit_Framework_MockObject_MockObject|PageLayoutViewDrawItemHookInterface $subject */
        $subject = $this->getMockBuilder($className)
            ->setMethods(['getTemplatePath'])
            ->getMock();
        $subject->expects($this->once())->method('getTemplatePath')->willReturn(PATH_site . 'typo3conf/ext/mask_export/Resources/Private/Backend/Templates/Export/List.html');
        $subject->preProcess($pageLayoutView, $drawItem, $headerContent, $itemContent, $row);

        // Get variable container
        $closure = \Closure::bind(function () use ($viewMock) {
            return $viewMock->baseRenderingContext;
        }, null, StandaloneView::class);
        $renderingContext = $closure();
        if (method_exists($renderingContext, 'getVariableProvider')) {
            $variables = $renderingContext->getVariableProvider();
        } else {
            $variables = $renderingContext->getTemplateVariableContainer();
        }

        $expectedArray = [
            'tx_maskexampleexport_related_content' => [
                0 => [
                    'assets' => [
                        0 => [
                            'uid' => 1,
                            'pid' => 1,
                            'uid_foreign' => 2,
                            'tablenames' => 'tt_content',
                            'fieldname' => 'assets',
                            'table_local' => 'sys_file',
                        ],
                    ],
                ],
            ],
        ];
        $processedRow = $variables->get('processedRow');

        $this->assertArraySubset($expectedArray, $processedRow);

        if (is_array($processedRow['tx_maskexampleexport_related_content'][0]['assets'][0]['uid_local'])) {
            $this->assertArraySubset(
                [
                    0 => [
                        'table' => 'sys_file',
                        'uid' => 1,
                    ],
                ],
                $processedRow['tx_maskexampleexport_related_content'][0]['assets'][0]['uid_local']
            );
        } else {
            $this->assertSame(
                'sys_file_1|ce_nested-content-elements.png',
                $processedRow['tx_maskexampleexport_related_content'][0]['assets'][0]['uid_local']
            );
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
