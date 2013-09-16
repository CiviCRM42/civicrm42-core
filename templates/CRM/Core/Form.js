cj(function ($) {
    'use strict';
    $('.form-submit').on("click", function() {
      $('.form-submit').attr({value: ts('Processing'), disabled : 'disabled'});
      $('.crm-form-button-back ').closest('span').hide();
      $('.crm-form-button-cancel').closest('span').hide();
    });
});
