<?php

declare(strict_types=1);

namespace IchHabRecht\MaskExport\Tests\Functional\Controller;

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

use IchHabRecht\MaskExport\Controller\ExportController;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use PHPUnit\Framework\MockObject\MockBuilder;
use Prophecy\Argument;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageStore;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Response;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\TemplateView;

abstract class AbstractExportControllerTestCase extends FunctionalTestCase
{
    /**
     * @var bool
     */
    protected $runTestInSeparateProcess = true;

    /**
     * @var array
     */
    protected $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'mask' => [
                'json' => 'typo3conf/ext/mask_export/Tests/Functional/Fixtures/Configuration/mask-default.json',
                'content' => 'typo3conf/ext/mask_export/Tests/Functional/Fixtures/Templates/Content/',
                'partials' => 'typo3conf/ext/mask_export/Tests/Functional/Fixtures/Templates/Partials/',
                'layouts' => 'typo3conf/ext/mask_export/Tests/Functional/Fixtures/Templates/Layouts/',
                'preview' => 'typo3conf/ext/mask_export/Tests/Functional/Fixtures/Templates/Preview/',
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $coreExtensionsToLoad = [
        'fluid_styled_content',
    ];

    /**
     * @var array
     */
    protected $files = [];

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/mask',
        'typo3conf/ext/mask_export',
    ];

    /**
     * Set up the subject under test
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $request = new Request('IchHabRecht\\MaskExport\\Controller\\ExportController');
        $request->setControllerObjectName('IchHabRecht\\MaskExport\\Controller\\ExportController');
        $request->setControllerActionName('list');
        $request->setFormat('html');
        if (method_exists($request, 'setControllerAliasToClassNameMapping')) {
            $request->setControllerAliasToClassNameMapping(['Export' => ExportController::class]);
        }

        $response = null;
        if (class_exists('TYPO3\\CMS\\Extbase\\Mvc\\Response')) {
            $response = new Response();
        }

        $view = $objectManager->get(TemplateView::class);
        $view->setLayoutRootPaths(['EXT:mask_export/Resources/Private/Layouts']);
        $view->setTemplateRootPaths(['EXT:mask_export/Tests/Functional/Fixtures/Templates']);
        if (method_exists(GeneralUtility::class, 'getContainer')) {
            $container = GeneralUtility::getContainer();
            $container->set(TemplateView::class, $view);
        } else {
            GeneralUtility::addInstance(TemplateView::class, $view);
        }

        $subject = $objectManager->get(ExportController::class);
        $response = $subject->processRequest($request, $response) ?: $response;

        $closure = \Closure::bind(function () use ($view) {
            return $view->baseRenderingContext;
        }, null, TemplateView::class);
        $renderingContext = $closure();
        $variables = $renderingContext->getVariableProvider();
        $this->files = $variables->get('files');
    }

    /**
     * As the export extension cannot be installed over extensionmanager APi, this function loads data manually
     */
    protected function installExtension()
    {
        // Require PHP files and take care of TCA configuration
        $_EXTKEY = 'mask_example_export';
        $_EXTCONF = '';
        foreach ($this->files as $file => $content) {
            if (!preg_match('/\.php$/', $file)) {
                continue;
            }

            if (preg_match('/Configuration\/TCA\/[^.\/]+\.php/', $file)) {
                $tableName = basename($file, '.php');
                $tableTca = eval('?>' . $content);
                $GLOBALS['TCA'][$tableName] = $tableTca;
            } else {
                eval('?>' . $content);
            }
        }

        // Load ext_tables.sql
        if (!empty($this->files['ext_tables.sql'])) {
            $sqlReader = GeneralUtility::makeInstance(SqlReader::class);
            $statements = $sqlReader->getCreateTableStatementArray($this->files['ext_tables.sql']);
            if (count($statements) !== 0) {
                $schemaMigrationService = GeneralUtility::makeInstance(SchemaMigrator::class);
                $schemaMigrationService->install($statements);
            }
        }

        $this->assertIsArray($GLOBALS['TCA']['tt_content']);
        $this->assertIsArray($GLOBALS['TCA']['tx_maskexampleexport_additionalcontent']);
    }

    /**
     * @param bool $mockBuilder
     * @return MockBuilder|LanguageService
     */
    protected function getLanguageService(bool $mockBuilder = false)
    {
        $cacheManagerProphecy = $this->prophesize(CacheManager::class);
        $cacheFrontendProphecy = $this->prophesize(FrontendInterface::class);
        $cacheFrontendProphecy->get(Argument::cetera())->willReturn(false);
        $cacheFrontendProphecy->set(Argument::cetera())->willReturn(null);
        $cacheManagerProphecy->getCache('l10n')->willReturn($cacheFrontendProphecy->reveal());
        $packageManager = GeneralUtility::makeInstance(PackageManager::class);

        if ($mockBuilder) {
            return $this->getMockBuilder(LanguageService::class)
                ->setConstructorArgs(
                    [
                        new Locales(),
                        new LocalizationFactory(new LanguageStore($packageManager), $cacheManagerProphecy->reveal()),
                        $cacheFrontendProphecy->reveal(),
                    ]
                );
        }

        $languageService = new LanguageService(new Locales(), new LocalizationFactory(new LanguageStore($packageManager), $cacheManagerProphecy->reveal()), $cacheFrontendProphecy->reveal());
        $languageService->init('default');

        return $languageService;
    }
}
