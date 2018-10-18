<?php
namespace IchHabRecht\MaskExport\Tests\Functional\Controller\DataProvider;

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
use TYPO3\CMS\Backend\Form\FormDataCompiler;
use TYPO3\CMS\Backend\Form\FormDataGroup\TcaDatabaseRecord;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

class ExportControllerTest extends AbstractExportControllerTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * @return array
     */
    public function tcaColPosItemDataProviderAddsNestedContentColPosForSupportedRecordsDataProvider()
    {
        return [
            'Add nested inline record' => [
                [
                    'command' => 'new',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 1,
                    'isInlineChild' => true,
                    'inlineFirstPid' => '1',
                    'inlineParentUid' => '1',
                    'inlineParentTableName' => 'tt_content',
                    'inlineParentFieldName' => 'tx_maskexampleexport_related_content',
                    'inlineParentConfig' => [
                        'type' => 'inline',
                        'foreign_table' => 'tt_content',
                        'foreign_sortby' => 'sorting',
                        'appearance' => [
                            'collapseAll' => '1',
                            'levelLinksPosition' => 'top',
                            'showSynchronizationLink' => '1',
                            'showPossibleLocalizationRecords' => true,
                            'showAllLocalizationLink' => '1',
                            'useSortable' => '1',
                            'enabledControls' => [
                                'info' => true,
                                'new' => true,
                                'dragdrop' => '1',
                                'sort' => true,
                                'hide' => true,
                                'delete' => true,
                                'localize' => true,
                            ],
                            'showRemovedLocalizationRecords' => false,
                        ],
                        'behaviour' => [
                            'localizationMode' => 'none',
                        ],
                        'foreign_field' => 'tx_maskexampleexport_related_content_parent',
                        'foreign_record_defaults' => [
                            'colPos' => '999',
                        ],
                        'overrideChildTca' => [
                            'columns' => [
                                'colPos' => [
                                    'config' => [
                                        'default' => '999',
                                    ],
                                ],
                            ],
                        ],
                        'minitems' => 0,
                        'maxitems' => 99999,
                        'inline' => [
                            'parentSysLanguageUid' => 0,
                            'first' => 2,
                            'last' => 2,
                        ],
                    ],
                    'inlineTopMostParentUid' => '1',
                    'inlineTopMostParentTableName' => 'tt_content',
                    'inlineTopMostParentFieldName' => 'tx_maskexampleexport_related_content',
                ],
                true,
            ],
            'Edit nested record' => [
                [
                    'command' => 'edit',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 2,
                    'isInlineChild' => true,
                    'inlineFirstPid' => '1',
                    'inlineParentConfig' => [
                        'type' => 'inline',
                        'foreign_table' => 'tt_content',
                        'foreign_sortby' => 'sorting',
                        'appearance' => [
                            'collapseAll' => '1',
                            'levelLinksPosition' => 'top',
                            'showSynchronizationLink' => '1',
                            'showPossibleLocalizationRecords' => true,
                            'showAllLocalizationLink' => '1',
                            'useSortable' => '1',
                            'enabledControls' => [
                                'info' => true,
                                'new' => true,
                                'dragdrop' => '1',
                                'sort' => true,
                                'hide' => true,
                                'delete' => true,
                                'localize' => true,
                            ],
                            'showRemovedLocalizationRecords' => false,
                        ],
                        'behaviour' => [
                            'localizationMode' => 'none',
                        ],
                        'foreign_field' => 'tx_maskexampleexport_related_content_parent',
                        'foreign_record_defaults' => [
                            'colPos' => '999',
                        ],
                        'overrideChildTca' => [
                            'columns' => [
                                'colPos' => [
                                    'config' => [
                                        'default' => '999',
                                    ],
                                ],
                            ],
                        ],
                        'minitems' => 0,
                        'maxitems' => 99999,
                        'inline' => [
                            'parentSysLanguageUid' => 0,
                            'first' => 2,
                            'last' => 2,
                        ],
                        'renderFieldsOnly' => true,
                    ],
                    'isInlineAjaxOpeningContext' => true,
                    'inlineParentUid' => 1,
                    'inlineParentTableName' => 'tt_content',
                    'inlineParentFieldName' => 'tx_maskexampleexport_related_content',
                    'inlineTopMostParentUid' => '1',
                    'inlineTopMostParentTableName' => 'tt_content',
                    'inlineTopMostParentFieldName' => 'tx_maskexampleexport_related_content',
                ],
                true,
            ],
            'Open nested record directly' => [
                [
                    'command' => 'edit',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 2,
                ],
                true,
            ],
            'Open parent record' => [
                [
                    'command' => 'edit',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 1,
                ],
                false,
            ],
            'Add new tt_content record' => [
                [
                    'command' => 'new',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 1,
                ],
                false,
            ],
            'Add nested inline record in mask element' => [
                [
                    'command' => 'new',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 1,
                    'isInlineChild' => true,
                    'inlineFirstPid' => '1',
                    'inlineParentUid' => '3',
                    'inlineParentTableName' => 'tt_content',
                    'inlineParentFieldName' => 'tx_mask_related_content',
                    'inlineParentConfig' => [
                        'type' => 'inline',
                        'foreign_table' => 'tt_content',
                        'foreign_sortby' => 'sorting',
                        'appearance' => [
                            'collapseAll' => '1',
                            'levelLinksPosition' => 'top',
                            'showSynchronizationLink' => '1',
                            'showPossibleLocalizationRecords' => true,
                            'showAllLocalizationLink' => '1',
                            'useSortable' => '1',
                            'enabledControls' => [
                                'info' => true,
                                'new' => true,
                                'dragdrop' => '1',
                                'sort' => true,
                                'hide' => true,
                                'delete' => true,
                                'localize' => true,
                            ],
                            'showRemovedLocalizationRecords' => false,
                        ],
                        'behaviour' => [
                            'localizationMode' => 'none',
                        ],
                        'foreign_field' => 'tx_mask_related_content_parent',
                        'foreign_record_defaults' => [
                            'colPos' => '999',
                        ],
                        'overrideChildTca' => [
                            'columns' => [
                                'colPos' => [
                                    'config' => [
                                        'default' => '999',
                                    ],
                                ],
                            ],
                        ],
                        'minitems' => 0,
                        'maxitems' => 99999,
                        'inline' => [
                            'parentSysLanguageUid' => 0,
                            'first' => 4,
                            'last' => 4,
                        ],
                    ],
                    'inlineTopMostParentUid' => '3',
                    'inlineTopMostParentTableName' => 'tt_content',
                    'inlineTopMostParentFieldName' => 'tx_mask_related_content',
                ],
                false,
            ],
            'Edit nested record in mask element' => [
                [
                    'command' => 'edit',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 4,
                    'isInlineChild' => true,
                    'inlineFirstPid' => '1',
                    'inlineParentConfig' => [
                        'type' => 'inline',
                        'foreign_table' => 'tt_content',
                        'foreign_sortby' => 'sorting',
                        'appearance' => [
                            'collapseAll' => '1',
                            'levelLinksPosition' => 'top',
                            'showSynchronizationLink' => '1',
                            'showPossibleLocalizationRecords' => true,
                            'showAllLocalizationLink' => '1',
                            'useSortable' => '1',
                            'enabledControls' => [
                                'info' => true,
                                'new' => true,
                                'dragdrop' => '1',
                                'sort' => true,
                                'hide' => true,
                                'delete' => true,
                                'localize' => true,
                            ],
                            'showRemovedLocalizationRecords' => false,
                        ],
                        'behaviour' => [
                            'localizationMode' => 'none',
                        ],
                        'foreign_field' => 'tx_mask_related_content_parent',
                        'foreign_record_defaults' => [
                            'colPos' => '999',
                        ],
                        'overrideChildTca' => [
                            'columns' => [
                                'colPos' => [
                                    'config' => [
                                        'default' => '999',
                                    ],
                                ],
                            ],
                        ],
                        'minitems' => 0,
                        'maxitems' => 99999,
                        'inline' => [
                            'parentSysLanguageUid' => 0,
                            'first' => 4,
                            'last' => 4,
                        ],
                        'renderFieldsOnly' => true,
                    ],
                    'isInlineAjaxOpeningContext' => true,
                    'inlineParentUid' => 1,
                    'inlineParentTableName' => 'tt_content',
                    'inlineParentFieldName' => 'tx_mask_related_content',
                    'inlineTopMostParentUid' => '1',
                    'inlineTopMostParentTableName' => 'tt_content',
                    'inlineTopMostParentFieldName' => 'tx_mask_related_content',
                ],
                false,
            ],
            'Open nested record from mask element directly' => [
                [
                    'command' => 'edit',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 4,
                ],
                false,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider tcaColPosItemDataProviderAddsNestedContentColPosForSupportedRecordsDataProvider
     * @param array $formDataCompilerInput
     * @param bool $expectation
     */
    public function tcaColPosItemDataProviderAddsNestedContentColPosForSupportedRecords(array $formDataCompilerInput, $expectation)
    {
        $this->installExtension();

        // Load database fixtures
        $fixturePath = ORIGINAL_ROOT . 'typo3conf/ext/mask_export/Tests/Functional/Fixtures/Database/';
        $this->importDataSet($fixturePath . 'pages.xml');
        $this->importDataSet($fixturePath . 'tt_content.xml');
        $this->importDataSet($fixturePath . 'sys_file.xml');

        $this->setUpBackendUserFromFixture(1);
        /** @var \PHPUnit_Framework_MockObject_MockObject|LanguageService $languageService */
        $languageService = $this->getMockBuilder(LanguageService::class)
            ->setMethods(['sL'])
            ->getMock();
        $languageService->init('default');
        $languageService->expects($this->any())
            ->method('sL')
            ->willReturnCallback(function ($argument) {
                if ($argument === 'LLL:EXT:mask_example_export/Resources/Private/Language/locallang_db.xlf:tt_content.colPos.nestedContentColPos') {
                    return 'Nested content (mask_example_export)';
                }

                return 'placeholder';
            });
        $GLOBALS['LANG'] = $languageService;

        $formDataGroup = GeneralUtility::makeInstance(TcaDatabaseRecord::class);
        $formDataCompiler = GeneralUtility::makeInstance(FormDataCompiler::class, $formDataGroup);
        $result = $formDataCompiler->compile($formDataCompilerInput);

        if ($expectation) {
            $this->assertSame('Nested content (mask_example_export)', $result['processedTca']['columns']['colPos']['config']['items'][0][0]);
        } else {
            $this->assertNotSame('Nested content (mask_example_export)', $result['processedTca']['columns']['colPos']['config']['items'][0][0]);
        }
    }

    /**
     * @return array
     */
    public function tcaCTypeItemDataProviderRemovesRestrictedCTypesForSupportedRecordsDataProvider()
    {
        return [
            'Add nested inline record' => [
                [
                    'command' => 'new',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 1,
                    'isInlineChild' => true,
                    'inlineFirstPid' => '1',
                    'inlineParentUid' => '1',
                    'inlineParentTableName' => 'tt_content',
                    'inlineParentFieldName' => 'tx_maskexampleexport_related_content',
                    'inlineParentConfig' => [
                        'type' => 'inline',
                        'foreign_table' => 'tt_content',
                        'foreign_sortby' => 'sorting',
                        'appearance' => [
                            'collapseAll' => '1',
                            'levelLinksPosition' => 'top',
                            'showSynchronizationLink' => '1',
                            'showPossibleLocalizationRecords' => true,
                            'showAllLocalizationLink' => '1',
                            'useSortable' => '1',
                            'enabledControls' => [
                                'info' => true,
                                'new' => true,
                                'dragdrop' => '1',
                                'sort' => true,
                                'hide' => true,
                                'delete' => true,
                                'localize' => true,
                            ],
                            'showRemovedLocalizationRecords' => false,
                        ],
                        'behaviour' => [
                            'localizationMode' => 'none',
                        ],
                        'foreign_field' => 'tx_maskexampleexport_related_content_parent',
                        'foreign_record_defaults' => [
                            'colPos' => '999',
                        ],
                        'overrideChildTca' => [
                            'columns' => [
                                'colPos' => [
                                    'config' => [
                                        'default' => '999',
                                    ],
                                ],
                            ],
                        ],
                        'minitems' => 0,
                        'maxitems' => 99999,
                        'inline' => [
                            'parentSysLanguageUid' => 0,
                            'first' => 2,
                            'last' => 2,
                        ],
                    ],
                    'inlineTopMostParentUid' => '1',
                    'inlineTopMostParentTableName' => 'tt_content',
                    'inlineTopMostParentFieldName' => 'tx_maskexampleexport_related_content',
                ],
                true,
            ],
            'Edit nested record' => [
                [
                    'command' => 'edit',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 2,
                    'isInlineChild' => true,
                    'inlineFirstPid' => '1',
                    'inlineParentConfig' => [
                        'type' => 'inline',
                        'foreign_table' => 'tt_content',
                        'foreign_sortby' => 'sorting',
                        'appearance' => [
                            'collapseAll' => '1',
                            'levelLinksPosition' => 'top',
                            'showSynchronizationLink' => '1',
                            'showPossibleLocalizationRecords' => true,
                            'showAllLocalizationLink' => '1',
                            'useSortable' => '1',
                            'enabledControls' => [
                                'info' => true,
                                'new' => true,
                                'dragdrop' => '1',
                                'sort' => true,
                                'hide' => true,
                                'delete' => true,
                                'localize' => true,
                            ],
                            'showRemovedLocalizationRecords' => false,
                        ],
                        'behaviour' => [
                            'localizationMode' => 'none',
                        ],
                        'foreign_field' => 'tx_maskexampleexport_related_content_parent',
                        'foreign_record_defaults' => [
                            'colPos' => '999',
                        ],
                        'overrideChildTca' => [
                            'columns' => [
                                'colPos' => [
                                    'config' => [
                                        'default' => '999',
                                    ],
                                ],
                            ],
                        ],
                        'minitems' => 0,
                        'maxitems' => 99999,
                        'inline' => [
                            'parentSysLanguageUid' => 0,
                            'first' => 2,
                            'last' => 2,
                        ],
                        'renderFieldsOnly' => true,
                    ],
                    'isInlineAjaxOpeningContext' => true,
                    'inlineParentUid' => 1,
                    'inlineParentTableName' => 'tt_content',
                    'inlineParentFieldName' => 'tx_maskexampleexport_related_content',
                    'inlineTopMostParentUid' => '1',
                    'inlineTopMostParentTableName' => 'tt_content',
                    'inlineTopMostParentFieldName' => 'tx_maskexampleexport_related_content',
                ],
                true,
            ],
            'Open nested record directly' => [
                [
                    'command' => 'edit',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 2,
                ],
                true,
            ],
            'Open parent record' => [
                [
                    'command' => 'edit',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 1,
                ],
                false,
            ],
            'Add new tt_content record' => [
                [
                    'command' => 'new',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 1,
                ],
                false,
            ],
            'Add nested inline record in mask element' => [
                [
                    'command' => 'new',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 1,
                    'isInlineChild' => true,
                    'inlineFirstPid' => '1',
                    'inlineParentUid' => '3',
                    'inlineParentTableName' => 'tt_content',
                    'inlineParentFieldName' => 'tx_mask_related_content',
                    'inlineParentConfig' => [
                        'type' => 'inline',
                        'foreign_table' => 'tt_content',
                        'foreign_sortby' => 'sorting',
                        'appearance' => [
                            'collapseAll' => '1',
                            'levelLinksPosition' => 'top',
                            'showSynchronizationLink' => '1',
                            'showPossibleLocalizationRecords' => true,
                            'showAllLocalizationLink' => '1',
                            'useSortable' => '1',
                            'enabledControls' => [
                                'info' => true,
                                'new' => true,
                                'dragdrop' => '1',
                                'sort' => true,
                                'hide' => true,
                                'delete' => true,
                                'localize' => true,
                            ],
                            'showRemovedLocalizationRecords' => false,
                        ],
                        'behaviour' => [
                            'localizationMode' => 'none',
                        ],
                        'foreign_field' => 'tx_mask_related_content_parent',
                        'foreign_record_defaults' => [
                            'colPos' => '999',
                        ],
                        'overrideChildTca' => [
                            'columns' => [
                                'colPos' => [
                                    'config' => [
                                        'default' => '999',
                                    ],
                                ],
                            ],
                        ],
                        'minitems' => 0,
                        'maxitems' => 99999,
                        'inline' => [
                            'parentSysLanguageUid' => 0,
                            'first' => 4,
                            'last' => 4,
                        ],
                    ],
                    'inlineTopMostParentUid' => '3',
                    'inlineTopMostParentTableName' => 'tt_content',
                    'inlineTopMostParentFieldName' => 'tx_mask_related_content',
                ],
                false,
            ],
            'New page content' => [
                [
                    'command' => 'new',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 1,
                ],
                false,
            ],
            'Edit page content' => [
                [
                    'command' => 'edit',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 5,
                ],
                false,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider tcaCTypeItemDataProviderRemovesRestrictedCTypesForSupportedRecordsDataProvider
     * @param array $formDataCompilerInput
     * @param bool $expectation
     */
    public function tcaCTypeItemDataProviderRemovesRestrictedCTypesForSupportedRecords(array $formDataCompilerInput, $expectation)
    {
        $this->installExtension();

        // Load database fixtures
        $fixturePath = ORIGINAL_ROOT . 'typo3conf/ext/mask_export/Tests/Functional/Fixtures/Database/';
        $this->importDataSet($fixturePath . 'pages.xml');
        $this->importDataSet($fixturePath . 'tt_content.xml');
        $this->importDataSet($fixturePath . 'sys_file.xml');

        $this->setUpBackendUserFromFixture(1);
        $languageService = new LanguageService();
        $languageService->init('default');
        $GLOBALS['LANG'] = $languageService;

        $formDataGroup = GeneralUtility::makeInstance(TcaDatabaseRecord::class);
        $formDataCompiler = GeneralUtility::makeInstance(FormDataCompiler::class, $formDataGroup);
        $result = $formDataCompiler->compile($formDataCompilerInput);

        if ($expectation) {
            $this->assertCount(3, $result['processedTca']['columns']['CType']['config']['items']);
        } else {
            $this->assertGreaterThan(3, count($result['processedTca']['columns']['CType']['config']['items']));
        }
    }

    /**
     * @return array
     */
    public function tcaCTypeItemDataProviderRemovesRestrictedCTypesForMultiNestedRecordsDataProvider()
    {
        return [
            'Add multi-nested inline record' => [
                [
                    'command' => 'new',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 1,
                    'isInlineChild' => true,
                    'inlineFirstPid' => '1',
                    'inlineParentUid' => '1',
                    'inlineParentTableName' => 'tx_maskexampleexport_additionalcontent',
                    'inlineParentFieldName' => 'tx_maskexampleexport_morecontent',
                    'inlineParentConfig' => [
                        'type' => 'inline',
                        'foreign_table' => 'tt_content',
                        'foreign_sortby' => 'sorting',
                        'appearance' => [
                            'collapseAll' => '1',
                            'levelLinksPosition' => 'top',
                            'showSynchronizationLink' => '1',
                            'showPossibleLocalizationRecords' => true,
                            'showAllLocalizationLink' => '1',
                            'useSortable' => '1',
                            'enabledControls' => [
                                'info' => true,
                                'new' => true,
                                'dragdrop' => '1',
                                'sort' => true,
                                'hide' => true,
                                'delete' => true,
                                'localize' => true,
                            ],
                            'showRemovedLocalizationRecords' => false,
                        ],
                        'behaviour' => [
                            'localizationMode' => 'none',
                        ],
                        'foreign_field' => 'tx_maskexampleexport_morecontent_parent',
                        'foreign_record_defaults' => [
                            'colPos' => '999',
                        ],
                        'overrideChildTca' => [
                            'columns' => [
                                'colPos' => [
                                    'config' => [
                                        'default' => '999',
                                    ],
                                ],
                            ],
                        ],
                        'minitems' => 0,
                        'maxitems' => 99999,
                        'inline' => [
                            'parentSysLanguageUid' => 0,
                            'first' => 2,
                            'last' => 2,
                        ],
                    ],
                    'inlineTopMostParentUid' => '1',
                    'inlineTopMostParentTableName' => 'tt_content',
                    'inlineTopMostParentFieldName' => 'tx_maskexampleexport_additionalcontent',
                ],
                true,
            ],
            'Edit multi-nested record' => [
                [
                    'command' => 'edit',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 6,
                    'isInlineChild' => true,
                    'inlineFirstPid' => '1',
                    'inlineParentConfig' => [
                        'type' => 'inline',
                        'foreign_table' => 'tt_content',
                        'foreign_sortby' => 'sorting',
                        'appearance' => [
                            'collapseAll' => '1',
                            'levelLinksPosition' => 'top',
                            'showSynchronizationLink' => '1',
                            'showPossibleLocalizationRecords' => true,
                            'showAllLocalizationLink' => '1',
                            'useSortable' => '1',
                            'enabledControls' => [
                                'info' => true,
                                'new' => true,
                                'dragdrop' => '1',
                                'sort' => true,
                                'hide' => true,
                                'delete' => true,
                                'localize' => true,
                            ],
                            'showRemovedLocalizationRecords' => false,
                        ],
                        'behaviour' => [
                            'localizationMode' => 'none',
                        ],
                        'foreign_field' => 'tx_maskexampleexport_morecontent_parent',
                        'foreign_record_defaults' => [
                            'colPos' => '999',
                        ],
                        'overrideChildTca' => [
                            'columns' => [
                                'colPos' => [
                                    'config' => [
                                        'default' => '999',
                                    ],
                                ],
                            ],
                        ],
                        'minitems' => 0,
                        'maxitems' => 99999,
                        'inline' => [
                            'parentSysLanguageUid' => 0,
                            'first' => 6,
                            'last' => 6,
                        ],
                        'renderFieldsOnly' => true,
                    ],
                    'isInlineAjaxOpeningContext' => true,
                    'inlineParentUid' => 1,
                    'inlineParentTableName' => 'tx_maskexampleexport_additionalcontent',
                    'inlineParentFieldName' => 'tx_maskexampleexport_morecontent',
                    'inlineTopMostParentUid' => '1',
                    'inlineTopMostParentTableName' => 'tt_content',
                    'inlineTopMostParentFieldName' => 'tx_maskexampleexport_additionalcontent',
                ],
                true,
            ],
            'Open multi-nested record directly' => [
                [
                    'command' => 'edit',
                    'tableName' => 'tt_content',
                    'vanillaUid' => 6,
                ],
                true,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider tcaCTypeItemDataProviderRemovesRestrictedCTypesForMultiNestedRecordsDataProvider
     * @param array $formDataCompilerInput
     * @param bool $expectation
     */
    public function tcaCTypeItemDataProviderRemovesRestrictedCTypesForMultiNestedRecords(array $formDataCompilerInput, $expectation)
    {
        $this->installExtension();

        // Load database fixtures
        $fixturePath = ORIGINAL_ROOT . 'typo3conf/ext/mask_export/Tests/Functional/Fixtures/Database/';
        $this->importDataSet($fixturePath . 'pages.xml');
        $this->importDataSet($fixturePath . 'tt_content.xml');
        $this->importDataSet($fixturePath . 'tx_maskexampleexport_additionalcontent.xml');
        $this->importDataSet($fixturePath . 'sys_file.xml');

        $this->setUpBackendUserFromFixture(1);
        $languageService = new LanguageService();
        $languageService->init('default');
        $GLOBALS['LANG'] = $languageService;

        $formDataGroup = GeneralUtility::makeInstance(TcaDatabaseRecord::class);
        $formDataCompiler = GeneralUtility::makeInstance(FormDataCompiler::class, $formDataGroup);
        $result = $formDataCompiler->compile($formDataCompilerInput);

        if ($expectation) {
            $this->assertCount(4, $result['processedTca']['columns']['CType']['config']['items']);
            $this->assertNotContains('bullets', $result['processedTca']['columns']['CType']['config']['items']);
        } else {
            $this->assertGreaterThan(4, count($result['processedTca']['columns']['CType']['config']['items']));
        }
    }
}
