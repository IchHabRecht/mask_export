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
class PagesOverridesAggregate extends AbstractOverridesAggregate
{
    /**
     * @var string
     */
    protected $table = 'pages';

    /**
     * Adds necessary TCA override information for pages and pages_language_overlay tables
     */
    protected function process()
    {
        if (empty($GLOBALS['TCA'][$this->table])
            || empty($this->maskConfiguration[$this->table]['tca']) && empty($this->maskConfiguration[$this->table]['elements'])
        ) {
            return;
        }

        $tableConfiguration = $GLOBALS['TCA'][$this->table];
        $this->addDisplayCondition($tableConfiguration);
        $this->addTableColumns($tableConfiguration);
    }

    /**
     * @param array $tableConfiguration
     */
    protected function addDisplayCondition(array &$tableConfiguration)
    {
        $newTableFields = $this->getNewTableFields($tableConfiguration);
        foreach ($newTableFields as $key => &$field) {
            $field['displayCond'] = 'USER:MASK\\Mask\\Hooks\\BackendLayoutDisplayCondition->checkBackendLayout:' . $this->getBackendLayoutsByField($key);
        }
        $this->addPhpFile(
            'Classes/Hooks/BackendLayoutDisplayCondition.php',
<<<EOS
namespace MASK\Mask\Hooks;

use TYPO3\CMS\Backend\View\BackendLayoutView;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendLayoutDisplayCondition
{
    /**
     * @param string \$parameter
     * @return bool
     */
    public function checkBackendLayout(\$parameter)
    {
        \$backendLayouts = GeneralUtility::trimExplode(',', \$parameter['conditionParameters'][0], true);
        \$backendLayoutView = GeneralUtility::makeInstance(BackendLayoutView::class);
        \$pageId = \$parameter['record']['uid'];
        \$selectedCombinedIdentifier = \$backendLayoutView->getSelectedCombinedIdentifier(\$pageId);
        if (empty(\$selectedCombinedIdentifier)) {
            \$selectedCombinedIdentifier = 'default';
        }
        \$backendLayout = \$backendLayoutView->getDataProviderCollection()->getBackendLayout(\$selectedCombinedIdentifier, \$pageId);
        if (null === \$backendLayout) {
            return false;
        }

        return in_array(\$backendLayout->getIdentifier(), \$backendLayouts);
    }
}
EOS
        );
    }

    /**
     * @param string $fieldName
     * @return string
     */
    protected function getBackendLayoutsByField($fieldName)
    {
        $backendLayouts = [];
        foreach ($this->maskConfiguration['pages']['elements'] as $backendLayoutIdentifier => $configuration) {
            if (empty($configuration['columns'])) {
                continue;
            }

            if (in_array($fieldName, $configuration['columns'], true)) {
                $backendLayouts[] = $backendLayoutIdentifier;
            }
        }

        return implode(',', $backendLayouts);
    }
}
