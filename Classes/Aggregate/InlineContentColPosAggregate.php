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

class InlineContentColPosAggregate extends AbstractInlineContentAggregate implements LanguageAwareInterface, PhpAwareInterface
{
    use LanguageAwareTrait;
    use PhpAwareTrait;

    /**
     * @var string
     */
    protected $languageFileIdentifier = 'locallang_db.xlf';

    /**
     * Adds dataProvider for inline content colPos name
     */
    protected function process()
    {
        $inlineFields = $this->getAvailableInlineFields();
        if (empty($inlineFields)) {
            return;
        }

        $this->addLabel(
            $this->languageFilePath . $this->languageFileIdentifier,
            'tt_content.colPos.nestedContentColPos',
            'Nested content (mask)'
        );

        $this->appendPhpFile(
            'ext_localconf.php',
<<<EOS
\$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\MASK\Mask\Form\FormDataProvider\TcaColPosItem::class] = [
    'depends' => [
        \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowDefaultValues::class,
    ],
    'before' => [
        \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class,
    ],
];

EOS
        );

        sort($inlineFields);
        array_walk($inlineFields, function (&$value) {
            $value .= '_parent';
        });
        $supportedInlineParentFields = var_export($inlineFields, true);

        $this->addPhpFile(
            'Classes/Form/FormDataProvider/TcaColPosItem.php',
<<<EOS
namespace MASK\Mask\Form\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;

class TcaColPosItem implements FormDataProviderInterface
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
            || 999 !== (int)\$result['databaseRow']['colPos']
            || ((empty(\$result['inlineParentUid'])
                    || !in_array(\$result['inlineParentConfig']['foreign_field'], \$this->supportedInlineParentFields, true))
                && empty(array_filter(array_intersect_key(\$result['databaseRow'], array_flip(\$this->supportedInlineParentFields))))
            )
        ) {
            return \$result;
        }

        if (!is_array(\$result['processedTca']['columns']['colPos']['config']['items'])) {
            \$result['processedTca']['columns']['colPos']['config']['items'] = [];
        }
        array_unshift(
            \$result['processedTca']['columns']['colPos']['config']['items'],
            [
                'LLL:EXT:mask/Resources/Private/Language/locallang_db.xlf:tt_content.colPos.nestedContentColPos',
                \$result['databaseRow']['colPos'],
            ]
        );
        unset(\$result['processedTca']['columns']['colPos']['config']['itemsProcFunc']);

        return \$result;
    }
}

EOS
        );
    }
}
