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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
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
