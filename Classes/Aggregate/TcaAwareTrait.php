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

trait TcaAwareTrait
{
    use LanguageAwareTrait;

    /**
     * @var string
     */
    protected $languageFileIdentifier = 'locallang_db.xlf';

    /**
     * @var string
     */
    protected $languageFilePath = 'Resources/Private/Language/';

    /**
     * @var array
     */
    protected $maskConfiguration;

    /**
     * @var string
     */
    protected $table;

    /**
     * @param array $fieldArray
     */
    protected function addFieldsSqlDefinitions(array $fieldArray)
    {
        foreach ($fieldArray as $field => $_) {
            // Always prefer the current table and search in all definitions as fallback
            if (!empty($this->maskConfiguration[$this->table]['sql'])
                && array_key_exists($field, $this->maskConfiguration[$this->table]['sql'])
            ) {
                $this->addFieldSqlDefinition($this->maskConfiguration[$this->table], $field);
            } else {
                foreach ($this->maskConfiguration as $table => $tableConfiguration) {
                    if ('sys_file_reference' === $table
                        || empty($tableConfiguration['sql'])
                        || !array_key_exists($field, $tableConfiguration['sql'])
                    ) {
                        continue;
                    }

                    $this->addFieldSqlDefinition($tableConfiguration, $field);
                    break;
                }
            }
        }
    }

    /**
     * @param array $tableConfiguration
     * @param string $field
     */
    protected function addFieldSqlDefinition(array $tableConfiguration, $field)
    {
        $table = key($tableConfiguration['sql'][$field]);
        $definition = $tableConfiguration['sql'][$field][$table][$field];
        $this->addSqlDefinition(
            $table,
            $field,
            $definition
        );
        if (isset($this->maskConfiguration[$table]['tca'][$field]['config']['type'])
            && isset($this->maskConfiguration[$table]['tca'][$field]['config']['foreign_table'])
            && 'inline' === $this->maskConfiguration[$table]['tca'][$field]['config']['type']
            && 'tt_content' === $this->maskConfiguration[$table]['tca'][$field]['config']['foreign_table']
        ) {
            $this->addSqlDefinition(
                'tt_content',
                $field . '_parent',
                $definition
            );
            $this->addSqlDefinitions(
                'tt_content',
                [
                    'KEY ' . $field . '_parent' => '(' . $field . '_parent,pid,deleted)',
                ]
            );
        }
    }

    /**
     * @param array $fields
     * @param string $table
     * @return array
     */
    protected function replaceFieldLabels(array $fields, $table)
    {
        foreach ($fields as $field => &$configuration) {
            if (0 !== strpos($field, 'tx_mask_') || 0 === strpos(($configuration['label'] ?? ''), 'LLL:')) {
                continue;
            }
            if (!isset($configuration['label']) && empty($this->maskConfiguration[$table]['elements'])) {
                continue;
            }

            $label = $field;
            if (isset($configuration['label'])) {
                $label = $configuration['label'];
            } else {
                foreach ($this->maskConfiguration[$table]['elements'] as $element) {
                    if (empty($element['columns']) || !in_array($field, $element['columns'], true)) {
                        continue;
                    }

                    $index = array_search($field, $element['columns']);
                    if (isset($element['labels'][$index])) {
                        $label = $element['labels'][$index];
                        break;
                    }
                }
            }

            $this->addLabel(
                $this->languageFilePath . $this->languageFileIdentifier,
                $table . '.' . $field,
                $label
            );
            $configuration['label'] = 'LLL:EXT:mask/'
                . $this->languageFilePath . $this->languageFileIdentifier . ':' . $table . '.' . $field;
        }

        return $fields;
    }

    /**
     * @param array $fields
     * @param string $table
     * @return array
     */
    protected function replaceItemsLabels(array $fields, $table)
    {
        foreach ($fields as $field => &$configuration) {
            if (0 !== strpos($field, 'tx_mask_') || empty($configuration['config']['items'])) {
                continue;
            }
            foreach ($configuration['config']['items'] as $key => &$item) {
                if (empty($item[0])) {
                    continue;
                }
                if (0 === strpos($item[0], 'LLL:')) {
                    continue;
                }

                $this->addLabel(
                    $this->languageFilePath . $this->languageFileIdentifier,
                    $table . '.' . $field . '.I.' . $key,
                    $item[0]
                );
                $item[0] = 'LLL:EXT:mask/'
                    . $this->languageFilePath . $this->languageFileIdentifier . ':' . $table . '.' . $field . '.I.' . $key;
            }
        }

        return $fields;
    }
}
