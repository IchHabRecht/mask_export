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
use PHPUnit\Framework\MockObject\MockBuilder;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Backend\Module\ModuleProvider;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageStore;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\FluidViewAdapter;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Response;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class AbstractExportControllerTestCase extends FunctionalTestCase
{
    use ProphecyTrait;

    /**
     * @var bool
     */
    protected $runTestInSeparateProcess = true;

    protected array $configurationToUseInTestInstance = [
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

    protected array $coreExtensionsToLoad = [
        'fluid_styled_content',
    ];

    protected array $files = [];

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/mask',
        'typo3conf/ext/mask_export',
    ];

    /**
     * Set up the subject under test
     */
    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['LANG'] = $this->getLanguageService();
        $GLOBALS['BE_USER'] = $this->createMock(BackendUserAuthentication::class);

        $route = new Route('', []);
        $route->setOption('packageName', 'mask_export');

        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withAttribute('extbase', new ExtbaseRequestParameters());
        $serverRequest = $serverRequest->withAttribute('route', $route);
        $serverRequest = $serverRequest->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);

        $request = new Request($serverRequest);
        $request = $request->withControllerObjectName('IchHabRecht\\MaskExport\\Controller\\ExportController');
        $request = $request->withControllerActionName('list');
        $request = $request->withFormat('html');

        $response = null;
        if (class_exists('TYPO3\\CMS\\Extbase\\Mvc\\Response')) {
            $response = new Response();
        }

        $renderingContext = GeneralUtility::makeInstance(RenderingContextFactory::class)->create();
        $renderingContext->setControllerName('Export');
        $renderingContext->setControllerAction('list');

        $view = GeneralUtility::makeInstance(TemplateView::class, $renderingContext);
        $view->setLayoutRootPaths(['EXT:mask_export/Resources/Private/Layouts']);
        $view->setTemplateRootPaths(['EXT:mask_export/Tests/Functional/Fixtures/Templates']);

        $subject = GeneralUtility::makeInstance(ExportController::class);
        $subject->setModuleTemplate($this->buildModuleTemplateWithViewAndRequest($view, $request));
        $response = $subject->processRequest($request, $response) ?: $response;

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

        $languageService = new LanguageService(
            new Locales(),
            new LocalizationFactory(
                new LanguageStore($packageManager),
                $cacheManagerProphecy->reveal()
            ),
            $cacheFrontendProphecy->reveal()
        );
        $languageService->init('default');

        return $languageService;
    }

    protected function buildModuleTemplateWithViewAndRequest(TemplateView $view, Request $request): ModuleTemplate
    {
        return new ModuleTemplate(
            GeneralUtility::makeInstance(PageRenderer::class),
            GeneralUtility::makeInstance(IconFactory::class),
            GeneralUtility::makeInstance(UriBuilder::class),
            GeneralUtility::makeInstance(ModuleProvider::class),
            GeneralUtility::makeInstance(FlashMessageService::class),
            GeneralUtility::makeInstance(ExtensionConfiguration::class),
            new FluidViewAdapter($view),
            $request
        );
    }
}
