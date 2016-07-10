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

use CPSIT\MaskExport\Aggregate\LanguageAwareInterface;

/**
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class LanguageFileCollection extends AbstractFileCollection
{
    /**
     * @return array
     */
    protected function processAggregateCollection()
    {
        $labels = [];
        foreach ($this->aggregateCollection as $aggregate) {
            if (!$aggregate instanceof LanguageAwareInterface) {
                continue;
            }

            $labels = array_replace_recursive($labels, $aggregate->getLabels());
        }

        foreach ($labels as $file => $body) {
            $this->files[$file] = $this->generateLanguageFile($body);
        }
    }

    /**
     * @param array $body
     * @return string
     */
    protected function generateLanguageFile(array $body)
    {
        $domDocument = new \DOMDocument('1.0', 'utf-8');
        $domDocument->formatOutput = TRUE;

        $domFile = $domDocument->createElement('file');
        $domFile->appendChild(new \DOMAttr('source-language', 'en'));
        $domFile->appendChild(new \DOMAttr('datatype', 'plaintext'));
        $domFile->appendChild(new \DOMAttr('original', 'messages'));
        $domFile->appendChild(new \DOMAttr('date', date('c')));
        $domFile->appendChild(new \DOMAttr('product-name', 'mask'));
        $domFile->appendChild($domDocument->createElement('header'));
        $domBody = $domDocument->createElement('body');
        $domFile->appendChild($domBody);

        $xliff = $domDocument->createElement('xliff');
        $xliff->appendChild(new \DOMAttr('version', '1.0'));
        $xliff->appendChild($domFile);
        $domDocument->appendChild($xliff);

        foreach ($body as $unit) {
            $source = $domDocument->createElement('source');
            $source->appendChild($domDocument->createTextNode($unit['source']));
            $transUnit = $domDocument->createElement('trans-unit');
            $transUnit->appendChild(new \DOMAttr('id', $unit['id']));
            $transUnit->appendChild(new \DOMAttr('xml:space', 'preserve'));
            $transUnit->appendChild($source);
            $domBody->appendChild($transUnit);
        }

        return $domDocument->saveXML();
    }
}
