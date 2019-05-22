<?php
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extensionmanager\Utility\InstallUtility;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\CMS\Install\Service\SqlSchemaMigrationService;

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
        'EXT' => [
            'extConf' => [
                'mask' => 'a:2:{s:4:"json";s:83:"typo3conf/ext/mask_export/Tests/Functional/Fixtures/Configuration/mask-default.json";s:7:"preview";s:70:"typo3conf/ext/mask_export/Tests/Functional/Fixtures/Templates/Preview/";}',
            ],
        ],
        'EXTENSIONS' => [
            'mask' => [
                'json' => 'typo3conf/ext/mask_export/Tests/Functional/Fixtures/Configuration/mask-default.json',
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
    protected function setUp()
    {
        parent::setUp();

        $objectManager = new ObjectManager();

        $viewMock = $objectManager->get(TemplateView::class);
        $viewMock->setLayoutRootPaths(['EXT:mask_export/Resources/Private/Backend/Layout']);
        $viewMock->setPartialRootPaths(['EXT:mask_export/Resources/Private/Backend/Partials']);
        $viewMock->setTemplateRootPaths(['EXT:mask_export/Resources/Private/Backend/Templates']);
        GeneralUtility::addInstance(TemplateView::class, $viewMock);

        $request = new Request();
        $request->setControllerVendorName('IchHabRecht');
        $request->setControllerExtensionName('mask_export');
        $request->setControllerName('Export');
        $request->setControllerActionName('list');
        $response = new Response();

        $subject = $objectManager->get(ExportController::class);
        $subject->processRequest($request, $response);

        $closure = \Closure::bind(function () use ($viewMock) {
            return $viewMock->baseRenderingContext;
        }, null, TemplateView::class);
        $renderingContext = $closure();
        if (method_exists($renderingContext, 'getVariableProvider')) {
            $variables = $renderingContext->getVariableProvider();
        } else {
            $variables = $renderingContext->getTemplateVariableContainer();
        }
        $this->files = $variables->get('files');
    }

    /**
     * @param array $additionalConfiguration
     */
    protected function setUpWithExtensionConfiguration(array $additionalConfiguration)
    {
        $configuration = [];
        foreach ($this->configurationToUseInTestInstance['EXT']['extConf'] as $key => $value) {
            $configuration[$key] = unserialize($value);
        }

        $configuration = array_replace_recursive($configuration, $additionalConfiguration);

        foreach ($configuration as $key => $value) {
            $this->configurationToUseInTestInstance['EXT']['extConf'][$key] = serialize($value);
            $this->configurationToUseInTestInstance['EXTENSIONS'][$key] = $value;
        }

        self::setUp();
    }

    /**
     * As the export extension cannot be installed over extensionmanager APi, this function loads data manually
     */
    protected function installExtension()
    {
        // Load ext_tables.sql
        if (!empty($this->files['ext_tables.sql'])) {
            $installUtility = new InstallUtility();
            if (class_exists('TYPO3\\CMS\\Install\\Service\\SqlSchemaMigrationService')) {
                $installToolSqlParser = new SqlSchemaMigrationService();
                $installUtility->injectInstallToolSqlParser($installToolSqlParser);
            }
            $installUtility->updateDbWithExtTablesSql($this->files['ext_tables.sql']);
        }

        // Require PHP files and take care of TCA configuration
        $_EXTKEY = $this->extensionName;
        $_EXTCONF = '';
        foreach ($this->files as $file => $content) {
            if (!preg_match('/\.php$/', $file)) {
                continue;
            }

            if (preg_match('/Configuration\/TCA\/[^.]+\.php', $file)) {
                $tableName = basename($file);
                $tableTca = eval('?>' . $content);
                $GLOBALS['TCA'][$tableName] = $tableTca;
            } else {
                eval('?>' . $content);
            }
        }
    }
}
