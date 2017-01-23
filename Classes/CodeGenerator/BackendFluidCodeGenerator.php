<?php
namespace CPSIT\MaskExport\CodeGenerator;

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

        $content =
<<<EOS
<f:if condition="{processedRow.{$field}}">
    <strong><f:translate key="{$label}" /></strong>

EOS;

        switch ($fieldConfiguration['config']['type']) {
            case 'inline':
                $content .= '<br>';
                $content .= $this->getInlineFluid($field, $fieldConfiguration);
                break;
            default:
                $content .=
<<<EOS
    {processedRow.{$field}} (raw={row.{$field}})<br>

EOS;
        }

        $content .=
<<<EOS
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

        $content =
<<<EOS
<f:for each="{{$fieldIterator}.{$field}}" as="{$variableIterator}">
    <ul>
        <li>

EOS;
        switch ($foreignLabelFieldConfiguration['config']['type']) {
            case 'inline':
                $content .=
<<<EOS
            <f:translate key="{$foreignLabel}" /> (id={{$variableIterator}.uid})<br/>

EOS;
                $content .= $this->getInlineFluid($foreignLabelField, $foreignLabelFieldConfiguration, 'item');
                break;
            default:
                $content .=
<<<EOS
            <f:translate key="{$foreignLabel}" /> {{$variableIterator}.{$foreignLabelField}} (id={{$variableIterator}.uid})<br/>

EOS;
        }

        $content .=
<<<EOS
        </li>
    </ul>
</f:for>

EOS;

        return $content;
    }
}
