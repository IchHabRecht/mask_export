<?php

declare(strict_types=1);

namespace IchHabRecht\MaskExport\Tests\Functional\Controller\FlagResolver;

/*
 * This file is part of the TYPO3 extension mask_export.
 *
 * (c) 2019 Nicole Cordes <typo3@cordes.co>, biz-design
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

require_once __DIR__ . '/../AbstractExportControllerTestCase.php';

use IchHabRecht\MaskExport\Tests\Functional\Controller\AbstractExportControllerTestCase;

class PhpFileFlagResolverTest extends AbstractExportControllerTestCase
{
    /**
     * @return array
     */
    public function closureFunctionIsAppliedDataProvider()
    {
        return [
            'ext_localconf.php' => [
                '/ext_localconf\.php/',
            ],
            'ext_tables.php' => [
                '/ext_tables\.php/',
            ],
            'Configuration/TCA/Overrides/*' => [
                '/Configuration\/TCA\/Overrides\/[^.]+\.php/',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider closureFunctionIsAppliedDataProvider
     * @param string $filePattern
     */
    public function closureFunctionIsApplied($filePattern)
    {
        $files = [];
        foreach ($this->files as $file => $content) {
            if (preg_match($filePattern, $file)) {
                $files[$file] = $content;
            }
        }

        $this->assertNotEmpty($files);

        foreach ($files as $file => $fileContent) {
            $this->assertStringContainsString('call_user_func(static function () {', $fileContent);
        }
    }

    /**
     * @return array
     */
    public function definedTypo3ModeIsAppliedDataProvider()
    {
        return [
            'ext_localconf.php' => [
                '/ext_localconf\.php/',
            ],
            'ext_tables.php' => [
                '/ext_tables\.php/',
            ],
            'Configuration/TCA/Overrides/*' => [
                '/Configuration\/TCA\/Overrides\/[^.]+\.php/',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider definedTypo3ModeIsAppliedDataProvider
     * @param string $filePattern
     */
    public function definedTypo3ModeIsApplied($filePattern)
    {
        $files = [];
        foreach ($this->files as $file => $content) {
            if (preg_match($filePattern, $file)) {
                $files[$file] = $content;
            }
        }

        $this->assertNotEmpty($files);

        foreach ($files as $file => $fileContent) {
            $this->assertStringContainsString('defined(\'TYPO3_MODE\') || die();', $fileContent);
        }
    }
}
