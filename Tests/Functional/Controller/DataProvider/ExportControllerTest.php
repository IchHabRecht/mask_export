<?php
namespace CPSIT\MaskExport\Tests\Functional\Controller\DataProvider;

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

use CPSIT\MaskExport\Tests\Functional\Controller\AbstractExportControllerTestCase;
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
        $languageService = $this->getMock(LanguageService::class, ['sL']);
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
}
