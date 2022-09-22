<?php

declare(strict_types=1);

namespace IchHabRecht\MaskExport\Command;

use IchHabRecht\MaskExport\Controller\ExportController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extensionmanager\Domain\Model\Extension;

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

/**
 * This class provides a CLI command for exporting the mask elements
 */
class ExportCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setHelp('Export all mask elements')
            ->addArgument(
                'vendorName',
                InputArgument::REQUIRED,
                'Vendor name for the new extension'
            )
            ->addArgument(
                'extensionName',
                InputArgument::REQUIRED,
                'Extension name for the new extension'
            )
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Path for the extension to be written. Defaults to the usual extension path.'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ['path' => $path, 'extensionName' => $extensionName, 'vendorName' => $vendorName] = $input->getArguments();
        GeneralUtility::setIndpEnv('TYPO3_REQUEST_URL', '');
        Bootstrap::initializeBackendUser();

        $request = GeneralUtility::makeInstance(Request::class);
        $request->setControllerName('Export');
        $request->setControllerActionName('install');
        $request->setArguments([
            'vendorName' => $input->getArgument('vendorName'),
            'extensionName' => $input->getArgument('extensionName'),
            'elements' => [],
        ]);
        if ($path) {
            $request->setArgument('path', $path . '/');
        }

        $controller = GeneralUtility::makeInstance(ExportController::class);
        $elements = $controller->getAvailableElements();
        $GLOBALS['BE_USER']->uc['mask_export'] = [
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'elements' => implode(',', array_keys($elements)),
        ];
        try {
            $controller->processRequest($request);
        } catch (StopActionException $e) {
            // Ignore this Exception because it is for web requests only
        }
        $output->writeln(sprintf(
            'Extension written to %s%s',
            $path ? realpath($path) . '/' : Extension::returnInstallPaths()['Local'],
            $input->getArgument('extensionName')
        ));

        return Command::SUCCESS;
    }
}
