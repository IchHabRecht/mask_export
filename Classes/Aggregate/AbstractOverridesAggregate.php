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

use MASK\Mask\CodeGenerator\HtmlCodeGenerator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractOverridesAggregate extends AbstractAggregate implements LanguageAwareInterface, PhpAwareInterface, PlainTextFileAwareInterface, SqlAwareInterface
{
    use PhpAwareTrait;
    use PlainTextFileAwareTrait;
    use SqlAwareTrait;
    use TcaAwareTrait;

    /**
     * @var HtmlCodeGenerator
     */
    protected $htmlCodeGenerator;

    /**
     * @var string
     */
    protected $tcaOverridesFilePath = 'Configuration/TCA/Overrides/';

    /**
     * @var string
     */
    protected $templatesFilePath = 'Resources/Private/Templates/';

    /**
     * @var string
     */
    protected $typoScriptFilePath = 'Configuration/TypoScript/';

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
     * @param array $tableConfiguration
     */
    protected function addTableColumns(array $tableConfiguration)
    {
        $newTableFields = array_intersect_key($tableConfiguration['columns'],
            $this->maskConfiguration[$this->table]['tca']);

        if (empty($newTableFields)) {
            return;
        }

        ksort($newTableFields);
        $newTableFields = $this->replaceFieldLabels($newTableFields);
        $this->addSqlDefinitions($newTableFields);
        $tempColumns = var_export($newTableFields, true);
        $this->appendPhpFile(
            $this->tcaOverridesFilePath . $this->table . '.php',
<<<EOS
\$tempColumns = {$tempColumns};
\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility::addTCAcolumns('{$this->table}', \$tempColumns);

EOS
        );
    }

    /**
     * @param array $fieldArray
     */
    protected function addSqlDefinitions(array $fieldArray)
    {
        foreach ($fieldArray as $field => $_) {
            foreach ($this->maskConfiguration as $table => $tableConfiguration) {
                if (empty($tableConfiguration['sql']) || !array_key_exists($field, $tableConfiguration['sql'])) {
                    continue;
                }

                $table = key($tableConfiguration['sql'][$field]);
                $definition = $tableConfiguration['sql'][$field][$table][$field];
                $this->addSqlDefinition(
                    $table,
                    $field,
                    $definition
                );
                if ('pages' === $table) {
                    $this->addSqlDefinition(
                        'pages_language_overlay',
                        $field,
                        $definition
                    );
                }
                break;
            }
        }
    }

    /**
     * @param array $element
     */
    protected function addFluidTemplate(array $element)
    {
        $key = $element['key'];
        $html = $this->htmlCodeGenerator->generateHtml($key);
        if (!empty($html)) {
            $this->addPlainTextFile(
                $this->templatesFilePath . GeneralUtility::underscoredToUpperCamelCase($this->table) . '/'. $key . '.html',
                $html
            );
        }
    }
}
