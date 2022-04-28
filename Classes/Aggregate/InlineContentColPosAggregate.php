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
            ,
            PhpAwareInterface::PHPFILE_DEFINED_TYPO3_MODE | PhpAwareInterface::PHPFILE_CLOSURE_FUNCTION
        );

        $flattenedInlineFields = [];
        array_walk_recursive($inlineFields, static function ($field) use (&$flattenedInlineFields) {
            $flattenedInlineFields[] = $field . '_parent';
        });
        sort($flattenedInlineFields);
        $supportedInlineParentFields = ArrayUtility::arrayExport($flattenedInlineFields);

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

        if (!is_array(\$result['processedTca']['columns']['colPos']['config']['items'] ?? null)) {
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
