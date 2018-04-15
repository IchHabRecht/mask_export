<?php
namespace IchHabRecht\MaskExport\Aggregate;

/*
 * This file is part of the TYPO3 extension mask_export.
 *
 * (c) 2016 Nicole Cordes <typo3@cordes.co>, CPS-IT GmbH
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

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
