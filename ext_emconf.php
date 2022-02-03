<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "mask_export".
 *
 * Auto generated 27-10-2020 16:42
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
  'version' => '3.0.4',
  'constraints' => 
  array (
    'depends' => 
    array (
      'typo3' => '9.5.0-11.5.99',
      'mask' => '4.0.0-0.0.0',
    ),
    'conflicts' => 
    array (
    ),
    'suggests' => 
    array (
    ),
  ),
  '_md5_values_when_last_written' => 'a:72:{s:9:"ChangeLog";s:4:"9bba";s:7:"LICENSE";s:4:"b234";s:9:"README.md";s:4:"4e4a";s:13:"composer.json";s:4:"3e19";s:13:"composer.lock";s:4:"e980";s:12:"ext_icon.png";s:4:"03b4";s:14:"ext_tables.php";s:4:"501e";s:16:"phpunit.xml.dist";s:4:"041c";s:24:"sonar-project.properties";s:4:"207a";s:39:"Classes/Aggregate/AbstractAggregate.php";s:4:"a0f0";s:52:"Classes/Aggregate/AbstractInlineContentAggregate.php";s:4:"b29c";s:48:"Classes/Aggregate/AbstractOverridesAggregate.php";s:4:"1273";s:41:"Classes/Aggregate/AggregateCollection.php";s:4:"4341";s:45:"Classes/Aggregate/BackendPreviewAggregate.php";s:4:"df9f";s:49:"Classes/Aggregate/ContentElementIconAggregate.php";s:4:"2447";s:47:"Classes/Aggregate/ContentRenderingAggregate.php";s:4:"5137";s:53:"Classes/Aggregate/ExtensionConfigurationAggregate.php";s:4:"e778";s:49:"Classes/Aggregate/InlineContentCTypeAggregate.php";s:4:"ba0d";s:50:"Classes/Aggregate/InlineContentColPosAggregate.php";s:4:"49e1";s:44:"Classes/Aggregate/LanguageAwareInterface.php";s:4:"4cc3";s:40:"Classes/Aggregate/LanguageAwareTrait.php";s:4:"82e9";s:54:"Classes/Aggregate/NewContentElementWizardAggregate.php";s:4:"8372";s:39:"Classes/Aggregate/PhpAwareInterface.php";s:4:"bfa9";s:35:"Classes/Aggregate/PhpAwareTrait.php";s:4:"277d";s:49:"Classes/Aggregate/PlainTextFileAwareInterface.php";s:4:"0767";s:45:"Classes/Aggregate/PlainTextFileAwareTrait.php";s:4:"7c14";s:39:"Classes/Aggregate/SqlAwareInterface.php";s:4:"e57c";s:35:"Classes/Aggregate/SqlAwareTrait.php";s:4:"5747";s:34:"Classes/Aggregate/TcaAggregate.php";s:4:"7909";s:35:"Classes/Aggregate/TcaAwareTrait.php";s:4:"9467";s:49:"Classes/Aggregate/TtContentOverridesAggregate.php";s:4:"c926";s:51:"Classes/CodeGenerator/BackendFluidCodeGenerator.php";s:4:"c832";s:43:"Classes/CodeGenerator/HtmlCodeGenerator.php";s:4:"0326";s:39:"Classes/Controller/ExportController.php";s:4:"3307";s:49:"Classes/FileCollection/AbstractFileCollection.php";s:4:"0184";s:41:"Classes/FileCollection/FileCollection.php";s:4:"d9ab";s:49:"Classes/FileCollection/LanguageFileCollection.php";s:4:"76eb";s:44:"Classes/FileCollection/PhpFileCollection.php";s:4:"2f5c";s:50:"Classes/FileCollection/PlainTextFileCollection.php";s:4:"f059";s:44:"Classes/FileCollection/SqlFileCollection.php";s:4:"fbf5";s:45:"Classes/FlagResolver/AbstractFlagResolver.php";s:4:"3aa3";s:38:"Classes/FlagResolver/FlagInterface.php";s:4:"8c65";s:46:"Classes/FlagResolver/FlagResolverInterface.php";s:4:"226d";s:44:"Classes/FlagResolver/PhpFileFlagResolver.php";s:4:"9ace";s:56:"Classes/FlagResolver/PhpFileFlag/ClosureFunctionFlag.php";s:4:"6388";s:57:"Classes/FlagResolver/PhpFileFlag/DefinedTypo3ModeFlag.php";s:4:"c44a";s:52:"Classes/FlagResolver/PhpFileFlag/PhpStartTagFlag.php";s:4:"58a3";s:44:"Resources/Private/Language/locallang_mod.xlf";s:4:"f0bd";s:38:"Resources/Private/Layouts/Default.html";s:4:"8382";s:44:"Resources/Private/Templates/Export/List.html";s:4:"3880";s:36:"Resources/Public/Icons/Extension.svg";s:4:"d494";s:38:"Resources/Public/JavaScript/Toggler.js";s:4:"1880";s:64:"Tests/Functional/Controller/AbstractExportControllerTestCase.php";s:4:"11bd";s:67:"Tests/Functional/Controller/BackendPreview/ExportControllerTest.php";s:4:"2873";s:66:"Tests/Functional/Controller/Configuration/ExportControllerTest.php";s:4:"26c7";s:65:"Tests/Functional/Controller/DataProvider/ExportControllerTest.php";s:4:"76ce";s:68:"Tests/Functional/Controller/FlagResolver/PhpFileFlagResolverTest.php";s:4:"f6f4";s:66:"Tests/Functional/Controller/MaskConfiguration/EmptyColumnsTest.php";s:4:"ce08";s:76:"Tests/Functional/Controller/MaskConfiguration/EmptyMaskConfigurationTest.php";s:4:"9e96";s:76:"Tests/Functional/Controller/NewContentElementWizard/ExportControllerTest.php";s:4:"37e2";s:63:"Tests/Functional/Controller/Ressources/ExportControllerTest.php";s:4:"0eff";s:63:"Tests/Functional/Controller/TypoScript/ExportControllerTest.php";s:4:"33d3";s:57:"Tests/Functional/Fixtures/Configuration/mask-default.json";s:4:"8319";s:63:"Tests/Functional/Fixtures/Configuration/mask-empty-columns.json";s:4:"28ea";s:44:"Tests/Functional/Fixtures/Database/pages.xml";s:4:"5d05";s:47:"Tests/Functional/Fixtures/Database/sys_file.xml";s:4:"fac7";s:49:"Tests/Functional/Fixtures/Database/tt_content.xml";s:4:"9b39";s:77:"Tests/Functional/Fixtures/Database/tx_maskexampleexport_additionalcontent.xml";s:4:"df9e";s:52:"Tests/Functional/Fixtures/Templates/Export/List.html";s:4:"d41d";s:74:"Tests/Functional/Fixtures/Templates/Preview/ce_nested-content-elements.png";s:4:"5ef4";s:62:"Tests/Functional/Fixtures/Templates/Preview/simple-element.png";s:4:"78c9";s:51:"Tests/Unit/FileCollection/PhpFileCollectionTest.php";s:4:"c774";}',
);

