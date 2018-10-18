<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "mask_export".
 *
 * Auto generated 18-10-2018 13:27
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
  'author_email' => 'typo3@cordes.co',
  'author_company' => 'CPS-IT GmbH | biz-design.biz',
  'state' => 'stable',
  'version' => '2.1.0',
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
      'IchHabRecht\\MaskExport\\' => 'Classes/',
    ),
  ),
  'autoload-dev' => 
  array (
    'psr-4' => 
    array (
      'IchHabRecht\\MaskExport\\Tests\\' => 'Tests/',
    ),
  ),
  '_md5_values_when_last_written' => 'a:62:{s:9:"ChangeLog";s:4:"bf64";s:7:"LICENSE";s:4:"b234";s:9:"README.md";s:4:"4449";s:13:"composer.json";s:4:"3b0d";s:13:"composer.lock";s:4:"636b";s:12:"ext_icon.png";s:4:"03b4";s:14:"ext_tables.php";s:4:"abf6";s:24:"ext_typoscript_setup.txt";s:4:"878c";s:16:"phpunit.xml.dist";s:4:"7d0d";s:24:"sonar-project.properties";s:4:"029e";s:39:"Classes/Aggregate/AbstractAggregate.php";s:4:"8a46";s:52:"Classes/Aggregate/AbstractInlineContentAggregate.php";s:4:"4346";s:48:"Classes/Aggregate/AbstractOverridesAggregate.php";s:4:"6708";s:41:"Classes/Aggregate/AggregateCollection.php";s:4:"0c61";s:45:"Classes/Aggregate/BackendPreviewAggregate.php";s:4:"b270";s:49:"Classes/Aggregate/ContentElementIconAggregate.php";s:4:"beea";s:47:"Classes/Aggregate/ContentRenderingAggregate.php";s:4:"d61f";s:53:"Classes/Aggregate/ExtensionConfigurationAggregate.php";s:4:"f876";s:49:"Classes/Aggregate/InlineContentCTypeAggregate.php";s:4:"dab2";s:50:"Classes/Aggregate/InlineContentColPosAggregate.php";s:4:"fd10";s:44:"Classes/Aggregate/LanguageAwareInterface.php";s:4:"0488";s:40:"Classes/Aggregate/LanguageAwareTrait.php";s:4:"61fc";s:54:"Classes/Aggregate/NewContentElementWizardAggregate.php";s:4:"405e";s:39:"Classes/Aggregate/PhpAwareInterface.php";s:4:"3cbb";s:35:"Classes/Aggregate/PhpAwareTrait.php";s:4:"3290";s:49:"Classes/Aggregate/PlainTextFileAwareInterface.php";s:4:"b2e1";s:45:"Classes/Aggregate/PlainTextFileAwareTrait.php";s:4:"eeb0";s:39:"Classes/Aggregate/SqlAwareInterface.php";s:4:"be60";s:35:"Classes/Aggregate/SqlAwareTrait.php";s:4:"b5ce";s:34:"Classes/Aggregate/TcaAggregate.php";s:4:"61ee";s:35:"Classes/Aggregate/TcaAwareTrait.php";s:4:"4aa3";s:49:"Classes/Aggregate/TtContentOverridesAggregate.php";s:4:"45f9";s:51:"Classes/CodeGenerator/BackendFluidCodeGenerator.php";s:4:"e651";s:43:"Classes/CodeGenerator/HtmlCodeGenerator.php";s:4:"e404";s:39:"Classes/Controller/ExportController.php";s:4:"6cec";s:49:"Classes/FileCollection/AbstractFileCollection.php";s:4:"de93";s:41:"Classes/FileCollection/FileCollection.php";s:4:"6b99";s:49:"Classes/FileCollection/LanguageFileCollection.php";s:4:"be6e";s:44:"Classes/FileCollection/PhpFileCollection.php";s:4:"0037";s:50:"Classes/FileCollection/PlainTextFileCollection.php";s:4:"155f";s:44:"Classes/FileCollection/SqlFileCollection.php";s:4:"191e";s:45:"Resources/Private/Backend/Layout/Default.html";s:4:"d41d";s:52:"Resources/Private/Backend/Partials/General/Tabs.html";s:4:"7e6e";s:52:"Resources/Private/Backend/Templates/Export/List.html";s:4:"6e63";s:40:"Resources/Private/Language/locallang.xlf";s:4:"fd79";s:64:"Tests/Functional/Controller/AbstractExportControllerTestCase.php";s:4:"c1bc";s:67:"Tests/Functional/Controller/BackendPreview/ExportControllerTest.php";s:4:"7962";s:66:"Tests/Functional/Controller/Configuration/ExportControllerTest.php";s:4:"cf76";s:65:"Tests/Functional/Controller/DataProvider/ExportControllerTest.php";s:4:"578e";s:66:"Tests/Functional/Controller/MaskConfiguration/EmptyColumnsTest.php";s:4:"c0b0";s:76:"Tests/Functional/Controller/MaskConfiguration/EmptyMaskConfigurationTest.php";s:4:"515e";s:76:"Tests/Functional/Controller/NewContentElementWizard/ExportControllerTest.php";s:4:"d486";s:63:"Tests/Functional/Controller/Ressources/ExportControllerTest.php";s:4:"60fe";s:63:"Tests/Functional/Controller/TypoScript/ExportControllerTest.php";s:4:"57ad";s:57:"Tests/Functional/Fixtures/Configuration/mask-default.json";s:4:"6be6";s:63:"Tests/Functional/Fixtures/Configuration/mask-empty-columns.json";s:4:"6f9b";s:44:"Tests/Functional/Fixtures/Database/pages.xml";s:4:"5d05";s:47:"Tests/Functional/Fixtures/Database/sys_file.xml";s:4:"fac7";s:49:"Tests/Functional/Fixtures/Database/tt_content.xml";s:4:"9b39";s:77:"Tests/Functional/Fixtures/Database/tx_maskexampleexport_additionalcontent.xml";s:4:"df9e";s:74:"Tests/Functional/Fixtures/Templates/Preview/ce_nested-content-elements.png";s:4:"5ef4";s:62:"Tests/Functional/Fixtures/Templates/Preview/simple-element.png";s:4:"78c9";}',
);

