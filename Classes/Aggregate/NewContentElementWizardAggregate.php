<?php

declare(strict_types=1);

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

class NewContentElementWizardAggregate extends AbstractAggregate implements LanguageAwareInterface, PlainTextFileAwareInterface, PhpAwareInterface
{
    use LanguageAwareTrait;
    use PlainTextFileAwareTrait;
    use PhpAwareTrait;

    /**
     * @var string
     */
    protected $languageFileIdentifier = 'locallang_db_new_content_el.xlf';

    /**
     * @var string
     */
    protected $pageTSConfigFileIdentifier = 'NewContentElementWizard.tsconfig';

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
            ,
            PhpAwareInterface::PHPFILE_DEFINED_TYPO3_MODE | PhpAwareInterface::PHPFILE_CLOSURE_FUNCTION
        );
    }

    /**
     * @param array $element
     */
    protected function processElement(array $element)
    {
        $key = $element['key'];
        $iconIdentifier = 'tx_mask_' . $key;

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
