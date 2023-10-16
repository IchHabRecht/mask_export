<?php

return [
    'mask_export' => [
        'parent' => 'tools',
        'position' => ['after' => 'mask_module'],
        'access' => 'admin',
        'path' => '/module/MaskExport',
        'icon' => 'EXT:mask_export/Resources/Public/Icons/Extension.svg',
        'labels' => 'LLL:EXT:mask_export/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'MaskExport',
        'controllerActions' => [
            \IchHabRecht\MaskExport\Controller\ExportController::class => [
                'list', 'save', 'download', 'install'
            ]
        ],
    ],
];
