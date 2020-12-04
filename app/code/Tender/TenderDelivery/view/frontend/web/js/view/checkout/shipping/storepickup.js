define([
    'jquery',
    "underscore",
    'ko',
    'Magento_Checkout/js/model/quote',
    'uiComponent',
    'mage/calendar',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'tenderCalendarPicker'
], function ($, _, ko, quote, Component, calendar, modal,$t,tenderCalendarPicker) {
    'use strict';
    var show_hide_custom_blockConfig = window.checkoutConfig.show_hide_custom_block;
   
    
    $(document).ready(function () {
        
        
        
        $(document).on('change','.storepickup-shipping-method select',function () {
            $('.store_info ul li').hide();
            if ($(this).val() != "") {
                $(document).find('.storepickup_checked').val('1');
                $('li.store_info_'+$(this).val()).show();
            } else {
                $(document).find('.storepickup_checked').val('0');
            }
        });
        
        $(document).on('click','#click-me',function () {
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: 'Stores in Map',
                buttons: [{
                    text:'Close',
                    class: '',
                    click: function () {
                        this.closeModal();
                    }
                }]
            };
            
            var popup = modal(options, $('#ci-storepickup-popup-modal'));
            $("#ci-storepickup-popup-modal").modal("openModal");
           
        });
    });
    
    return Component.extend({
        defaults: {
            formSelector: '#checkout-step-shipping_method button',
            template: 'Tender_TenderDelivery/checkout/shipping/storepickup',
            storepickConfig: window.checkoutConfig.storepick_config,
            storepickConfigEncode: window.checkoutConfig.storepick_config_encode,
            storepickInfo: window.checkoutConfig.storepick_info,
            deliveryTimeInterval: window.checkoutConfig.delivery_time_interval
        },
        
        initObservable: function () {
                this._super();
                this.selectedMethod = ko.computed(function () {
                var method = quote.shippingMethod();
                var selectedMethod = method != null ? method.carrier_code + '_' + method.method_code : null;
                return selectedMethod;
            }, this);

            return this;
        },
        
        initialize: function () {
            this._super();
            ko.bindingHandlers.datetimepicker = {
                init: function (element, valueAccessor, allBindingsAccessor) {
                    var $el = $(element);
                    var format = 'yy-mm-dd';

                    //initialize datetimepicker with some optional options
                    var options = { minDate: 0, dateFormat:format, hourMin: parseInt(window.checkoutConfig.hour_min), hourMax: parseInt(window.checkoutConfig.hour_max) };
                    $el.datetimepicker(options);

                    var writable = valueAccessor();
                    if (!ko.isObservable(writable)) {
                        var propWriters = allBindingsAccessor()._ko_property_writers;
                        if (propWriters && propWriters.datetimepicker) {
                            writable = propWriters.datetimepicker;
                        } else {
                            return;
                        }
                    }
                    writable($(element).datetimepicker("getDate"));

                },
                update: function (element, valueAccessor) {
                    var widget = $(element).data("DateTimePicker");
                    //when the view model is updated, update the widget
                    if (widget) {
                        var date = ko.utils.unwrapObservable(valueAccessor());
                        widget.date(date);
                    }
                }
            };
            
            ko.bindingHandlers.tenderCalendarPicker = {
                init: function (element, valueAccessor, allBindingsAccessor) {
                    var $el = $(element);
                    var format = 'dd-mm-yy';

                    $('#mobile_delivery_calender').calendarPicker({
                        monthNames:[$t('Jan'), $t('Feb'),$t('Mar'),$t('Apr'),$t('May'),$t('Jun'),$t('Jul'),$t('Aug'),$t('Sep'),$t('Oct'),$t('Nov'),$t('December')],
                        dayNames: [$t('Sun'),$t('Mon'),$t('Tue'),$t('Wed'),$t('Thu'),$t('Fri'),$t('Sat')],
                        useWheel:false,
                        //callbackDelay:500,
                        years:1,
                        months:1,
                        days:3,
                        showDayArrows:true,
                        callback:function(cal) {
                            var formatted = $.datepicker.formatDate(format, new Date(cal.currentDate));
                            $("#mobile_delivery_date").val(formatted);
                        }
                    });
                }
            };

            return this;
        },
        canVisibleBlock: show_hide_custom_blockConfig
    });

});
