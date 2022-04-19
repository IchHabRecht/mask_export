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

use TYPO3\CMS\Core\Utility\ArrayUtility;

class TtContentOverridesAggregate extends AbstractOverridesAggregate
{
    /**
     * @var string
     */
    protected $table = 'tt_content';

    /**
     * Adds necessary TCA override information for tt_content table
     */
    protected function process()
    {
        if (empty($GLOBALS['TCA'][$this->table])
            || empty($this->maskConfiguration[$this->table]['tca']) && empty($this->maskConfiguration[$this->table]['elements'])
        ) {
            return;
        }

        $tableConfiguration = $GLOBALS['TCA'][$this->table];
        $this->addTableColumns($tableConfiguration);
        $this->addTableTypes($tableConfiguration);
    }

    /**
     * @param array $tableConfiguration
     */
    protected function addTableTypes(array $tableConfiguration)
    {
        if (empty($tableConfiguration['types']) || empty($this->maskConfiguration[$this->table]['elements'])) {
            return;
        }

        $types = array_keys($this->maskConfiguration[$this->table]['elements']);
        $newTypeFields = array_intersect_key(
            $tableConfiguration['types'],
            array_combine(
                array_map(static function ($value) {
                    return 'mask_' . $value;
                }, $types),
                $types
            )
        );
        ksort($newTypeFields);

        $this->addLabel(
            $this->languageFilePath . $this->languageFileIdentifier,
            $this->table . '.CType.div._mask_',
            'mask'
        );
        $this->appendPhpFile(
            $this->tcaOverridesFilePath . $this->table . '.php',
            <<<EOS
\$GLOBALS['TCA']['{$this->table}']['columns']['CType']['config']['items'][] = [
    'LLL:EXT:mask/{$this->languageFilePath}{$this->languageFileIdentifier}:{$this->table}.CType.div._mask_',
    '--div--',
];

EOS
            ,
            PhpAwareInterface::PHPFILE_DEFINED_TYPO3_MODE | PhpAwareInterface::PHPFILE_CLOSURE_FUNCTION
        );
        foreach ($newTypeFields as $type => $_) {
            $this->addLabel(
                $this->languageFilePath . $this->languageFileIdentifier,
                $this->table . '.CType.' . $type,
                $this->maskConfiguration[$this->table]['elements'][substr($type, 5)]['label']
            );
            $this->appendPhpFile(
                $this->tcaOverridesFilePath . $this->table . '.php',
                <<<EOS
\$GLOBALS['TCA']['{$this->table}']['columns']['CType']['config']['items'][] = [
    'LLL:EXT:mask/{$this->languageFilePath}{$this->languageFileIdentifier}:{$this->table}.CType.{$type}',
    '{$type}',
    'tx_{$type}',
];

EOS
                ,
                PhpAwareInterface::PHPFILE_DEFINED_TYPO3_MODE | PhpAwareInterface::PHPFILE_CLOSURE_FUNCTION
            );
        }

        $tempTypes = ArrayUtility::arrayExport($newTypeFields);
        $this->appendPhpFile(
            $this->tcaOverridesFilePath . $this->table . '.php',
            <<<EOS
\$tempTypes = {$tempTypes};
\$GLOBALS['TCA']['{$this->table}']['types'] += \$tempTypes;

EOS
            ,
            PhpAwareInterface::PHPFILE_DEFINED_TYPO3_MODE | PhpAwareInterface::PHPFILE_CLOSURE_FUNCTION
        );
    }
}
