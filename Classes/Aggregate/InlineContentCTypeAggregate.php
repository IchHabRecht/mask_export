<?php
namespace IchHabRecht\MaskExport\Aggregate;

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

class InlineContentCTypeAggregate extends AbstractInlineContentAggregate implements PhpAwareInterface
{
    use PhpAwareTrait;

    /**
     * Adds dataProvider for inline content CType restriction
     */
    protected function process()
    {
        $inlineFields = $this->getAvailableInlineFields();
        if (empty($inlineFields)) {
            return;
        }

        $supportedInlineParentFields = [];
        foreach ($inlineFields as $field) {
            if (empty($this->maskConfiguration['tt_content']['tca'][$field]['cTypes'])) {
                continue;
            }

            $supportedInlineParentFields[$field . '_parent'] = $this->maskConfiguration['tt_content']['tca'][$field]['cTypes'];
        }

        if (empty($supportedInlineParentFields)) {
            return;
        }

        ksort($supportedInlineParentFields);
        $supportedInlineParentFields = var_export($supportedInlineParentFields, true);

        $this->appendPhpFile(
            'ext_localconf.php',
            <<<EOS
\$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\MASK\Mask\Form\FormDataProvider\TcaCTypeItem::class] = [
    'depends' => [
        \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class,
    ],
];

EOS
        );

        $this->addPhpFile(
            'Classes/Form/FormDataProvider/TcaCTypeItem.php',
<<<EOS
namespace MASK\Mask\Form\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;

class TcaCTypeItem implements FormDataProviderInterface
{
    /**
     * @var array
     */
    protected \$supportedInlineParentFields = {$supportedInlineParentFields};

    /**
     * @param array \$result
     * @return array
     */
    public function addData(array \$result)
    {
        if ('tt_content' !== \$result['tableName']
            || empty(\$result['databaseRow']['colPos'][0])
            || 999 !== (int)\$result['databaseRow']['colPos'][0]
        ) {
            return \$result;
        }

        if (!empty(\$result['inlineParentUid'])
            && in_array(\$result['inlineParentConfig']['foreign_field'], array_keys(\$this->supportedInlineParentFields), true)
        ) {
            \$cTypes = \$this->supportedInlineParentFields[\$result['inlineParentConfig']['foreign_field']];
        } else {
            \$parentField = array_filter(array_intersect_key(\$result['databaseRow'], \$this->supportedInlineParentFields));
            if (empty(\$parentField)) {
                return \$result;
            }

            if (count(\$parentField) === 1) {
                \$cTypes = \$this->supportedInlineParentFields[key(\$parentField)];
            } else {
                \$cTypes = \$result['databaseRow']['CType'];
            }
        }

        \$result['processedTca']['columns']['CType']['config']['items'] = array_filter(
            \$result['processedTca']['columns']['CType']['config']['items'],
            function (\$item) use (\$cTypes) {
                return in_array(\$item[1], \$cTypes);
            }
        );

        return \$result;
    }
}

EOS
        );
    }
}
