<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') || die();

call_user_func(function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'IchHabRecht.mask_export',
        'tools',
        'mask_export',
        'after:MaskMask',
        [
            'Export' => 'list, save, download, install',
        ],
        [
            'access' => 'admin',
            'icon' => 'EXT:mask_export/Resources/Public/Icons/Extension.svg',
            'labels' => 'LLL:EXT:mask_export/Resources/Private/Language/locallang_mod.xlf',
        ]
    );

    $maskElements = array_filter(
        $GLOBALS['TCA']['tt_content']['types'] ?? [],
        function ($key) {
            return strpos((string)$key, 'mask_') === 0;
        },
        ARRAY_FILTER_USE_KEY
    );

    ExtensionManagementUtility::addPageTSConfig(
        'TCEFORM.tt_content.CType.removeItems := addToList('
        . implode(',', array_keys($maskElements))
        . ')'
    );
});
