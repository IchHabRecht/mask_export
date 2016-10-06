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
class TcaAggregate extends AbstractAggregate implements LanguageAwareInterface, PhpAwareInterface, SqlAwareInterface
{
    use PhpAwareTrait;
    use SqlAwareTrait;
    use TcaAwareTrait;

    /**
     * @var string
     */
    protected $tcaFilePath = 'Configuration/TCA/';

    protected function process()
    {
        if (empty($this->maskConfiguration)) {
            return;
        }

        $newTcaTables = array_diff_key($this->maskConfiguration, $this->systemTables);
        ksort($newTcaTables);

        if (empty($newTcaTables)) {
            return;
        }

        foreach ($newTcaTables as $table => $configuration) {
            if (!isset($GLOBALS['TCA'][$table])) {
                continue;
            }

            $this->table = $table;
            $tableConfiguration = $GLOBALS['TCA'][$table];

            $tcaConfiguration = $this->replaceTableLabels($tableConfiguration);
            $tcaConfiguration['ctrl']['iconfile'] = 'EXT:mask/ext_icon.png';
            $this->addTableSqlDefinitions($tableConfiguration);
            $this->addPhpFile(
                $this->tcaFilePath . $table . '.php',
                'return ' . var_export($tcaConfiguration, true) . ';'
            );
        }

        $tableList = implode(', ', array_keys($newTcaTables));
        $this->appendPhpFile(
            'ext_tables.php',
<<<EOS
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('{$tableList}');

EOS
        );
    }

    /**
     * @param array $tableConfiguration
     * @return array
     */
    protected function replaceTableLabels(array $tableConfiguration)
    {
        $title = $this->table;
        if (empty($tableConfiguration['ctrl']['title']) || 0 !== strpos($tableConfiguration['ctrl']['title'], 'LLL:')) {
            $title = $tableConfiguration['ctrl']['title'];
        }
        $this->addLabel(
            $this->languageFilePath . $this->languageFileIdentifier,
            $this->table,
            $title
        );
        $tableConfiguration['ctrl']['title'] = 'LLL:EXT:mask/'
            . $this->languageFilePath . $this->languageFileIdentifier . ':' . $this->table;

        if (!empty($tableConfiguration['columns'])) {
            $tableConfiguration['columns'] = $this->replaceFieldLabels($tableConfiguration['columns']);
        }

        return $tableConfiguration;
    }

    /**
     * @param array $tableConfiguration
     */
    protected function addTableSqlDefinitions(array $tableConfiguration)
    {
        $sqlDefinitions = [
            'uid' => 'int(11) NOT NULL auto_increment',
            'pid' => 'int(11) DEFAULT \'0\' NOT NULL',
            'parentid' => 'int(11) DEFAULT \'0\' NOT NULL',
            'parenttable' => 'varchar(255) DEFAULT \'\' NOT NULL',
            'sorting' => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
            't3ver_oid' => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
            't3ver_id' => 'int(11) DEFAULT \'0\' NOT NULL',
            't3ver_wsid' => 'int(11) DEFAULT \'0\' NOT NULL',
            't3ver_label' => 'varchar(255) DEFAULT \'\' NOT NULL',
            't3ver_state' => 'tinyint(4) DEFAULT \'0\' NOT NULL',
            't3ver_stage' => 'int(11) DEFAULT \'0\' NOT NULL',
            't3ver_count' => 'int(11) DEFAULT \'0\' NOT NULL',
            't3ver_tstamp' => 'int(11) DEFAULT \'0\' NOT NULL',
            't3ver_move_id' => 'int(11) DEFAULT \'0\' NOT NULL',
        ];

        if (!empty($tableConfiguration['ctrl']['tstamp'])) {
            $sqlDefinitions[$tableConfiguration['ctrl']['tstamp']] = 'int(11) unsigned DEFAULT \'0\' NOT NULL';
        }
        if (!empty($tableConfiguration['ctrl']['crdate'])) {
            $sqlDefinitions[$tableConfiguration['ctrl']['crdate']] = 'int(11) unsigned DEFAULT \'0\' NOT NULL';
        }
        if (!empty($tableConfiguration['ctrl']['cruser_id'])) {
            $sqlDefinitions[$tableConfiguration['ctrl']['cruser_id']] = 'int(11) unsigned DEFAULT \'0\' NOT NULL';
        }
        if (!empty($tableConfiguration['ctrl']['delete'])) {
            $sqlDefinitions[$tableConfiguration['ctrl']['delete']] = 'tinyint(4) unsigned DEFAULT \'0\' NOT NULL';
        }
        if (!empty($tableConfiguration['ctrl']['enablecolumns']['disabled'])) {
            $sqlDefinitions[$tableConfiguration['ctrl']['enablecolumns']['disabled']] = 'tinyint(4) unsigned DEFAULT \'0\' NOT NULL';
        }
        if (!empty($tableConfiguration['ctrl']['enablecolumns']['starttime'])) {
            $sqlDefinitions[$tableConfiguration['ctrl']['enablecolumns']['starttime']] = 'tinyint(4) unsigned DEFAULT \'0\' NOT NULL';
        }
        if (!empty($tableConfiguration['ctrl']['enablecolumns']['endtime'])) {
            $sqlDefinitions[$tableConfiguration['ctrl']['enablecolumns']['endtime']] = 'tinyint(4) unsigned DEFAULT \'0\' NOT NULL';
        }
        if (!empty($tableConfiguration['ctrl']['languageField'])) {
            $sqlDefinitions[$tableConfiguration['ctrl']['languageField']] = 'int(11) DEFAULT \'0\' NOT NULL';
        }
        if (!empty($tableConfiguration['ctrl']['transOrigPointerField'])) {
            $sqlDefinitions[$tableConfiguration['ctrl']['transOrigPointerField']] = 'int(11) unsigned DEFAULT \'0\' NOT NULL';
        }
        if (!empty($tableConfiguration['ctrl']['transOrigDiffSourceField'])) {
            $sqlDefinitions[$tableConfiguration['ctrl']['transOrigDiffSourceField']] = 'mediumblob';
        }

        $this->addSqlDefinitions($this->table, $sqlDefinitions);

        if (!empty($tableConfiguration['columns'])) {
            $this->addFieldsSqlDefinitions($tableConfiguration['columns']);
        }

        $this->addSqlDefinitions($this->table, [
            'PRIMARY KEY' => '(uid)',
            'KEY parent' => '(pid)',
            'KEY t3ver_oid' => '(t3ver_oid,t3ver_wsid)',
            'KEY language' => '(l10n_parent,sys_language_uid)',
        ]);
    }
}
