<?php

declare(strict_types=1);

namespace IchHabRecht\MaskExport\CodeGenerator;

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

/**
 * Generates fluid for backend preview rendering
 */
class BackendFluidCodeGenerator
{
    /**
     * Generate Fluid template according to field type
     *
     * @param string $table
     * @param string $field
     * @return string
     */
    public function generateFluid($table, $field)
    {
        if (!isset($GLOBALS['TCA'][$table]['columns'][$field])) {
            return '';
        }

        $fieldConfiguration = $GLOBALS['TCA'][$table]['columns'][$field];
        $label = $fieldConfiguration['label'];

        $content = <<<EOS
<f:if condition="{processedRow.{$field}}">
    <strong><f:translate key="{$label}" /></strong>

EOS;

        switch ($fieldConfiguration['config']['type']) {
            case 'inline':
                $content .= '<br>';
                $content .= $this->getInlineFluid($field, $fieldConfiguration);
                break;
            case 'select':
                $content .= $this->getSelectFluid($field, $fieldConfiguration);
                break;
            default:
                $content .= <<<EOS
    {processedRow.{$field}} (raw={row.{$field}})<br>

EOS;
        }

        $content .= <<<EOS
</f:if>

EOS;

        return $content;
    }

    /**
     * @param string $field
     * @param array $fieldConfiguration
     * @param string $iteratorName
     * @return string
     */
    protected function getInlineFluid($field, array $fieldConfiguration, $iteratorName = '')
    {
        $foreignTable = $fieldConfiguration['config']['foreign_table'];
        $foreignLabelField = $GLOBALS['TCA'][$foreignTable]['ctrl']['label'];
        $foreignLabelFieldConfiguration = $GLOBALS['TCA'][$foreignTable]['columns'][$foreignLabelField];
        $foreignLabel = $foreignLabelFieldConfiguration['label'];
        if (empty($iteratorName)) {
            $fieldIterator = 'processedRow';
            $variableIterator = 'item';
        } else {
            $fieldIterator = $iteratorName;
            $variableIterator = $iteratorName . '_item';
        }

        $content = <<<EOS
<f:for each="{{$fieldIterator}.{$field}}" as="{$variableIterator}">
    <ul>
        <li>

EOS;
        switch ($foreignLabelFieldConfiguration['config']['type']) {
            case 'inline':
                $content .= <<<EOS
            <f:translate key="{$foreignLabel}" /> (id={{$variableIterator}.uid})<br/>

EOS;
                $content .= $this->getInlineFluid($foreignLabelField, $foreignLabelFieldConfiguration, 'item');
                break;
            default:
                if ($foreignLabelFieldConfiguration['config']['type'] === 'group'
                    && $foreignLabelFieldConfiguration['config']['allowed'] === 'sys_file'
                ) {
                    $content .= $this->getSysFileFluid();
                } else {
                    $content .= <<<EOS
            <f:translate key="{$foreignLabel}" /> {{$variableIterator}.{$foreignLabelField}} (id={{$variableIterator}.uid})<br/>

EOS;
                }
        }

        $content .= <<<EOS
        </li>
    </ul>
</f:for>

EOS;

        return $content;
    }

    /**
     * @param string $field
     * @param array $fieldConfiguration
     * @return string
     */
    protected function getSelectFluid($field, array $fieldConfiguration)
    {
        if (empty($fieldConfiguration['config']['maxitems']) || 1 === (int)$fieldConfiguration['config']['maxitems']) {
            $content = <<<EOS
    {processedRow.{$field}.0} (raw={row.{$field}})<br>

EOS;
        } else {
            $content = <<<EOS
    <ul>
        <f:for each="{processedRow.{$field}}" as="item">
            <li>{item}</li>
        </f:for>
    </ul>

EOS;
        }

        return $content;
    }

    /**
     * @return string
     */
    protected function getSysFileFluid()
    {
        if (version_compare(TYPO3_version, '8.6.0', '>=')) {
            $content = <<<EOS
    <ul>
        <f:for each="{item.uid_local}" as="file">
            <li><f:translate key="LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.uid_local" /> {file.table}|{file.uid}|{file.title} (id={item.uid})</li>
        </f:for>
    </ul>

EOS;
        } else {
            $content = <<<EOS
    <f:translate key="LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.uid_local" /> {item.uid_local} (id={item.uid})

EOS;
        }

        return $content;
    }
}
