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

trait LanguageAwareTrait
{
    /**
     * @var array
     */
    protected $labels = [];

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param string $fileIdentifier
     * @param string $id
     * @param string $source
     */
    protected function addLabel($fileIdentifier, $id, $source)
    {
        if (!isset($this->labels[$fileIdentifier])) {
            $this->labels[$fileIdentifier] = [];
        }

        $this->labels[$fileIdentifier][$id] = [
            'id' => $id,
            'source' => $source,
        ];
    }
}
