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

trait SqlAwareTrait
{
    /**
     * @var array
     */
    protected $sqlDefinitions = [];

    /**
     * @return array
     */
    public function getSqlDefinitions()
    {
        return $this->sqlDefinitions;
    }

    /**
     * @param $table
     * @param $field
     * @param $definition
     */
    protected function addSqlDefinition($table, $field, $definition)
    {
        if (!isset($this->sqlDefinitions[$table])) {
            $this->sqlDefinitions[$table] = [];
        }

        $this->sqlDefinitions[$table][$field] = $definition;
    }

    /**
     * @param string $table
     * @param array $definitions
     */
    protected function addSqlDefinitions($table, array $definitions)
    {
        foreach ($definitions as $field => $definition) {
            $this->addSqlDefinition($table, $field, $definition);
        }
    }
}
