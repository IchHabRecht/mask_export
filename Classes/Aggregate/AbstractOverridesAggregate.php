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

abstract class AbstractOverridesAggregate extends AbstractAggregate implements LanguageAwareInterface, PhpAwareInterface, SqlAwareInterface
{
    use PhpAwareTrait;
    use SqlAwareTrait;
    use TcaAwareTrait;

    /**
     * @var string
     */
    protected $tcaOverridesFilePath = 'Configuration/TCA/Overrides/';

    /**
     * @param array $tableConfiguration
     */
    protected function addTableColumns(array $tableConfiguration)
    {
        if (empty($tableConfiguration['columns']) || empty($this->maskConfiguration[$this->table]['tca'])) {
            return;
        }

        $newTableFields = array_intersect_key(
            $tableConfiguration['columns'],
            $this->maskConfiguration[$this->table]['tca']
        );
        if (empty($newTableFields)) {
            return;
        }

        ksort($newTableFields);
        $newTableFields = $this->replaceFieldLabels($newTableFields, $this->table);
        $newTableFields = $this->replaceItemsLabels($newTableFields, $this->table);
        $this->addFieldsSqlDefinitions($newTableFields);
        $tempColumns = ArrayUtility::arrayExport($newTableFields);
        $this->appendPhpFile(
            $this->tcaOverridesFilePath . $this->table . '.php',
            <<<EOS
\$tempColumns = {$tempColumns};
\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility::addTCAcolumns('{$this->table}', \$tempColumns);

EOS
            ,
            PhpAwareInterface::PHPFILE_DEFINED_TYPO3_MODE | PhpAwareInterface::PHPFILE_CLOSURE_FUNCTION
        );
    }
}
