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

class InlineContentColPosAggregate extends AbstractAggregate implements LanguageAwareInterface, PhpAwareInterface
{
    use LanguageAwareTrait;
    use PhpAwareTrait;

    /**
     * @var string
     */
    protected $languageFileIdentifier = 'locallang_db.xlf';

    /**
     * @var string
     */
    protected $languageFilePath = 'Resources/Private/Language/';

    /**
     * Adds content elements to the newContentElementWizard
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
        \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class,
    ],
];

EOS
        );

        $this->addPhpFile(
            'Classes/Form/FormDataProvider/TcaColPosItem.php',
<<<EOS
namespace MASK\Mask\Form\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Lang\LanguageService;

class TcaColPosItem implements FormDataProviderInterface
{
    /**
     * @param array \$result
     * @return array
     */
    public function addData(array \$result)
    {
        if (!empty(\$result['databaseRow']['colPos']) 
            || empty(\$result['processedTca']['columns']['colPos']['config']['items'])
            || '999' !== \$result['processedTca']['columns']['colPos']['config']['items'][0][1]
        ) {
            return \$result;
        }

        \$result['processedTca']['columns']['colPos']['config']['items'][0][0] = \$this->getLanguageService()->sL('LLL:EXT:mask/{$this->languageFilePath}{$this->languageFileIdentifier}:tt_content.colPos.nestedContentColPos');

        return \$result;
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return \$GLOBALS['LANG'];
    }
}

EOS
        );
    }

    /**
     * @return array
     */
    protected function getAvailableInlineFields()
    {
        $inlineFields = [];
        foreach ($this->maskConfiguration as $table => $configuration) {
            if (empty($configuration['tca'])) {
                continue;
            }
            foreach ($configuration['tca'] as $field => $fieldConfiguration) {
                if ('inline' === $fieldConfiguration['config']['type']
                    && 'tt_content' === $fieldConfiguration['config']['foreign_table']
                ) {
                    $inlineFields[] = $field;
                }
            }
        }

        return $inlineFields;
    }
}
