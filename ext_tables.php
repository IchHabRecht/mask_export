<?php

defined('TYPO3') || die();

call_user_func(static function () {
    $maskElements = array_filter(
        $GLOBALS['TCA']['tt_content']['types'] ?? [],
        static function ($key) {
            return strpos((string)$key, 'mask_') === 0;
        },
        ARRAY_FILTER_USE_KEY
    );

    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        'TCEFORM.tt_content.CType.removeItems := addToList('
        . implode(',', array_keys($maskElements))
        . ')'
    );
});
