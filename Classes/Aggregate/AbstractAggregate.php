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

abstract class AbstractAggregate
{
    /**
     * @var array
     */
    protected $maskConfiguration;

    /**
     * @var array
     */
    protected $systemTables = [
        'pages' => 'pages',
        'tt_content' => 'tt_content',
        'sys_file_reference' => 'sys_file_reference',
        'mask_export' => 'mask_export',
    ];

    /**
     * @var string
     */
    protected $languageFilePath = 'Resources/Private/Language/';

    /**
     * @var string
     */
    protected $pageTSConfigFilePath = 'Configuration/TsConfig/Page/';

    /**
     * @var string
     */
    protected $typoScriptFilePath = 'Configuration/TypoScript/';

    /**
     * @param array $maskConfiguration
     */
    public function __construct(array $maskConfiguration)
    {
        $this->maskConfiguration = $maskConfiguration;
        $this->removeHiddenContentElements();
        $this->removeCoreFields();
        $this->process();
    }

    /**
     * Evaluates the configuration and stores necessary Interface information
     */
    abstract protected function process();

    /**
     * Remove hidden content elements from configuration
     */
    protected function removeHiddenContentElements()
    {
        if (empty($this->maskConfiguration['tt_content']['elements'])) {
            return;
        }

        $this->maskConfiguration['tt_content']['elements'] = array_filter(
            $this->maskConfiguration['tt_content']['elements'],
            static function (array $element) {
                return empty($element['hidden']);
            }
        );
    }

    /**
     * Remove core fields from configuration
     */
    protected function removeCoreFields()
    {
        foreach ($this->maskConfiguration as $table => $configuration) {
            if (empty($configuration['tca'])) {
                continue;
            }
            $this->maskConfiguration[$table]['tca'] = array_filter(
                $this->maskConfiguration[$table]['tca'],
                static function ($field) {
                    return empty($field['coreField']);
                }
            );
        }
    }

    /**
     * @param string $string
     * @return string
     */
    protected function escapeMaskExtensionKey($string)
    {
        return preg_replace('/(m)ask/i', '${\\1ask}', $string);
    }
}
