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

class NewContentElementWizardAggregate extends AbstractAggregate implements LanguageAwareInterface, PlainTextFileAwareInterface, PhpAwareInterface
{
    use LanguageAwareTrait;
    use PlainTextFileAwareTrait;
    use PhpAwareTrait;

    /**
     * @var string
     */
    protected static $defaultIconIdentifier = 'content-textpic';

    /**
     * @var string[]
     */
    protected $icons = [];

    /**
     * @var string
     */
    protected $iconResourceFilePath = 'Resources/Public/Icons/Content/';

    /**
     * @var string
     */
    protected $languageFileIdentifier = 'locallang_db_new_content_el.xlf';

    /**
     * @var string
     */
    protected $languageFilePath = 'Resources/Private/Language/';

    /**
     * @var string
     */
    protected $pageTSConfigFileIdentifier = 'NewContentElementWizard.ts';

    /**
     * @var string
     */
    protected $pageTSConfigFilePath = 'Configuration/PageTSconfig/';

    /**
     * Adds content elements to the newContentElementWizard
     */
    protected function process()
    {
        if (empty($this->maskConfiguration['tt_content']['elements'])) {
            return;
        }

        $this->appendPlainTextFile(
            $this->pageTSConfigFilePath . $this->pageTSConfigFileIdentifier,
<<<EOS
mod.wizards.newContentElement.wizardItems.common {
    elements {

EOS
        );

        $elements = $this->maskConfiguration['tt_content']['elements'];
        ksort($elements);

        $this->prepareContentElementIcons($elements);

        foreach ($elements as $element) {
            $this->processElement($element);
        }

        $elementKeys = implode(', ', array_keys($this->maskConfiguration['tt_content']['elements']));
        $this->appendPlainTextFile(
            $this->pageTSConfigFilePath . $this->pageTSConfigFileIdentifier,
<<<EOS
    }
    show := addToList({$elementKeys})
}

EOS
        );

        $this->appendPhpFile(
            'ext_localconf.php',
<<<EOS
\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:mask/{$this->pageTSConfigFilePath}{$this->pageTSConfigFileIdentifier}">'
);

EOS
        );
    }

    /**
     * @param array $elements
     */
    protected function prepareContentElementIcons(array $elements)
    {
        $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mask_export']);
        if (empty($extensionConfiguration['contentElementIcons'])) {
            return;
        }

        $maskConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mask']);
        $maskIconFolder = PATH_site . $maskConfiguration['preview'];

        $iconRegistryConfiguration = '';
        foreach ($elements as $element) {
            $key = $element['key'];
            $iconIdentifier = 'tx_mask_' . $key;
            $icon = 'ce_' . $key . '.png';
            if (file_exists($maskIconFolder . $icon)) {
                $iconPath = $this->iconResourceFilePath . $icon;
                $this->addPlainTextFile(
                    $iconPath,
                    file_get_contents($maskIconFolder . $icon)
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
                $this->icons[$key] = $iconIdentifier;
            } elseif (!empty($element['icon'])) {
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
                $this->icons[$key] = $iconIdentifier;
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

    /**
     * @param array $element
     */
    protected function processElement(array $element)
    {
        $key = $element['key'];
        $iconIdentifier = !empty($this->icons[$key]) ? $this->icons[$key] : static::$defaultIconIdentifier;

        $this->addLabel(
            $this->languageFilePath . $this->languageFileIdentifier,
            'wizards.newContentElement.' . $key . '_title',
            (!empty($element['label'])) ? $element['label'] : $key
        );
        $this->addLabel(
            $this->languageFilePath . $this->languageFileIdentifier,
            'wizards.newContentElement.' . $key . '_description',
            (!empty($element['description'])) ? $element['description'] : ''
        );

        $this->appendPlainTextFile(
            $this->pageTSConfigFilePath . $this->pageTSConfigFileIdentifier,
<<<EOS
            {$key} {
                iconIdentifier = $iconIdentifier
                title = LLL:EXT:mask/{$this->languageFilePath}{$this->languageFileIdentifier}:wizards.newContentElement.{$key}_title
                description = LLL:EXT:mask/{$this->languageFilePath}{$this->languageFileIdentifier}:wizards.newContentElement.{$key}_description
                tt_content_defValues {
                    CType = mask_{$key}
                }
            }

EOS
        );
    }
}
