<?php
namespace IchHabRecht\MaskExport\Aggregate;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Nicole Cordes <typo3@cordes.co>, CPS-IT GmbH
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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Utility\EmConfUtility;

class ExtensionConfigurationAggregate extends AbstractAggregate implements PhpAwareInterface, PlainTextFileAwareInterface
{
    use PhpAwareTrait;
    use PlainTextFileAwareTrait;

    /**
     * Adds typical extension files
     */
    protected function process()
    {
        $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mask_export']);
        $this->addExtEmconf();
        $this->addExtIcon();

        if (!empty($extensionConfiguration['maskConfiguration'])) {
            $this->addMaskConfiguration();
        }
    }

    /**
     * Adds ext_emconf.php file
     */
    protected function addExtEmconf()
    {
        $emConfUtility = GeneralUtility::makeInstance(EmConfUtility::class);
        $extensionData = [
            'extKey' => 'mask',
            'EM_CONF' => [
                'title' => 'mask',
                'description' => '',
                'category' => 'fe',
                'author' => '',
                'author_email' => '',
                'author_company' => '',
                'state' => 'stable',
                'version' => '0.1.0',
                'constraints' => [
                    'depends' => [
                        'typo3' => sprintf('%s.0-%s.99', TYPO3_branch, TYPO3_branch),
                    ],
                    'conflicts' => [],
                    'suggests' => [],
                ],
            ],
        ];

        $this->addPhpFile(
            'ext_emconf.php',
            substr($emConfUtility->constructEmConf($extensionData), 6)
        );
    }

    /**
     * Adds ext_icon.png from mask_export extension
     */
    protected function addExtIcon()
    {
        $this->addPlainTextFile(
            'ext_icon.png',
            file_get_contents(ExtensionManagementUtility::extPath('mask_export') . 'ext_icon.png')
        );
    }

    /**
     * Adds mask.josn configuration file
     */
    protected function addMaskConfiguration()
    {
        $maskConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mask']);
        if (empty($maskConfiguration['json'])) {
            return;
        }

        $content = '';
        $jsonFile = PATH_site . $maskConfiguration['json'];
        if (file_exists($jsonFile)) {
            $content = file_get_contents($jsonFile);
        }

        $this->addPlainTextFile(
            $this->escapeMaskExtensionKey('Configuration/Mask/mask.json'),
            $this->escapeMaskExtensionKey($content)
        );
    }
}
