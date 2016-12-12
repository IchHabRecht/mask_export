<?php
namespace CPSIT\MaskExport\FileCollection;

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

use CPSIT\MaskExport\Aggregate\SqlAwareInterface;

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
