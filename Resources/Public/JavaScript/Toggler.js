/**
 * Module: TYPO3/CMS/MaskExport/Toggler
 */
define(['jquery'], function($) {
    'use strict';

    $(function() {
        $('.panel .btn-toggle').on('click', function() {
            $(this).closest('.panel').find('.panel-collapse').each(function() {
                $(this).collapse('toggle');
            });
        });
    });
});
