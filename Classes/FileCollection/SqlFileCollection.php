<?php
namespace IchHabRecht\MaskExport\FileCollection;

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

use IchHabRecht\MaskExport\Aggregate\SqlAwareInterface;

class SqlFileCollection extends AbstractFileCollection
{
    /**
     * @var string
     */
    protected $fileIdentifier = 'ext_tables.sql';

    /**
     * @return array
     */
    protected function processAggregateCollection()
    {
        $files = [];
        $sqlDefinitions = [];
        foreach ($this->aggregateCollection as $aggregate) {
            if (!$aggregate instanceof SqlAwareInterface) {
                continue;
            }

            $sqlDefinitions = array_replace_recursive($sqlDefinitions, $aggregate->getSqlDefinitions());
        }

        if (empty($sqlDefinitions)) {
            return [];
        }

        ksort($sqlDefinitions);
        $files[$this->fileIdentifier] = '';
        foreach ($sqlDefinitions as $table => $fields) {
            array_walk($fields, function (&$definition, $field) {
                $definition = sprintf('    %s %s', $field, $definition);
            });
            $fieldDefinitions = implode(',' . PHP_EOL, $fields);
            $files[$this->fileIdentifier] .=
<<<EOS
CREATE TABLE {$table} (
{$fieldDefinitions}
);

EOS;
        }

        return $files;
    }
}
