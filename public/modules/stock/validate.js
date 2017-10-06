var FormValidation = function () {

    // FORM VALIDATION //
    var handleForm = function() {

            var form = $('#form_stock');
            var error = $('.alert-danger', form);
            var success = $('.alert-success', form);

            form.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",  // validate all fields including form hidden input
                rules: {
                    name: {
                        required: true
                    },
                    cost_of_good: {
                        required: true
                    },
                    stock: {
                        required: true
                    },
                    selling_price: {
                        required: true
                    },
                    customer: {
                        required: true,
                        min: 1
                    },
                    brand: {
                        required: true
                    },
                    name_of_consignment: {
                        required: true
                    },
                    restock_date: {
                        required: true
                    }

                },
                message: {
                    name: {
                        required: "Name wajib diisi"
                    },
                    cost_of_good: {
                        required: "Modal wajib diisi"
                    },
                    stock: {
                        required: "Stock wajib diisi"
                    },
                    selling_price: {
                        required: "Harga Jual wajib diisi"
                    },
                    customer: {
                        required: "Supplier wajib diisi",
                        min: "Supplier wajib diisi"
                    },
                    brand: {
                        required: "Brand wajib diisi"
                    },
                    name_of_consignment: {
                        required: "Nama Consignment wajib diisi"
                    },
                    restock_date: {
                        required: "Tanggal Stock wajib diisi"
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

    return {
        //main function to initiate the module
        init: function () {

            handleForm();

        }

    };

}();

jQuery(document).ready(function() {
    FormValidation.init();
});
