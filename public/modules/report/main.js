
$(document).ready(function() {
$("#loadcustomer").html('');
$("#loadevent").html('');
$("#loadbranch").html('');
$("#loadcategory").html('');
$("#loadpackage").html('');
$("#loadcalculate").html('');
$("#loadcalculateheader").html('');
$("#loadcalculateresult").html('');


    $("#branch").change( function() {
    $("#loadcustomer").html('<img alt" src="'+ IMG_SPINNER +'">');
        $.post(
            BASE_URL + '/customer/searchbybranch',
        {
            branch_id : $(this).val(),
            _token : CSRF_TOKEN
        },
        function( msg ) {
            $("#customer").html(msg).show();
            $("#loadcustomer").html('');
        });

        $("#loadevent").html('<img alt" src="'+ IMG_SPINNER +'">');
        $.post(
            BASE_URL + '/event/searchbybranch',
        {
            branch_id : $(this).val(),
            _token : CSRF_TOKEN
        },
        function( msg ) {
            $("#event").html(msg).show();
            $("#loadevent").html('');
        });

    });

    //CUSTOMER//
    $("#customer").change( function() {
    $("#loadbranch").html('<img alt" src="'+ IMG_SPINNER +'">');
        $.post(
            BASE_URL + '/customer/setbranch',
        {
            customer_id : $(this).val(),
            _token : CSRF_TOKEN
        },
        function(data) {
            var json = JSON.parse(data);
            // console.log(json.status);
            if(json.status == true){
                // console.log(json.output);
                $('#branch').val(json.output);
                $("#loadbranch").html('');
            }else{
                swal({
                        title: "Warning",
                        text: json.output,
                        type: "warning",
                        showConfirmButton: "btn-warning"
                    });
            }
        });
    });
    //CUSTOMER//

    //TREATMENT//
    $("#treatment").change( function() {
    $("#loadcategory").html('<img alt" src="'+ IMG_SPINNER +'">');
        // Category //
        var treatment_id = $(this).val();
        $.post(
            BASE_URL + '/treatmentcategory/searchbyparent',
        {
            treatment_id : treatment_id,
            _token : CSRF_TOKEN
        },
        function(msg) {
            if(msg != '<option value="0">Choose Treatment Category</option>'){
                $("#category").html(msg).show();
                $("#category").prop('disabled',false);
                $("#package").html('').show();
                $("#loadcategory").html('');
            }else{
                $("#category").html(msg).show();
                $("#category").prop('disabled',true);
                $("#loadcategory").html('<span class="help-block">Data is not specific</span>');
                $("#loadpackage").html('<img alt" src="'+ IMG_SPINNER +'">');

                //Package //
                $.post(
                    BASE_URL + '/treatmentpackage/searchbytreatment',
                {
                    treatment_id : treatment_id,
                    _token : CSRF_TOKEN
                },
                function(data) {
                    $("#package").html(data).show();
                    $("#package").prop('disabled',false);
                    $("#loadpackage").html('');
                });
                //Package //
            }
        });

        // Category //
    });
    //TREATMENT//


    //CATEGORY//
    $("#category").change( function() {
    $("#loadpackage").html('<img alt" src="'+ IMG_SPINNER +'">');
        var category_id     = $(this).val();
        var treatment_id    = $('#treatment').val();
        $.post(
            BASE_URL + '/treatmentpackage/searchbycategory',
        {
            category_id     : category_id,
            treatment_id    : treatment_id,
            _token          : CSRF_TOKEN
        },
        function(msg) {
            if(msg != '<option value="0">Choose Treatment Package</option>'){
                $("#package").html(msg).show();
                $("#package").prop('disabled',false);
                $("#loadpackage").html('');
            }else{
                $("#package").html(msg).show();
                $("#package").prop('disabled',true);
                $("#loadpackage").html('Package not Found.');
            }
        });

    });
    //CATEGORY//



    //PACKAGE//
    $("#package").change( function() {
    $("#loaddetail").html('<img alt" src="'+ IMG_SPINNER +'">');
        // Category //
        var package_id    = $(this).val();
        $.post(
            BASE_URL + '/treatmentpackage/getdetailpackage',
        {
            package_id    : package_id,
            _token          : CSRF_TOKEN
        },
        function(data) {
            var json = JSON.parse(data);
            if(json.status == true){
                $('#price_display').val(json.output.price_display);
                $('#price').val(json.output.price);
                $('#total').val(json.output.price_display);
                $("#loaddetail").html('');
            }else{
                swal({
                        title: "Warning",
                        text: json.output,
                        type: "warning",
                        showConfirmButton: "btn-warning"
                    });
            }
        });
    });
    //PACKAGE//

});


// SHOW REPORT EVENT //
$("#a-ShowReportEvent").click(function(){
    App.startPageLoading({animate: true});
    var branch              = $('#branch').val();
    var events               = $('#event').val();
    var from                = $('#from').val();
    var to                  = $('#to').val();
    var param               = btoa(branch + " | " + events + " | " + from + " | " + to + " | show" );
    window.location.href=BASE_URL + "/report/event/retrieve/" + param;
});
// SHOW REPORT EVENT //

// EXCEL REPORT EVENT //
$("#a-ExcelReportEvent").click(function(){
    App.startPageLoading({animate: true});
    var branch              = $('#branch').val();
    var events               = $('#event').val();
    var from                = $('#from').val();
    var to                  = $('#to').val();
    var param               = btoa(branch + " | " + events + " | " +  from + " | " + to + " | excel" );

    window.setTimeout(function() {
        App.stopPageLoading();
    }, 2000);
    window.open(BASE_URL + "/report/event/retrieve/" + param,"_blank");
});
// EXCEL REPORT EVENT //

// PDF REPORT EVENT //
$("#a-PDFReportEvent").click(function(){
    App.startPageLoading({animate: true});
    var branch              = $('#branch').val();
    var events               = $('#event').val();
    var from                = $('#from').val();
    var to                  = $('#to').val();
    var param               = btoa(branch + " | " + events + " | " +  from + " | " + to + " | pdf" );

    window.setTimeout(function() {
        App.stopPageLoading();
    }, 2000);
    window.open(BASE_URL + "/report/event/retrieve/" + param,"_blank");

});
// PDF REPORT EVENT //







// SHOW REPORT PROFITLOSS //
$("#a-ShowReportProfitLoss").click(function(){
    App.startPageLoading({animate: true});
    var branch              = $('#branch').val();
    var month               = $('#month').val();
    var year                = $('#year').val();
    var param               = btoa(branch + " | " + month + " | " + year + " | show" );
    window.location.href=BASE_URL + "/report/profitloss/retrieve/" + param;
});
// SHOW REPORT PROFITLOSS //

// EXCEL REPORT PROFITLOSS //
$("#a-ExcelReportProfitLoss").click(function(){
    App.startPageLoading({animate: true});
    var branch              = $('#branch').val();
    var month               = $('#month').val();
    var year                = $('#year').val();
    var param               = btoa(branch + " | " + month + " | " + year + " | excel" );

    window.setTimeout(function() {
        App.stopPageLoading();
    }, 2000);
    window.open(BASE_URL + "/report/profitloss/retrieve/" + param,"_blank");
});
// EXCEL REPORT PROFITLOSS //

// PDF REPORT PROFITLOSS //
$("#a-PDFReportProfitLoss").click(function(){
    App.startPageLoading({animate: true});
    var branch              = $('#branch').val();
    var month               = $('#month').val();
    var year                = $('#year').val();
    var param               = btoa(branch + " | " + month + " | " + year + " | pdf" );

    window.setTimeout(function() {
        App.stopPageLoading();
    }, 2000);
    window.open(BASE_URL + "/report/profitloss/retrieve/" + param,"_blank");
});
// PDF REPORT PROFITLOSS //