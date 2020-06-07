<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "mask_export".
 *
 * Auto generated 07-06-2020 14:37
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
  'version' => '3.0.1',
  'constraints' => 
  array (
    'depends' => 
    array (
      'typo3' => '9.5.0-10.4.99',
      'mask' => '4.0.0-0.0.0',
    ),
    'conflicts' => 
    array (
    ),
    'suggests' => 
    array (
    ),
  ),
  '_md5_values_when_last_written' => 'a:71:{s:9:"ChangeLog";s:4:"586d";s:7:"LICENSE";s:4:"b234";s:9:"README.md";s:4:"4e4a";s:13:"composer.json";s:4:"e3dd";s:12:"ext_icon.png";s:4:"03b4";s:14:"ext_tables.php";s:4:"501e";s:16:"phpunit.xml.dist";s:4:"041c";s:24:"sonar-project.properties";s:4:"207a";s:39:"Classes/Aggregate/AbstractAggregate.php";s:4:"618e";s:52:"Classes/Aggregate/AbstractInlineContentAggregate.php";s:4:"ff36";s:48:"Classes/Aggregate/AbstractOverridesAggregate.php";s:4:"cc54";s:41:"Classes/Aggregate/AggregateCollection.php";s:4:"21b1";s:45:"Classes/Aggregate/BackendPreviewAggregate.php";s:4:"8bf8";s:49:"Classes/Aggregate/ContentElementIconAggregate.php";s:4:"7989";s:47:"Classes/Aggregate/ContentRenderingAggregate.php";s:4:"135a";s:53:"Classes/Aggregate/ExtensionConfigurationAggregate.php";s:4:"3c38";s:49:"Classes/Aggregate/InlineContentCTypeAggregate.php";s:4:"4414";s:50:"Classes/Aggregate/InlineContentColPosAggregate.php";s:4:"9077";s:44:"Classes/Aggregate/LanguageAwareInterface.php";s:4:"16c3";s:40:"Classes/Aggregate/LanguageAwareTrait.php";s:4:"279d";s:54:"Classes/Aggregate/NewContentElementWizardAggregate.php";s:4:"7558";s:39:"Classes/Aggregate/PhpAwareInterface.php";s:4:"6ee0";s:35:"Classes/Aggregate/PhpAwareTrait.php";s:4:"e22d";s:49:"Classes/Aggregate/PlainTextFileAwareInterface.php";s:4:"af2e";s:45:"Classes/Aggregate/PlainTextFileAwareTrait.php";s:4:"10d4";s:39:"Classes/Aggregate/SqlAwareInterface.php";s:4:"40e1";s:35:"Classes/Aggregate/SqlAwareTrait.php";s:4:"3c2f";s:34:"Classes/Aggregate/TcaAggregate.php";s:4:"a8d2";s:35:"Classes/Aggregate/TcaAwareTrait.php";s:4:"af6f";s:49:"Classes/Aggregate/TtContentOverridesAggregate.php";s:4:"23e5";s:51:"Classes/CodeGenerator/BackendFluidCodeGenerator.php";s:4:"c2dc";s:43:"Classes/CodeGenerator/HtmlCodeGenerator.php";s:4:"c6f0";s:39:"Classes/Controller/ExportController.php";s:4:"d53c";s:49:"Classes/FileCollection/AbstractFileCollection.php";s:4:"e654";s:41:"Classes/FileCollection/FileCollection.php";s:4:"47c7";s:49:"Classes/FileCollection/LanguageFileCollection.php";s:4:"c36c";s:44:"Classes/FileCollection/PhpFileCollection.php";s:4:"648e";s:50:"Classes/FileCollection/PlainTextFileCollection.php";s:4:"56b2";s:44:"Classes/FileCollection/SqlFileCollection.php";s:4:"d06f";s:45:"Classes/FlagResolver/AbstractFlagResolver.php";s:4:"309a";s:38:"Classes/FlagResolver/FlagInterface.php";s:4:"a359";s:46:"Classes/FlagResolver/FlagResolverInterface.php";s:4:"339e";s:44:"Classes/FlagResolver/PhpFileFlagResolver.php";s:4:"8731";s:56:"Classes/FlagResolver/PhpFileFlag/ClosureFunctionFlag.php";s:4:"42db";s:57:"Classes/FlagResolver/PhpFileFlag/DefinedTypo3ModeFlag.php";s:4:"d0c9";s:52:"Classes/FlagResolver/PhpFileFlag/PhpStartTagFlag.php";s:4:"b83c";s:44:"Resources/Private/Language/locallang_mod.xlf";s:4:"f0bd";s:38:"Resources/Private/Layouts/Default.html";s:4:"8382";s:44:"Resources/Private/Templates/Export/List.html";s:4:"3880";s:36:"Resources/Public/Icons/Extension.svg";s:4:"d494";s:38:"Resources/Public/JavaScript/Toggler.js";s:4:"1880";s:64:"Tests/Functional/Controller/AbstractExportControllerTestCase.php";s:4:"8f05";s:67:"Tests/Functional/Controller/BackendPreview/ExportControllerTest.php";s:4:"fe09";s:66:"Tests/Functional/Controller/Configuration/ExportControllerTest.php";s:4:"c871";s:65:"Tests/Functional/Controller/DataProvider/ExportControllerTest.php";s:4:"1e83";s:68:"Tests/Functional/Controller/FlagResolver/PhpFileFlagResolverTest.php";s:4:"e3b4";s:66:"Tests/Functional/Controller/MaskConfiguration/EmptyColumnsTest.php";s:4:"dcdd";s:76:"Tests/Functional/Controller/MaskConfiguration/EmptyMaskConfigurationTest.php";s:4:"1956";s:76:"Tests/Functional/Controller/NewContentElementWizard/ExportControllerTest.php";s:4:"b53d";s:63:"Tests/Functional/Controller/Ressources/ExportControllerTest.php";s:4:"bec2";s:63:"Tests/Functional/Controller/TypoScript/ExportControllerTest.php";s:4:"f941";s:57:"Tests/Functional/Fixtures/Configuration/mask-default.json";s:4:"8319";s:63:"Tests/Functional/Fixtures/Configuration/mask-empty-columns.json";s:4:"28ea";s:44:"Tests/Functional/Fixtures/Database/pages.xml";s:4:"5d05";s:47:"Tests/Functional/Fixtures/Database/sys_file.xml";s:4:"fac7";s:49:"Tests/Functional/Fixtures/Database/tt_content.xml";s:4:"9b39";s:77:"Tests/Functional/Fixtures/Database/tx_maskexampleexport_additionalcontent.xml";s:4:"df9e";s:52:"Tests/Functional/Fixtures/Templates/Export/List.html";s:4:"d41d";s:74:"Tests/Functional/Fixtures/Templates/Preview/ce_nested-content-elements.png";s:4:"5ef4";s:62:"Tests/Functional/Fixtures/Templates/Preview/simple-element.png";s:4:"78c9";s:51:"Tests/Unit/FileCollection/PhpFileCollectionTest.php";s:4:"7e85";}',
);

