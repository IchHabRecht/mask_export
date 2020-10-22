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

use TYPO3\CMS\Core\Utility\GeneralUtility;

class AggregateCollection
{
    /**
     * @var array
     */
    protected $aggregateClassNames;

    /**
     * @var AbstractAggregate[]
     */
    protected $collection;

    /**
     * @var array
     */
    protected $maskConfiguration;

    /**
     * @param array $aggregateClassNames
     * @param array $maskConfiguration
     */
    public function __construct(array $aggregateClassNames, array $maskConfiguration)
    {
        $this->aggregateClassNames = $aggregateClassNames;
        $this->maskConfiguration = $maskConfiguration;
    }

    /**
     * @return AbstractAggregate[]
     */
    public function getCollection()
    {
        if (null === $this->collection) {
            $this->initializeCollection();
        }

        return $this->collection;
    }

    /**
     * Initializes aggregate objects
     */
    protected function initializeCollection()
    {
        $this->collection = [];
        foreach ($this->aggregateClassNames as $className) {
            if (class_exists($className)) {
                $this->collection[$className] = GeneralUtility::makeInstance($className, $this->maskConfiguration);
            }
        }
    }
}
