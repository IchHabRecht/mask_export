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
        foreach ($inlineFields as $table => $fieldArray) {
            foreach ($fieldArray as $field) {
                if (empty($this->maskConfiguration[$table]['tca'][$field]['cTypes'])) {
                    continue;
                }

                $supportedInlineParentFields[$field . '_parent'] = $this->maskConfiguration[$table]['tca'][$field]['cTypes'];
            }
        }

        if (empty($supportedInlineParentFields)) {
            return;
        }

        ksort($supportedInlineParentFields);
        $supportedInlineParentFields = ArrayUtility::arrayExport($supportedInlineParentFields);

        $this->appendPhpFile(
            'ext_localconf.php',
            <<<EOS
\$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\MASK\Mask\Form\FormDataProvider\TcaCTypeItem::class] = [
    'depends' => [
        \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class,
    ],
];

EOS
            ,
            PhpAwareInterface::PHPFILE_DEFINED_TYPO3_MODE | PhpAwareInterface::PHPFILE_CLOSURE_FUNCTION
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
            || empty(\$result['databaseRow']['colPos'])
            || (is_array(\$result['databaseRow']['colPos']) ? 999 !== (int)\$result['databaseRow']['colPos'][0] : 999 !== (int)\$result['databaseRow']['colPos'])
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
            static function (\$item) use (\$cTypes) {
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
