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

/**
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TtContentOverridesAggregate extends AbstractOverridesAggregate
{
    /**
     * @var string
     */
    protected $table = 'tt_content';

    /**
     * Adds necessary TCA override information for tt_content table
     */
    protected function process()
    {
        if (empty($GLOBALS['TCA'][$this->table])
            || empty($this->maskConfiguration[$this->table]['tca']) && empty($this->maskConfiguration[$this->table]['elements'])
        ) {
            return;
        }

        $tableConfiguration = $GLOBALS['TCA'][$this->table];
        $this->addTableColumns($tableConfiguration);
        $this->addTableTypes($tableConfiguration);
    }

    /**
     * @param array $tableConfiguration
     */
    protected function addTableTypes(array $tableConfiguration)
    {
        $types = array_keys($this->maskConfiguration[$this->table]['elements']);
        $newTypeFields = array_intersect_key(
            $tableConfiguration['types'],
            array_combine(
                array_map(function ($value) {
                    return 'mask_' . $value;
                }, $types),
                $types
            )
        );

        if (empty($newTypeFields)) {
            return;
        }

        ksort($newTypeFields);

        foreach ($newTypeFields as $type => $_) {
            $this->addLabel(
                $this->languageFilePath . $this->languageFileIdentifier,
                $this->table. '.CType.' . $type,
                $this->maskConfiguration[$this->table]['elements'][substr($type, 5)]['label']
            );
            $this->appendPhpFile(
                $this->tcaOverridesFilePath . $this->table . '.php',
<<<EOS
\$GLOBALS['TCA']['{$this->table}']['columns']['CType']['config']['items'][] = array(
    'LLL:EXT:mask/{$this->languageFilePath}{$this->languageFileIdentifier}:{$this->table}.CType.{$type}',
    '{$type}',
);

EOS
            );
        }

        $tempTypes = var_export($newTypeFields, true);
        $this->appendPhpFile(
            $this->tcaOverridesFilePath . $this->table . '.php',
<<<EOS
\$tempTypes = {$tempTypes};
\$GLOBALS['TCA']['{$this->table}']['types'] += \$tempTypes;

EOS
        );
    }
}
