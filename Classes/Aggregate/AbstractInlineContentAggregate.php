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

abstract class AbstractInlineContentAggregate extends AbstractAggregate
{
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
                    if (empty($inlineFields[$table])) {
                        $inlineFields[$table] = [];
                    }
                    $inlineFields[$table][] = $field;
                }
            }
        }

        return $inlineFields;
    }
}
