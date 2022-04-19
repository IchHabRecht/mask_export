<?php

declare(strict_types=1);

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

use Doctrine\DBAL\Schema\Table;
use IchHabRecht\MaskExport\Aggregate\SqlAwareInterface;
use TYPO3\CMS\Core\Database\Schema\DefaultTcaSchema;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SqlFileCollection extends AbstractFileCollection
{
    /**
     * @var string
     */
    protected $fileIdentifier = 'ext_tables.sql';

    /**
     * @var DefaultTcaSchema
     */
    protected $defaultSchema;

    public function __construct(array $aggregateCollection, DefaultTcaSchema $defaultSchema = null)
    {
        parent::__construct($aggregateCollection);

        $this->defaultSchema = $defaultSchema ?: GeneralUtility::makeInstance(DefaultTcaSchema::class);
    }

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
            $schemaTable = new Table($table);
            $schema = $this->defaultSchema->enrich([$schemaTable])[0];

            $schemaColumns = $schema->getColumns();
            $schemaIndexes = $schema->getIndexes();

            $aggregatedFields = array_diff_key($fields, $schemaColumns);
            unset($aggregatedFields['PRIMARY KEY']);
            foreach (array_keys($schemaIndexes) as $index) {
                unset($aggregatedFields['KEY ' . $index]);
            }

            array_walk($aggregatedFields, static function (&$definition, $field) {
                $definition = sprintf('    %s %s', $field, $definition);
            });
            $fieldDefinitions = implode(',' . PHP_EOL, $aggregatedFields);
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
