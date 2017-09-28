<?php
namespace CPSIT\MaskExport\Tests\Functional\Controller;

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

use CPSIT\MaskExport\Controller\ExportController;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
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
     * @var array
     */
    protected $configurationToUseInTestInstance = [
        'EXT' => [
            'extConf' => [
                'mask' => 'a:2:{s:4:"json";s:83:"typo3conf/ext/mask_export/Tests/Functional/Fixtures/Configuration/mask-default.json";s:7:"preview";s:70:"typo3conf/ext/mask_export/Tests/Functional/Fixtures/Templates/Preview/";}',
                'mask_export' => 'a:3:{s:17:"maskConfiguration";s:1:"1";s:14:"backendPreview";s:1:"1";s:19:"contentElementIcons";s:1:"1";}',
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
     * @var string
     */
    protected $extensionName = 'mask_example_export';

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

        // The export stores new extension names in backend user settings, so we need a pseudo user object here
        $backendUser = new BackendUserAuthentication();
        $GLOBALS['BE_USER'] = $backendUser;

        $objectManager = new ObjectManager();

        $viewMock = $objectManager->get(TemplateView::class);
        $viewMock->setLayoutRootPaths(['EXT:mask_export/Resources/Private/Backend/Layout']);
        $viewMock->setPartialRootPaths(['EXT:mask_export/Resources/Private/Backend/Partials']);
        $viewMock->setTemplateRootPaths(['EXT:mask_export/Resources/Private/Backend/Templates']);
        GeneralUtility::addInstance(TemplateView::class, $viewMock);

        $request = new Request();
        $request->setControllerVendorName('CPSIT');
        $request->setControllerExtensionName('mask_export');
        $request->setControllerName('Export');
        $request->setControllerActionName('list');
        $request->setArgument('extensionName', $this->extensionName);
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
            $installToolSqlParser = new SqlSchemaMigrationService();
            $installUtility = new InstallUtility();
            $installUtility->injectInstallToolSqlParser($installToolSqlParser);
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
