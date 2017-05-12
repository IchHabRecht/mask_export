<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "mask_export".
 *
 * Auto generated 12-05-2017 11:47
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'Mask Export',
  'description' => 'Export your mask elements as extension',
  'category' => 'module',
  'author' => 'Nicole Cordes',
  'author_email' => 'cordes@cps-it.de',
  'author_company' => 'CPS-IT GmbH',
  'state' => 'beta',
  'version' => '0.9.0',
  'constraints' => 
  array (
    'depends' => 
    array (
      'typo3' => '7.6.0-8.7.99',
      'mask' => '2.0.0-0.0.0',
    ),
    'conflicts' => 
    array (
    ),
    'suggests' => 
    array (
    ),
  ),
  'autoload' => 
  array (
    'psr-4' => 
    array (
      'CPSIT\\MaskExport\\' => 'Classes/',
    ),
  ),
  'autoload-dev' => 
  array (
    'psr-4' => 
    array (
      'CPSIT\\MaskExport\\Tests\\' => 'Tests/',
    ),
  ),
  '_md5_values_when_last_written' => 'a:51:{s:9:"ChangeLog";s:4:"81b2";s:13:"composer.json";s:4:"3a5c";s:21:"ext_conf_template.txt";s:4:"08b6";s:12:"ext_icon.png";s:4:"03b4";s:14:"ext_tables.php";s:4:"042f";s:24:"ext_typoscript_setup.txt";s:4:"1014";s:39:"Classes/Aggregate/AbstractAggregate.php";s:4:"0608";s:52:"Classes/Aggregate/AbstractInlineContentAggregate.php";s:4:"a359";s:48:"Classes/Aggregate/AbstractOverridesAggregate.php";s:4:"9f95";s:41:"Classes/Aggregate/AggregateCollection.php";s:4:"de71";s:45:"Classes/Aggregate/BackendPreviewAggregate.php";s:4:"d65f";s:49:"Classes/Aggregate/ContentElementIconAggregate.php";s:4:"be44";s:47:"Classes/Aggregate/ContentRenderingAggregate.php";s:4:"97ce";s:53:"Classes/Aggregate/ExtensionConfigurationAggregate.php";s:4:"4789";s:49:"Classes/Aggregate/InlineContentCTypeAggregate.php";s:4:"ebd5";s:50:"Classes/Aggregate/InlineContentColPosAggregate.php";s:4:"fea3";s:44:"Classes/Aggregate/LanguageAwareInterface.php";s:4:"9918";s:40:"Classes/Aggregate/LanguageAwareTrait.php";s:4:"4f29";s:54:"Classes/Aggregate/NewContentElementWizardAggregate.php";s:4:"3e71";s:39:"Classes/Aggregate/PhpAwareInterface.php";s:4:"4737";s:35:"Classes/Aggregate/PhpAwareTrait.php";s:4:"7681";s:49:"Classes/Aggregate/PlainTextFileAwareInterface.php";s:4:"9a43";s:45:"Classes/Aggregate/PlainTextFileAwareTrait.php";s:4:"0fb0";s:39:"Classes/Aggregate/SqlAwareInterface.php";s:4:"78e3";s:35:"Classes/Aggregate/SqlAwareTrait.php";s:4:"12fa";s:34:"Classes/Aggregate/TcaAggregate.php";s:4:"80fb";s:35:"Classes/Aggregate/TcaAwareTrait.php";s:4:"82c3";s:49:"Classes/Aggregate/TtContentOverridesAggregate.php";s:4:"8901";s:51:"Classes/CodeGenerator/BackendFluidCodeGenerator.php";s:4:"b5a4";s:43:"Classes/CodeGenerator/HtmlCodeGenerator.php";s:4:"51c5";s:39:"Classes/Controller/ExportController.php";s:4:"a15e";s:49:"Classes/FileCollection/AbstractFileCollection.php";s:4:"c8e9";s:41:"Classes/FileCollection/FileCollection.php";s:4:"f81c";s:49:"Classes/FileCollection/LanguageFileCollection.php";s:4:"1ce7";s:44:"Classes/FileCollection/PhpFileCollection.php";s:4:"972a";s:50:"Classes/FileCollection/PlainTextFileCollection.php";s:4:"1722";s:44:"Classes/FileCollection/SqlFileCollection.php";s:4:"599b";s:45:"Resources/Private/Backend/Layout/Default.html";s:4:"d41d";s:52:"Resources/Private/Backend/Partials/General/Tabs.html";s:4:"7e6e";s:52:"Resources/Private/Backend/Templates/Export/List.html";s:4:"8b0e";s:40:"Resources/Private/Language/locallang.xlf";s:4:"aedc";s:64:"Tests/Functional/Controller/AbstractExportControllerTestCase.php";s:4:"4287";s:67:"Tests/Functional/Controller/BackendPreview/ExportControllerTest.php";s:4:"59a8";s:65:"Tests/Functional/Controller/DataProvider/ExportControllerTest.php";s:4:"aa6f";s:63:"Tests/Functional/Controller/Ressources/ExportControllerTest.php";s:4:"e2b8";s:63:"Tests/Functional/Controller/TypoScript/ExportControllerTest.php";s:4:"763e";s:35:"Tests/Functional/Fixtures/mask.json";s:4:"a84f";s:44:"Tests/Functional/Fixtures/Database/pages.xml";s:4:"5d05";s:47:"Tests/Functional/Fixtures/Database/sys_file.xml";s:4:"fac7";s:49:"Tests/Functional/Fixtures/Database/tt_content.xml";s:4:"8abb";s:74:"Tests/Functional/Fixtures/Templates/Preview/ce_nested-content-elements.png";s:4:"5ef4";}',
);

