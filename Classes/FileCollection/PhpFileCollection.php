<?php
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

use IchHabRecht\MaskExport\Aggregate\PhpAwareInterface;

class PhpFileCollection extends AbstractFileCollection
{
    /**
     * @return array
     */
    protected function processAggregateCollection()
    {
        $fileInformation = $this->fetchFileInformation();

        return $this->processFileInformation($fileInformation);
    }

    /**
     * @return array
     */
    protected function fetchFileInformation()
    {
        $fileInformation = [];
        foreach ($this->aggregateCollection as $aggregate) {
            if (!$aggregate instanceof PhpAwareInterface) {
                continue;
            }

            $aggregateFiles = $aggregate->getPhpFiles();
            foreach ($aggregateFiles as $file => $information) {
                if (!isset($fileInformation[$file])) {
                    $fileInformation[$file] = [
                        'content' => '',
                        'flags' => 0,
                    ];
                }

                $fileInformation[$file]['content'] .= $information['content'];
                $fileInformation[$file]['flags'] |= $information['flags'];
            }
        }

        return $fileInformation;
    }

    /**
     * @param array $fileInformation
     * @return array
     */
    protected function processFileInformation(array $fileInformation)
    {
        $files = [];
        foreach ($fileInformation as $file => $information) {
            $content = $information['content'];
            $flags = $information['flags'];

            if (($flags & PhpAwareInterface::PHPFILE_CLOSURE_FUNCTION) === PhpAwareInterface::PHPFILE_CLOSURE_FUNCTION) {
                $content = <<<EOS
call_user_func(function () {

{$content}
});

EOS;
            }

            if (($flags & PhpAwareInterface::PHPFILE_DEFINED_TYPO3_MODE) === PhpAwareInterface::PHPFILE_DEFINED_TYPO3_MODE) {
                $content = <<<EOS
defined('TYPO3_MODE') || die();

{$content}

EOS;
            }

            $content = '<?php' . PHP_EOL . $content;

            $files[$file] = $content;
        }

        return $files;
    }
}
