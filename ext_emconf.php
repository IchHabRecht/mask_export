<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "mask_export".
 *
 * Auto generated 12-01-2017 02:15
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
  'version' => '0.6.3',
  'constraints' => 
  array (
    'depends' => 
    array (
      'typo3' => '7.6.0-8.5.99',
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
  '_md5_values_when_last_written' => 'a:40:{s:9:"ChangeLog";s:4:"6363";s:13:"composer.json";s:4:"e53b";s:21:"ext_conf_template.txt";s:4:"fbeb";s:12:"ext_icon.png";s:4:"03b4";s:14:"ext_tables.php";s:4:"042f";s:24:"ext_typoscript_setup.txt";s:4:"1014";s:39:"Classes/Aggregate/AbstractAggregate.php";s:4:"0608";s:48:"Classes/Aggregate/AbstractOverridesAggregate.php";s:4:"9f95";s:41:"Classes/Aggregate/AggregateCollection.php";s:4:"de71";s:45:"Classes/Aggregate/BackendPreviewAggregate.php";s:4:"6729";s:47:"Classes/Aggregate/ContentRenderingAggregate.php";s:4:"ac09";s:53:"Classes/Aggregate/ExtensionConfigurationAggregate.php";s:4:"4789";s:50:"Classes/Aggregate/InlineContentColPosAggregate.php";s:4:"12da";s:44:"Classes/Aggregate/LanguageAwareInterface.php";s:4:"9918";s:40:"Classes/Aggregate/LanguageAwareTrait.php";s:4:"4f29";s:54:"Classes/Aggregate/NewContentElementWizardAggregate.php";s:4:"3b48";s:39:"Classes/Aggregate/PhpAwareInterface.php";s:4:"4737";s:35:"Classes/Aggregate/PhpAwareTrait.php";s:4:"7681";s:49:"Classes/Aggregate/PlainTextFileAwareInterface.php";s:4:"9a43";s:45:"Classes/Aggregate/PlainTextFileAwareTrait.php";s:4:"0fb0";s:39:"Classes/Aggregate/SqlAwareInterface.php";s:4:"78e3";s:35:"Classes/Aggregate/SqlAwareTrait.php";s:4:"12fa";s:34:"Classes/Aggregate/TcaAggregate.php";s:4:"80fb";s:35:"Classes/Aggregate/TcaAwareTrait.php";s:4:"82c3";s:49:"Classes/Aggregate/TtContentOverridesAggregate.php";s:4:"2f67";s:51:"Classes/CodeGenerator/BackendFluidCodeGenerator.php";s:4:"5634";s:43:"Classes/CodeGenerator/HtmlCodeGenerator.php";s:4:"ba75";s:39:"Classes/Controller/ExportController.php";s:4:"017b";s:49:"Classes/FileCollection/AbstractFileCollection.php";s:4:"c8e9";s:41:"Classes/FileCollection/FileCollection.php";s:4:"f81c";s:49:"Classes/FileCollection/LanguageFileCollection.php";s:4:"f15d";s:44:"Classes/FileCollection/PhpFileCollection.php";s:4:"972a";s:50:"Classes/FileCollection/PlainTextFileCollection.php";s:4:"1722";s:44:"Classes/FileCollection/SqlFileCollection.php";s:4:"599b";s:45:"Resources/Private/Backend/Layout/Default.html";s:4:"d41d";s:52:"Resources/Private/Backend/Partials/General/Tabs.html";s:4:"7e6e";s:52:"Resources/Private/Backend/Templates/Export/List.html";s:4:"8b0e";s:40:"Resources/Private/Language/locallang.xlf";s:4:"db5e";s:52:"Tests/Functional/Controller/ExportControllerTest.php";s:4:"2529";s:35:"Tests/Functional/Fixtures/mask.json";s:4:"0a2b";}',
);

