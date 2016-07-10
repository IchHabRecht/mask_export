<?php
namespace CPSIT\MaskExport\FileCollection;

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

use CPSIT\MaskExport\Aggregate\PlainTextFileAwareInterface;

/**
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PlainTextFileCollection extends AbstractFileCollection
{
    /**
     * @return array
     */
    protected function processAggregateCollection()
    {
        foreach ($this->aggregateCollection as $aggregate) {
            if (!$aggregate instanceof PlainTextFileAwareInterface) {
                continue;
            }

            $aggregateFiles = $aggregate->getPlainTextFiles();
            foreach ($aggregateFiles as $file => $content) {
                if (!isset($this->files[$file])) {
                    $this->files[$file] = '';
                }
                $this->files[$file] .= $content;
            }
        }
    }
}
