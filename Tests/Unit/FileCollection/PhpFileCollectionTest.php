<?php

declare(strict_types=1);

namespace IchHabRecht\MaskExport\Tests\Unit\FileCollection;

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
use IchHabRecht\MaskExport\FileCollection\PhpFileCollection;
use IchHabRecht\MaskExport\FlagResolver\PhpFileFlagResolver;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Service\DependencyOrderingService;

class PhpFileCollectionTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getFilesCombinesPhpFileFlags()
    {
        $aggregateOne = $this->prophesize(PhpAwareInterface::class);
        $aggregateOne->getPhpFiles()->willReturn(
            [
                'ext_tables.php' => [
                    'content' => '',
                    'flags' => PhpAwareInterface::PHPFILE_CLOSURE_FUNCTION,
                ],
            ]
        );
        $aggregateTwo = $this->prophesize(PhpAwareInterface::class);
        $aggregateTwo->getPhpFiles()->willReturn(
            [
                'ext_tables.php' => [
                    'content' => '',
                    'flags' => PhpAwareInterface::PHPFILE_DEFINED_TYPO3_MODE,
                ],
            ]
        );
        $aggregateCollection = [
            $aggregateOne->reveal(),
            $aggregateTwo->reveal(),
        ];

        $dependencyOrderingService = new DependencyOrderingService();
        $phpFileFlagResolver = new PhpFileFlagResolver($dependencyOrderingService);

        $phpFileCollection = new PhpFileCollection($aggregateCollection, $phpFileFlagResolver);
        $files = $phpFileCollection->getFiles();

        $this->assertArrayHasKey('ext_tables.php', $files);
        $this->assertStringContainsString('defined(\'TYPO3_MODE\') || die();', $files['ext_tables.php']);
        $this->assertStringContainsString('call_user_func(static function () {', $files['ext_tables.php']);
    }
}
