var FormValidation = function () {

    // FORM VALIDATION //
    var handleFormInfo = function() {

            var form = $('#form_info');
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
                    place_of_bird: {
                        required: true
                    },
                    date_of_bird: {
                        required: true
                    },
                    address: {
                        required: true
                    },
                    city: {
                        required: true
                    }
                },
                message: {
                    name: {
                        required: "Name required"
                    },
                    place_of_bird: {
                        required: "Place of bird required"
                    },
                    date_of_bird: {
                        required: "Date of bird required"
                    },
                    address: {
                        required: "Address required"
                    },
                    city: {
                        required: "City required"
                    }

                },

                invalidHandler: function (event, validator) { //display error alert on form submit
                    success.hide();
                    error.show();
                    App.scrollTo(error, -200);
                },

                errorPlacement: function (error, element) { // render error placement for each input type
                    var cont = $(element).parent('.input-group');
                    if (cont.size() > 0) {
                        cont.after(error);
                    } else {
                        element.after(error);
                    }
                },

                highlight: function (element) { // hightlight error inputs

                    $(element)
                        .closest('.form-group').addClass('has-error'); // set error class to the control group
                },

                unhighlight: function (element) { // revert the change done by hightlight
                    $(element)
                        .closest('.form-group').removeClass('has-error'); // set error class to the control group
                },

                success: function (label) {
                    label
                        .closest('.form-group').removeClass('has-error'); // set success class to the control group
                },

                submitHandler: function (form) {
                    success.show();
                    error.hide();
                    form.submit(); // submit the form
                }
            });
    }

    var handleFormAvatar = function() {

            var form = $('#form_avatar');
            var error = $('.alert-danger', form);
            var success = $('.alert-success', form);

            form.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",  // validate all fields including form hidden input
                rules: {
                    avatar: {
                        required: true
                    }
                },
                message: {
                    avatar: {
                        required: "Avatar required"
                    }

                },

                invalidHandler: function (event, validator) { //display error alert on form submit
                    success.hide();
                    error.show();
                    App.scrollTo(error, -200);
                },

                errorPlacement: function (error, element) { // render error placement for each input type
                    var cont = $(element).parent('.input-group');
                    if (cont.size() > 0) {
                        cont.after(error);
                    } else {
                        element.after(error);
                    }
                },

                highlight: function (element) { // hightlight error inputs

                    $(element)
                        .closest('.form-group').addClass('has-error'); // set error class to the control group
                },

                unhighlight: function (element) { // revert the change done by hightlight
                    $(element)
                        .closest('.form-group').removeClass('has-error'); // set error class to the control group
                },

                success: function (label) {
                    label
                        .closest('.form-group').removeClass('has-error'); // set success class to the control group
                },

                submitHandler: function (form) {
                    success.show();
                    error.hide();
                    loading();
                    form.submit(); // submit the form
                }

            });
    }

    // FORM VALIDATION //

    return {
        //main function to initiate the module
        init: function () {

            handleFormInfo(); // Member Info
            handleFormAvatar(); // Avatar

        }

    };

}();

jQuery(document).ready(function() {
    FormValidation.init();
});
