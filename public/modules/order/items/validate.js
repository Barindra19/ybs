var FormValidation = function () {

    // FORM VALIDATION //
    var handleFormStep1 = function() {

            var form = $('#form_add');
            var error = $('.alert-danger', form);
            var success = $('.alert-success', form);

            form.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",  // validate all fields including form hidden input
                rules: {
                    // customer: {
                    //     required: true,
                    //     min: 1
                    // },
                    grandtotal: {
                        required: true
                    },
                    payment: {
                        required: true
                    },
                    payment_type: {
                        required: true,
                        min: 1
                    },


                },
                message: {
                    // customer: {
                    //     required: "Customer Wajib diisi",
                    //     min: "Customer Wajib diisi"
                    // },
                    grandtotal: {
                        required: "Subtotal  diisi"
                    },
                    payment: {
                        required: "Payment  diisi",
                        min: "Payment  diisi"
                    },
                    payment_type: {
                        required: "Metode Pembayaran Wajib  diisi",
                        min: "Metode Pembayaran Wajib diisi"
                    }
                },

                invalidHandler: function (event, validator) { //display error alert on form submit
                    success.hide();
                    error.show();
                    App.scrollTo(error, -200);
                },

                errorPlacement: function (error, element) { // render error placement for each input type
                    var icon = $(element).parent('.input-icon').children('i');
                    icon.removeClass('fa-check').addClass("fa-warning");
                    icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
                },

                highlight: function (element) { // hightlight error inputs
                    $(element)
                        .closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
                },

                unhighlight: function (element) { // revert the change done by hightlight

                },

                success: function (label, element) {
                    var icon = $(element).parent('.input-icon').children('i');
                    $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                    icon.removeClass("fa-warning").addClass("fa-check");
                },

                submitHandler: function (form) {
                    success.show();
                    error.hide();
                    form[0].submit(); // submit the form
                }
            });
    }
    // FORM VALIDATION //

    //REPAY //
    var handleRepay = function() {
        // for more info visit the official plugin documentation:
        // http://docs.jquery.com/Plugins/Validation
        var form1 = $('#form_repay');
        var error1 = $('.alert-danger', form1);
        var success1 = $('.alert-success', form1);

        form1.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "", // validate all fields including form hidden input
            messages: {
                payment_type: {
                    required: "Repay is required"
                },
                full_payment: {
                    required: "Repay is required"
                }
            },
            rules: {
                payment_type: {
                    required: true
                },
                full_payment: {
                    required: true
                }
            },

            invalidHandler: function(event, validator) { //display error alert on form submit
                success1.hide();
                error1.show();
                App.scrollTo(error1, -200);
            },

            errorPlacement: function(error, element) {
                if (element.is(':checkbox')) {
                    error.insertAfter(element.closest(".md-checkbox-list, .md-checkbox-inline, .checkbox-list, .checkbox-inline"));
                } else if (element.is(':radio')) {
                    error.insertAfter(element.closest(".md-radio-list, .md-radio-inline, .radio-list,.radio-inline"));
                } else {
                    error.insertAfter(element); // for other inputs, just perform default behavior
                }
            },

            highlight: function(element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').addClass('has-error'); // set error class to the control group
            },

            unhighlight: function(element) { // revert the change done by hightlight
                $(element)
                    .closest('.form-group').removeClass('has-error'); // set error class to the control group
            },

            success: function(label) {
                label
                    .closest('.form-group').removeClass('has-error'); // set success class to the control group
            },

            submitHandler: function(form) {
                success1.show();
                error1.hide();
                form[0].submit(); // submit the form
            }
        });
    }
    // REPAY //

    return {
        //main function to initiate the module
        init: function () {

            handleFormStep1();
            handleRepay();

        }

    };

}();

jQuery(document).ready(function() {
    FormValidation.init();
});
