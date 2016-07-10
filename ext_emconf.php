<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "mask_export".
 *
 * Auto generated 10-07-2016 19:36
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
  'state' => 'alpha',
  'version' => '0.1.0-dev',
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
  '_md5_values_when_last_written' => 'a:1:{s:13:"composer.json";s:4:"d47c";}',
);
