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

use IchHabRecht\MaskExport\CodeGenerator\HtmlCodeGenerator;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContentRenderingAggregate extends AbstractOverridesAggregate implements PlainTextFileAwareInterface
{
    use PlainTextFileAwareTrait;

    /**
     * @var HtmlCodeGenerator
     */
    protected $htmlCodeGenerator;

    /**
     * @var string
     */
    protected $table = 'tt_content';

    /**
     * @var string
     */
    protected $resourcePath = 'Resources/Private/';

    /**
     * @var string
     */
    protected $templatePath = 'Templates/';

    /**
     * @var string
     */
    protected $layoutPath = 'Layouts/';

    /**
     * @var string
     */
    protected $partialPath = 'Partials/';

    /**
     * @param array $maskConfiguration
     * @param HtmlCodeGenerator $htmlCodeGenerator
     */
    public function __construct(array $maskConfiguration, HtmlCodeGenerator $htmlCodeGenerator = null)
    {
        $this->htmlCodeGenerator = (null !== $htmlCodeGenerator) ? $htmlCodeGenerator : GeneralUtility::makeInstance(HtmlCodeGenerator::class);

        parent::__construct($maskConfiguration);
    }

    /**
     * Adds TypoScript and Fluid information
     */
    protected function process()
    {
        if (empty($this->maskConfiguration[$this->table]['elements'])) {
            return;
        }

        $this->addPlainTextFile(
            $this->typoScriptFilePath . 'constants.typoscript',
            <<<EOS
# cat=mask/file; type=string; label=Path to template root (FE)
plugin.tx_mask.view.templateRootPath =

# cat=mask/file; type=string; label=Path to template partials (FE)
plugin.tx_mask.view.partialRootPath =

# cat=mask/file; type=string; label=Path to template layouts (FE)
plugin.tx_mask.view.layoutRootPath =

EOS
        );
        $this->addPlainTextFile(
            $this->typoScriptFilePath . 'setup.typoscript',
            ''
        );
        $this->appendPhpFile(
            $this->tcaOverridesFilePath . $this->table . '.php',
            <<<EOS
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'mask',
    '{$this->typoScriptFilePath}',
    'mask'
);

EOS
            ,
            PhpAwareInterface::PHPFILE_DEFINED_TYPO3_MODE | PhpAwareInterface::PHPFILE_CLOSURE_FUNCTION
        );

        foreach ($this->maskConfiguration[$this->table]['elements'] as $element) {
            $this->addTypoScript($element);
            $this->addFluidTemplate($element);
        }
    }

    /**
     * @param array $element
     */
    protected function addTypoScript(array $element)
    {
        $resourcesPath = 'EXT:mask/' . $this->resourcePath;
        $layoutsPath = $resourcesPath . $this->layoutPath;
        $partialPath = $resourcesPath . $this->partialPath;
        $templateSubFolder = 'tt_content' === $this->table ? 'Content' : GeneralUtility::underscoredToUpperCamelCase($this->table);
        $templatesPath = $resourcesPath . $this->templatePath . $templateSubFolder . '/';
        $key = $element['key'];
        $templateName = GeneralUtility::underscoredToUpperCamelCase($key);
        $this->appendPlainTextFile(
            $this->typoScriptFilePath . 'setup.typoscript',
            <<<EOS
tt_content.mask_{$key} = FLUIDTEMPLATE
tt_content.mask_{$key} {
    layoutRootPaths.0 = {$layoutsPath}
    layoutRootPaths.10 = {\$plugin.tx_mask.view.layoutRootPath}
    partialRootPaths.0 = {$partialPath}
    partialRootPaths.10 = {\$plugin.tx_mask.view.partialRootPath}
    templateRootPaths.0 = {$templatesPath}
    templateRootPaths.10 = {\$plugin.tx_mask.view.templateRootPath}
    templateName = {$templateName}

EOS
        );

        if (!empty($element['columns'])) {
            $dataProcessing = $this->addDataProcessing('tt_content', $element['columns']);
            if (!empty($dataProcessing)) {
                $this->appendPlainTextFile($this->typoScriptFilePath . 'setup.typoscript', $dataProcessing);
            }
        }

        $this->appendPlainTextFile(
            $this->typoScriptFilePath . 'setup.typoscript',
            <<<EOS
}

EOS
        );
    }

    /**
     * @param string $table
     * @param array $fields
     * @return string
     */
    protected function addDataProcessing($table, array $fields)
    {
        $dataProcessing = '';
        $index = 10;
        foreach ($fields as $field) {
            if (empty($GLOBALS['TCA'][$table]['columns'][$field]['config']['type'])
                || 'inline' !== $GLOBALS['TCA'][$table]['columns'][$field]['config']['type']
                || empty($GLOBALS['TCA'][$table]['columns'][$field]['config']['foreign_table'])
            ) {
                continue;
            }

            switch ($GLOBALS['TCA'][$table]['columns'][$field]['config']['foreign_table']) {
                case 'sys_file_reference':
                    $dataProcessing .= $this->addFileProcessorForField($table, $field, $index);
                    break;
                case 'tt_content':
                    $dataProcessing .= $this->addDatabaseQueryProcessorForField($table, $field, $index);
                    break;
                default:
                    $dataProcessing .= $this->addDatabaseQueryProcessorForField($table, $field, $index);
                    $foreignTable = $GLOBALS['TCA'][$table]['columns'][$field]['config']['foreign_table'];
                    if (!empty($GLOBALS['TCA'][$foreignTable]['columns'])) {
                        $inlineDataProcession = $this->addDataProcessing(
                            $GLOBALS['TCA'][$table]['columns'][$field]['config']['foreign_table'],
                            array_keys($GLOBALS['TCA'][$foreignTable]['columns'])
                        );
                        if (!empty($inlineDataProcession)) {
                            $dataProcessing .= <<<EOS
dataProcessing.{$index} {
    {$inlineDataProcession}
}

EOS;
                        }
                    }
                    break;
            }
            $index += 10;
        }

        return $dataProcessing;
    }

    /**
     * @param string $table
     * @param string $columnName
     * @param int $index
     * @return string
     */
    protected function addFileProcessorForField($table, $columnName, $index)
    {
        return <<<EOS
    dataProcessing.{$index} = TYPO3\CMS\Frontend\DataProcessing\FilesProcessor
    dataProcessing.{$index} {
        if.isTrue.field = {$columnName}
        references {
            fieldName = {$columnName}
            table = {$table}
        }
        as = data_{$columnName}
    }

EOS;
    }

    /**
     * @param string $table
     * @param string $columnName
     * @param int $index
     * @return string
     */
    protected function addDatabaseQueryProcessorForField($table, $columnName, $index)
    {
        $where = $GLOBALS['TCA'][$table]['columns'][$columnName]['config']['foreign_field'] . '=###uid### AND deleted=0 AND hidden=0';
        $markerArray = [
            'uid.field' => 'uid',
        ];
        $overrideColumns = [];
        if (!empty($GLOBALS['TCA'][$table]['columns'][$columnName]['config']['foreign_record_defaults'])) {
            $overrideColumns = $GLOBALS['TCA'][$table]['columns'][$columnName]['config']['foreign_record_defaults'];
        } elseif (!empty($GLOBALS['TCA'][$table]['columns'][$columnName]['config']['overrideChildTca']['columns'])) {
            $overrideColumns = array_map(
                static function ($value) {
                    return $value['config']['default'];
                },
                array_filter(
                    $GLOBALS['TCA'][$table]['columns'][$columnName]['config']['overrideChildTca']['columns'],
                    static function ($item) {
                        return isset($item['config']['default']);
                    }
                )
            );
        }
        foreach ($overrideColumns as $key => $value) {
            if ('CType' === $key) {
                continue;
            }
            $where .= ' AND ' . $key . '=###' . $key . '###';
            $markerArray[$key] = $value;
        }

        if (!empty($this->maskConfiguration[$table]['tca'][$columnName]['cTypes'])) {
            $types = array_combine(
                array_map(
                    static function ($value) {
                        return 'CType' . $value;
                    },
                    range(1, count($this->maskConfiguration[$table]['tca'][$columnName]['cTypes']))
                ),
                $this->maskConfiguration[$table]['tca'][$columnName]['cTypes']
            );
            $where .= ' AND CType IN (###' . implode('###, ###', array_keys($types)) . '###)';
            $markerArray += $types;
        }

        $sorting = 'uid';
        if (!empty($GLOBALS['TCA'][$table]['columns'][$columnName]['config']['foreign_sortby'])) {
            $sorting = $GLOBALS['TCA'][$table]['columns'][$columnName]['config']['foreign_sortby'];
        }

        uksort($markerArray, 'strnatcasecmp');
        $markers = implode("\n        ", array_map(
            static function ($key, $value) {
                return 'markers.' . $key . (strpos($key, '.') === false ? '.value' : '') . ' = ' . $value;
            },
            array_keys($markerArray),
            $markerArray
        ));

        return <<<EOS
    dataProcessing.{$index} = TYPO3\CMS\Frontend\DataProcessing\DatabaseQueryProcessor
    dataProcessing.{$index} {
        if.isTrue.field = {$columnName}
        table = {$GLOBALS['TCA'][$table]['columns'][$columnName]['config']['foreign_table']}
        pidInList.field = pid
        where = {$where}
        orderBy = {$sorting}
        {$markers}
        as = data_{$columnName}
    }

EOS;
    }

    /**
     * @param array $element
     */
    protected function addFluidTemplate(array $element)
    {
        $key = $element['key'];
        $templateSubFolder = 'tt_content' === $this->table ? 'Content' : GeneralUtility::underscoredToUpperCamelCase($this->table);
        $templatePath = $this->resourcePath . $this->templatePath . $templateSubFolder . '/';
        $templateName = GeneralUtility::underscoredToUpperCamelCase($key);
        $html = $this->htmlCodeGenerator->generateHtml($key);
        $this->addPlainTextFile(
            $templatePath . $templateName . '.html',
            $html
        );
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
