/**
 * Created by Barind on 6/8/17.
 */

var FormInputMask = function () {

    var handleInputMasks = function () {

        $(".mask_npwp").inputmask({
            "mask": "99.999.999.9-999.999",
            placeholder: "" // remove underscores
        });

        $(".mask_ktp").inputmask({
            "mask":    "9999999999999999",
            placeholder: "" // remove underscores
        });

        $(".mask_zipcode").inputmask({
            "mask":    "99999",
            placeholder: "" // remove underscores
        });

        $(".mask_phone").inputmask({
            "mask":    "999999999999999",
            placeholder: "" // remove underscores
        });

        $(".stock").inputmask({
            "mask":    "999",
            placeholder: "" // remove underscores
        });

        $(".rp").inputmask("99.999.999",{
            numericInput: true,
            rightAlignNumerics: false,
        });


        $(".discount").inputmask("999",{
            numericInput: true,
            rightAlignNumerics: false,
        });

        $(".barcode").inputmask({
            "mask":    "9999999999999",
            placeholder: "*************" // FORMAT //
        });

        $(".rupiah").inputmask("Rp 999.999.999",{
            numericInput: true,
            rightAlignNumerics: false,
        });

    }


    return {
        //main function to initiate the module
        init: function () {
            handleInputMasks();
        }
    };

}();

if (App.isAngularJsApp() === false) {
    jQuery(document).ready(function() {
        FormInputMask.init(); // init metronic core componets
    });
}
