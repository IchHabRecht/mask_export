<?php

declare(strict_types=1);

namespace IchHabRecht\MaskExport\Tests\Functional\Controller\BackendPreview;

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

use GuzzleHttp\Psr7\ServerRequest;
use IchHabRecht\MaskExport\Tests\Functional\Controller\AbstractExportControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Fluid\View\StandaloneView;

class ExportControllerTest extends AbstractExportControllerTestCase
{
    /**
     * @test
     */
    public function checkFluidTemplatePathInBackendPreview()
    {
        $this->assertArrayHasKey('Configuration/TsConfig/Page/BackendPreview.tsconfig', $this->files);

        // Get templatePaths from file
        $templatePath = [];
        preg_match(
            '#mod\.web_layout\.tt_content\.preview\.([^.]+)\.templateRootPath = [^:]+:[^/]+/(.*)#',
            $this->files['Configuration/TsConfig/Page/BackendPreview.tsconfig'],
            $templatePath
        );

        $this->assertNotEmpty($templatePath);

        // Fetch supported content types from file
        $matches = [];
        preg_match(
            '#protected \\$supportedContentTypes = ([^;]+);#',
            $this->files['Classes/Hooks/PageLayoutViewDrawItem.php'],
            $matches
        );

        $this->assertCount(2, $matches);

        $supportedContentTypes = eval('return ' . $matches[1] . ';');

        foreach ($supportedContentTypes as $contentType) {
            $this->assertArrayHasKey($templatePath[2] . ucfirst($contentType) . '.html', $this->files);
        }
    }

    /**
     * @test
     */
    public function validateProcessedRowDataFromPageLayoutViewDrawItem()
    {
        $className = 'IchHabRecht\\MaskExampleExport\\Hooks\\PageLayoutViewDrawItem';
        $this->installExtension();

        $this->assertTrue(class_exists($className));

        // Load database fixtures
        $fixturePath = ORIGINAL_ROOT . 'typo3conf/ext/mask_export/Tests/Functional/Fixtures/Database/';
        $this->importDataSet($fixturePath . 'pages.xml');
        $this->importDataSet($fixturePath . 'tt_content.xml');
        $this->importDataSet($fixturePath . 'sys_file.xml');

        // Load backend user and LanguageService for FormEngine
        $this->setUpBackendUserFromFixture(1);
        $GLOBALS['LANG'] = $this->getLanguageService();
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('GET', '/'))
            ->withAttribute('applicationType', \TYPO3\CMS\Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_BE);

        // Get StandaloneView mock
        /** @var MockObject|StandaloneView $viewMock */
        $viewMock = $this->getMockBuilder(StandaloneView::class)
            ->setMethods(['render'])
            ->getMock();
        $viewMock->expects($this->once())->method('render');

        // Call preProcess function on PageLayoutViewDrawItem
        $eventDispatcher = interface_exists('Psr\\EventDispatcher\\EventDispatcherInterface')
            ? EventDispatcherInterface::class
            : Dispatcher::class;
        $eventDispatcher = $this->prophesize($eventDispatcher);
        $eventDispatcher->dispatch(Argument::cetera())->willReturnArgument(0);
        $pageLayoutView = new PageLayoutView($eventDispatcher->reveal());
        $drawItem = true;
        $headerContent = '';
        $itemContent = '';
        $row = BackendUtility::getRecord('tt_content', 1);
        /** @var MockObject|PageLayoutViewDrawItemHookInterface $subject */
        $subject = new $className($viewMock);
        $subject->preProcess($pageLayoutView, $drawItem, $headerContent, $itemContent, $row);

        // Get variable container
        $closure = \Closure::bind(static function () use ($viewMock) {
            return $viewMock->baseRenderingContext;
        }, null, StandaloneView::class);
        $renderingContext = $closure();
        $variables = $renderingContext->getVariableProvider();

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
        $this->assertStringContainsString(
            '{processedRow.tx_maskexampleexport_simpleselectboxsingle.0} (raw={row.tx_maskexampleexport_simpleselectboxsingle})<br>',
            $this->files['Resources/Private/Backend/Templates/Content/Simple-element.html']
        );
        $this->assertStringContainsString(
            '<f:for each="{processedRow.tx_maskexampleexport_simpleselectboxmulti}" as="item">',
            $this->files['Resources/Private/Backend/Templates/Content/Simple-element.html']
        );
    }
}
