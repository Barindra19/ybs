var ComponentsDateTimePickers = function () {

    var handleDatePickers = function () {

        if (jQuery().datepicker) {
            /**
            * @author Barindra
            * add class="date-picker"  data-date-format="dd-mm-yyyy"
            */
            $('.date-picker').datepicker({
                autoclose: true,
                orientation: "bottom auto" // to fix month and year hidden from header
            });
        }

    }

    var handleTimePickers = function () {

        if (jQuery().timepicker) {
            $('.timepicker-default').timepicker({
                autoclose: true,
                showSeconds: true,
                minuteStep: 1
            });
        }
    }


    return {
        //main function to initiate the module
        init: function () {
            handleDatePickers();
            handleTimePickers();
        }
    };

}();

if (App.isAngularJsApp() === false) {
    jQuery(document).ready(function() {
        ComponentsDateTimePickers.init();
    });
}
