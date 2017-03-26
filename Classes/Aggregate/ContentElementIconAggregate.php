<?php
namespace CPSIT\MaskExport\Aggregate;

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

class ContentElementIconAggregate extends AbstractAggregate implements PhpAwareInterface, PlainTextFileAwareInterface
{
    use PhpAwareTrait;
    use PlainTextFileAwareTrait;

    /**
     * @var string
     */
    protected $iconResourceFilePath = 'Resources/Public/Icons/Content/';

    /**
     * Adds content elements to the newContentElementWizard
     */
    protected function process()
    {
        if (empty($this->maskConfiguration['tt_content']['elements'])) {
            return;
        }

        $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mask_export']);
        if (empty($extensionConfiguration['contentElementIcons'])) {
            return;
        }

        $maskConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mask']);
        $maskIconFolder = PATH_site . $maskConfiguration['preview'];

        $elements = $this->maskConfiguration['tt_content']['elements'];
        ksort($elements);

        $iconRegistryConfiguration = '';
        foreach ($elements as $element) {
            $key = $element['key'];
            $iconIdentifier = 'tx_mask_' . $key;
            $icon = 'ce_' . $key . '.png';
            if (file_exists($maskIconFolder . $icon)
                || empty($element['icon'])
            ) {
                $iconFileName = file_exists($maskIconFolder . $icon) ? $maskIconFolder . $icon
                    : ExtensionManagementUtility::extPath('mask_export') . 'ext_icon.png';
                $iconPath = $this->iconResourceFilePath . $icon;
                $this->addPlainTextFile(
                    $iconPath,
                    file_get_contents($iconFileName)
                );
                $iconRegistryConfiguration .=
<<<EOS
\$iconRegistry->registerIcon(
    '$iconIdentifier',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    [
        'source' => 'EXT:mask/$iconPath',
    ]
);

EOS;
            } else {
                $iconName = substr($element['icon'], 3);
                $iconRegistryConfiguration .=
<<<EOS
\$iconRegistry->registerIcon(
    '$iconIdentifier',
    \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
    [
        'name' => '$iconName',
    ]
);

EOS;
            }
        }

        if (!empty($iconRegistryConfiguration)) {
            $this->appendPhpFile(
                'ext_localconf.php',
<<<EOS
// Register content element icons
\$iconRegistry = \\TYPO3\\CMS\\Core\\Utility\\GeneralUtility::makeInstance(\\TYPO3\\CMS\\Core\\Imaging\\IconRegistry::class);
$iconRegistryConfiguration

EOS
            );
        }
    }
}
