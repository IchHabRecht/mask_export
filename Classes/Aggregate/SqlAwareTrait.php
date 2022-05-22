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

trait SqlAwareTrait
{
    /**
     * @var array
     */
    protected $sqlDefinitions = [];

    public function getSqlDefinitions(): array
    {
        return $this->sqlDefinitions;
    }

    protected function addSqlDefinition(string $table, string $field, string $definition): void
    {
        if (!isset($this->sqlDefinitions[$table])) {
            $this->sqlDefinitions[$table] = [];
        }

        $this->sqlDefinitions[$table][$field] = $definition;
    }

    protected function addSqlDefinitions(string $table, array $definitions): void
    {
        foreach ($definitions as $field => $definition) {
            $this->addSqlDefinition($table, $field, $definition);
        }
    }
}
