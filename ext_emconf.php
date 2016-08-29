<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "mask_export".
 *
 * Auto generated 29-08-2016 15:36
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
  'version' => '0.3.0',
  'constraints' => 
  array (
    'depends' => 
    array (
      'typo3' => '7.6.0-8.2.99',
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
  '_md5_values_when_last_written' => 'a:33:{s:9:"ChangeLog";s:4:"411a";s:13:"composer.json";s:4:"10e2";s:12:"ext_icon.png";s:4:"03b4";s:14:"ext_tables.php";s:4:"042f";s:24:"ext_typoscript_setup.txt";s:4:"64d2";s:39:"Classes/Aggregate/AbstractAggregate.php";s:4:"2824";s:48:"Classes/Aggregate/AbstractOverridesAggregate.php";s:4:"dd94";s:41:"Classes/Aggregate/AggregateCollection.php";s:4:"2ae6";s:47:"Classes/Aggregate/ContentRenderingAggregate.php";s:4:"262e";s:53:"Classes/Aggregate/ExtensionConfigurationAggregate.php";s:4:"b9c0";s:44:"Classes/Aggregate/LanguageAwareInterface.php";s:4:"9589";s:40:"Classes/Aggregate/LanguageAwareTrait.php";s:4:"8175";s:54:"Classes/Aggregate/NewContentElementWizardAggregate.php";s:4:"ce0a";s:39:"Classes/Aggregate/PhpAwareInterface.php";s:4:"6a54";s:35:"Classes/Aggregate/PhpAwareTrait.php";s:4:"e3ef";s:49:"Classes/Aggregate/PlainTextFileAwareInterface.php";s:4:"f0ec";s:45:"Classes/Aggregate/PlainTextFileAwareTrait.php";s:4:"d52d";s:39:"Classes/Aggregate/SqlAwareInterface.php";s:4:"74b7";s:35:"Classes/Aggregate/SqlAwareTrait.php";s:4:"65ec";s:34:"Classes/Aggregate/TcaAggregate.php";s:4:"26b0";s:35:"Classes/Aggregate/TcaAwareTrait.php";s:4:"9ece";s:49:"Classes/Aggregate/TtContentOverridesAggregate.php";s:4:"ee6f";s:43:"Classes/CodeGenerator/HtmlCodeGenerator.php";s:4:"1742";s:39:"Classes/Controller/ExportController.php";s:4:"af5a";s:49:"Classes/FileCollection/AbstractFileCollection.php";s:4:"5771";s:41:"Classes/FileCollection/FileCollection.php";s:4:"269c";s:49:"Classes/FileCollection/LanguageFileCollection.php";s:4:"67a1";s:44:"Classes/FileCollection/PhpFileCollection.php";s:4:"aec6";s:50:"Classes/FileCollection/PlainTextFileCollection.php";s:4:"7d95";s:44:"Classes/FileCollection/SqlFileCollection.php";s:4:"048c";s:52:"Resources/Private/Backend/Partials/General/Tabs.html";s:4:"7e6e";s:52:"Resources/Private/Backend/Templates/Export/List.html";s:4:"ddcf";s:40:"Resources/Private/Language/locallang.xlf";s:4:"38d5";}',
);

