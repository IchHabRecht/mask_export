<?php

declare(strict_types=1);

namespace IchHabRecht\MaskExport\FileCollection;

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

use IchHabRecht\MaskExport\Aggregate\LanguageAwareInterface;

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

        $files = [];
        foreach ($labels as $file => $body) {
            $files[$file] = $this->generateLanguageFile($body);
        }

        return $files;
    }

    /**
     * @param array $body
     * @return string
     */
    protected function generateLanguageFile(array $body)
    {
        $domDocument = new \DOMDocument('1.0', 'utf-8');
        $domDocument->formatOutput = true;

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
            $transUnit->appendChild($source);
            $domBody->appendChild($transUnit);
        }

        return $domDocument->saveXML();
    }
}
