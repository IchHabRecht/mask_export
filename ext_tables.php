<?php
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
});
