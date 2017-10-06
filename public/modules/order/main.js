var counter = 1;
var rcounter = 1;

$(document).ready(function() {
$("#loadcustomer").html('');
$("#loadevent").html('');
$("#loadbranch").html('');
$("#loadcategory").html('');
$("#loadpackage").html('');
$("#loadcalculate").html('');
$("#loadcalculateheader").html('');
$("#loadcalculateresult").html('');

    $('#customer').select2({
        minimumInputLength: 3,
        ajax: {
            url: BASE_URL + '/customer/search_autocomplete',
            dataType: 'json',
            type: "POST",
            data: function (term) {
                return {
                    term            : term,
                    branch_id       : $('#branch').val(),
                    _token          : CSRF_TOKEN
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.name + " - " + item.branch,
                            slug: item.name,
                            id: item.id
                        }
                    })
                };
            }
        }
    });

    // $( "#customer" ).change(function() {
    //     App.startPageLoading({animate: true});
    //     $.ajax({
    //         url: BASE_URL + "/customer/get_detail",
    //         type: 'POST',
    //         data: {
    //             id              : $(this).val(),
    //             _token          : CSRF_TOKEN
    //         },
    //         success: function (data) {
    //             var json = JSON.parse(data);
    //             if(json.status == true){
    //                 $('#name').val(json.output.name);
    //                 $('#stock').val(json.output.stock);
    //                 $('#location').val(json.output.location);
    //                 $('#ModalItemsInfo').modal('show');
    //                 App.stopPageLoading();
    //             }else{
    //                 App.stopPageLoading();
    //             }
    //         },
    //         error: function(XMLHttpRequest, textStatus, errorThrown) {
    //             App.stopPageLoading();
    //         }
    //     });
    // });


    $("#branch").change( function() {
    App.startPageLoading({animate: true});
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
        App.stopPageLoading();

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
        function(msg,status) {
                console.log(status);
            if(status == 'success'){
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
            }else if(status == 'error'){
                toastr.error("Errpr! Please contact your web administrator.");
            }else if(status == 'timeout'){
                toastr.warning("Connection Timeout! Mohon check koneksi anda.");
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

    $("#btn-Add").on('click', function(e){
        $("#merk").select2('val', "0");
        $("#treatment").select2('val', "0");
        $("#price_display").val(0);
        $("#price").val(0);
        $("#discount").val(0);
        $("#nominaldiscount").val(0);
        $("#additional").val(0);
        $("#additional_description").val("");
        $("#total").val(0);
        $('#AddDetail').modal('show');
    });

});

function EditList(order_detail_id){
    $.post(
        BASE_URL + '/order/get_detailorderinfo',
    {
        order_detail_id              : order_detail_id,
        _token                      : CSRF_TOKEN
    },
    function(data) {
        var json = JSON.parse(data);
        if(json.status == true){
            $("#merk").select2('val', "0");
            $("#treatment").val(json.output.treatment);
            $("#treatmentcategory").val(json.output.treatment_category);
            $("#treatmentpackage").val(json.output.treatment_package);
            $("#price_display").val(json.output.price);
            $("#price").val(json.output.price);
            $("#discount").val(json.output.discount);
            $("#nominaldiscount").val(0);
            $("#additional").val(json.output.additional);
            $("#additional_description").val(json.output.additional_description);
            $("#total").val(json.output.total);
            $('#EditDetail').modal('show');
        }else{
            swal({
                    title: "Warning",
                    text: json.message,
                    type: "warning",
                    showConfirmButton: "btn-warning"
                });
        }
    });
}

function EditMerk(order_detail_id){
    var merk            = $('#merk'+order_detail_id).val();

    $.post(
        BASE_URL + '/order/edit_merk',
    {
        order_detail_id             : order_detail_id,
        merk                        : merk,
        _token                      : CSRF_TOKEN
    },
    function(data) {
        var json = JSON.parse(data);
        if(json.status == true){
            toastr.success(json.message);
        }else{
            swal({
                    title: "Warning",
                    text: json.message,
                    type: "warning",
                    showConfirmButton: "btn-warning"
                });
        }
    });
}

$("#discount").keyup(function() {
    var price       = $('#price').val();
    var discount    = $('#discount').val();
    var additional  = $('#additional').val();
    $("#loadcalculate").html('<img alt" src="'+ IMG_SPINNER +'">');

    $.post(
        BASE_URL + '/order/calculate',
    {
        price           : price,
        discount        : discount,
        additional      : additional,
        _token          : CSRF_TOKEN
    },
    function(output) {
        var json = JSON.parse(output);
        $("#loadcalculate").html('');
        $('#nominaldiscount').val(json.nominaldiscount);
        $('#total').val(json.total);
        $('#payment_left').val(json.total);
    });
});


$("#additional").keyup(function() {
    var price       = $('#price').val();
    var discount    = $('#discount').val();
    var additional  = $('#additional').val();
    $("#loadcalculate").html('<img alt" src="'+ IMG_SPINNER +'">');

    $.post(
        BASE_URL + '/order/calculate',
    {
        price           : price,
        discount        : discount,
        additional      : additional,
        _token          : CSRF_TOKEN
    },
    function(output) {
        var json = JSON.parse(output);
        $('#total').val(json.total);
        $('#nominaldiscount').val(json.nominaldiscount);
        $("#loadcalculate").html('');
        $('#payment_left').val(json.total);
    });
});


$("#discount_header").keyup(function() {
    var total_header        = $('#total_header').val();
    var discount_header     = $('#discount_header').val();
    var additional_header   = $('#additional_header').val();
    $("#loadcalculateheader").html('<img alt" src="'+ IMG_SPINNER +'">');

    $.post(
        BASE_URL + '/order/calculate_header',
    {
        total           : total_header,
        discount        : discount_header,
        additional      : additional_header,
        _token          : CSRF_TOKEN
    },
    function(output) {
        var json = JSON.parse(output);
        $("#loadcalculateheader").html('');
        $('#nominaldiscount_header').val(json.nominaldiscount);
        $('#grandtotal').val(json.total);
        $('#payment_left').val(json.total);
    });
});

$("#additional_header").keyup(function() {
    var total_header        = $('#total_header').val();
    var discount_header     = $('#discount_header').val();
    var additional_header   = $('#additional_header').val();
    $("#loadcalculateheader").html('<img alt" src="'+ IMG_SPINNER +'">');

    $.post(
        BASE_URL + '/order/calculate_header',
    {
        total           : total_header,
        discount        : discount_header,
        additional      : additional_header,
        _token          : CSRF_TOKEN
    },
    function(output) {
        var json = JSON.parse(output);
        $("#loadcalculateheader").html('');
        $('#nominaldiscount_header').val(json.nominaldiscount);
        $('#grandtotal').val(json.total);
        $('#payment_left').val(json.total);
    });
});


$("#btn-AddCustomer").click(function() {
    $('#ModalCustomer').modal('show');
});

$("#btn-SearchCustomer").click(function() {
        window.open(BASE_URL + "/customer/list_customer","","width=800,height=600");
        window.close();
});

function getDataParet(id){
    $("#loadcustomer").html('<img alt" src="'+ IMG_SPINNER +'">');
    $("#loadbranch").html('<img alt" src="'+ IMG_SPINNER +'">');
    $.post(
        BASE_URL + '/customer/getdetail',
    {
        customer        : id,
        _token          : CSRF_TOKEN
    },
    function(data) {
        var json = JSON.parse(data);
            $("#customer").html(json.customer_select).show();
            $("#loadcustomer").html('');

        $.post(
            BASE_URL + '/customer/setbranch',
        {
            customer_id : json.output.branch_id,
            _token : CSRF_TOKEN
        },
        function(data_branch) {
            var jsonbranch = JSON.parse(data_branch);
            // console.log(json.status);
            if(jsonbranch.status == true){
                // console.log(json.output);
                $('#branch').val(jsonbranch.output);
                $("#loadbranch").html('');
            }
        });
    });
}


$("#btn-AddCustomerNew").click(function() {
    $('#row-name').removeClass('has-error');
    $('#row-name').addClass('has-success');
    $('#row-address').removeClass('has-error');
    $('#row-address').addClass('has-success');
    $('#row-phone').removeClass('has-error');
    $('#row-phone').addClass('has-success');
    $('#row-mobile').removeClass('has-error');
    $('#row-mobile').addClass('has-success');
    $('#row-email').removeClass('has-error');
    $('#row-email').addClass('has-success');

    $.post(
        BASE_URL + '/customer/add_customer',
    $("#save-customer").serialize(), function(data) {
        var json = JSON.parse(data);
        console.log(json);
        if(json.status == true){
            $('#ModalCustomer').modal('hide');
            $("#customer").html(json.customer_select).show();
            $("#loadcustomer").html('');

        $.post(
            BASE_URL + '/customer/setbranch',
        {
            customer_id : json.output.branch_id,
            _token : CSRF_TOKEN
        },
        function(data_branch) {
            var jsonbranch = JSON.parse(data_branch);
            // console.log(json.status);
            if(jsonbranch.status == true){
                // console.log(json.output);
                $('#branch').val(jsonbranch.output);
                $("#loadbranch").html('');
            }
        });

            swal({
                    title: "Success",
                    text: json.message,
                    type: "success",
                    showConfirmButton: "btn-success"
                });


        }else{
            $('#row-'+json.field).addClass('has-error');
            toastr.error(json.message);
        }
    });
});

$("#btn-AddItem").click(function() {
    $.post(
        BASE_URL + '/order/save_item',
    $("#save-item").serialize(), function(data) {
        var json = JSON.parse(data);
        if(json.status == true){
            $('#AddDetail').modal('hide');
            swal({
                    title: "Success",
                    text: json.message,
                    type: "success",
                    showConfirmButton: "btn-success"
                });
                var trHTML      = '<tr id="row'+ json.output.id +'">';
                        trHTML += '<td>' + json.output.treatment + '</td>';
                        trHTML += '<td>' + json.output.merk + '</td>';
                        trHTML += '<td>' + json.output.category + '</td>';
                        trHTML += '<td>' + json.output.package + '</td>';
                        if(json.output.discount > 0){
                            trHTML += '<td><span class="label label-sm label-danger"> ' + json.output.nominaldiscount + '</span></td>';
                        }else{
                            trHTML += '<td>-</td>';
                        }
                        if(json.output.additional > 0){
                            trHTML += '<td><span class="label label-sm label-info"> ' + json.output.additional + '</span> <span class="label label-sm label-default">' + json.output.additional_description + '</span></td>';
                        }else{
                            trHTML += '<td>-</td>';
                        }
                            trHTML += '<td><span class="label label-sm label-success"> ' + json.output.total + '</span></td>';
                            trHTML += '<td id="imageUpload' + json.output.id + '">' + json.output.image + '</td>';
                        trHTML += '<td><a href="javascript:void(0)" class="btn btn-outline btn-circle red btn-sm black" onclick="deleteList(' + json.output.id + ')"><i class="fa fa-trash-o"></i> Delete </a><a href="javascript:void(0)" class="btn btn-outline btn-circle blue btn-sm black" onclick="Upload(' + json.output.id + ')"><i class="fa fa-cloud-upload"></i> Upload </a></td>';
                        trHTML += '</tr>';
                $('#order_detail').append(trHTML);
                $('#total_header').val(json.output.total_header);
                $('#grandtotal').val(json.output.total_header);
                $('#payment_left').val(json.output.total_header);
        }else{
            toastr.error(json.message)
            // $('#msgError').text();
            // $('#ShowErrMsg').modal('show');
        }
    });
});

function deleteList(id){
    $('#id_delete').val(id);
    $('#ConfirmDelete').modal('show');
}

$("#btn-DeleteRow").click(function() {
    var id              = $('#id_delete').val();

    $.post(
        BASE_URL + '/order/deletedetail',
    {
        id              : id,
        _token          : CSRF_TOKEN
    },
    function(data) {
        var json = JSON.parse(data);
        if(json.status == true){
            $('#row'+id).remove();
            $('#total_header').val(json.output.total_header);
            $('#grandtotal').val(json.output.total_header);
            $('#payment_left').val(json.output.total_header);
            swal({
                    title: "Success",
                    text: json.message,
                    type: "success",
                    showConfirmButton: "btn-success"
                });
        }else{
            swal({
                    title: "Warning",
                    text: json.message,
                    type: "warning",
                    showConfirmButton: "btn-warning"
                });
        }
    });
});


function Upload(id){
    $('#AddUploadImage').modal('show');
    var i;
    for(i = 1; i <= counter; i++){
        $("#row-" + i).remove();
    }
    counter = 1;
    rcounter = 1;

    $.post(
        BASE_URL + '/order/getdetail',
    {
        id              : id,
        _token          : CSRF_TOKEN
    },
    function(data) {
        var json = JSON.parse(data);
        if(json.status == true){
            $('#treatment_modal').val(json.output.treatment);
            $('#merk_modal').val(json.output.merk);
            $('#category_modal').val(json.output.category);
            $('#package_modal').val(json.output.package);
            $('#order_detail_id').val(id);
        }else{
            swal({
                    title: "Warning",
                    text: json.message,
                    type: "warning",
                    showConfirmButton: "btn-warning"
                });
        }
    });
}



$("#btn-addMoreItem").click(function() {
        var html = '<div class="form-group" id="row-' + counter + '">';
            html += '<label class="control-label col-md-3">Image ' + counter + '</label>';
            html += '<div class="col-md-3">';
            html += '    <div class="fileinput fileinput-new" data-provides="fileinput">';
            html += '        <div class="input-group input-large">';
            html += '            <div class="form-control uneditable-input input-fixed input-medium" data-trigger="fileinput">';
            html += '                <i class="fa fa-file fileinput-exists"></i>&nbsp;';
            html += '                <span class="fileinput-filename"> </span>';
            html += '            </div>';
            html += '            <span class="input-group-addon btn default btn-file">';
            html += '                <span class="fileinput-new"> Select file </span>';
            html += '                <span class="fileinput-exists"> Change </span>';
            html += '                <input type="file"  id="imageFile' + counter + '" name="imageFile[]"> </span>';
            html += '            <a href="javascript:;" class="input-group-addon btn red fileinput-exists" data-dismiss="fileinput"> Remove </a>';
            html += '        </div>';
            html += '    </div>';
            html += '</div>';
            html += '<div class="col-md-1">';
            html += '</div>';
            html += '<div class="col-md-2">';
            html += '<button type="button" class="btn btn-warning mt-ladda-btn ladda-button btn-circle" onclick="removeImage('+counter+')"><i class="fa fa-trash"></i> Delete</button>';
            html += '</div>';
            html += '</div> ';

        $("#UploadImage").append(html);
        counter++;
        rcounter++;
});

$("#btn-addMoreItemFinish").click(function() {
        var html = '<div class="row"><div class="col-md-12"><div class="form-group" id="row-' + counter + '">';
            html += '<label class="control-label col-md-3">Image ' + counter + '</label>';
            html += '<div class="col-md-3">';
            html += '    <div class="fileinput fileinput-new" data-provides="fileinput">';
            html += '        <div class="input-group input-large">';
            html += '            <div class="form-control uneditable-input input-fixed input-medium" data-trigger="fileinput">';
            html += '                <i class="fa fa-file fileinput-exists"></i>&nbsp;';
            html += '                <span class="fileinput-filename"> </span>';
            html += '            </div>';
            html += '            <span class="input-group-addon btn default btn-file">';
            html += '                <span class="fileinput-new"> Select file </span>';
            html += '                <span class="fileinput-exists"> Change </span>';
            html += '                <input type="file"  id="imageFile' + counter + '" name="imageFile[]"> </span>';
            html += '            <a href="javascript:;" class="input-group-addon btn red fileinput-exists" data-dismiss="fileinput"> Remove </a>';
            html += '        </div>';
            html += '    </div>';
            html += '</div>';
            html += '<div class="col-md-1">';
            html += '</div>';
            html += '<div class="col-md-2">';
            html += '<button type="button" class="btn btn-warning mt-ladda-btn ladda-button btn-circle" onclick="removeImage('+counter+')"><i class="fa fa-trash"></i> Delete</button>';
            html += '</div>';
            html += '</div></div></div> ';

        $("#UploadImageFinish").append(html);
        counter++;
        rcounter++;
});

function removeImage(id){
    if (rcounter>1){
        $("#row-" + id).remove();
        counter--;
        rcounter--;
    }
}


$("form#form_upload").submit(function(){

    var formData = new FormData(this);
    $.ajax({
        url: BASE_URL + '/order/upload',
        type: 'POST',
        data: formData,
        async: false,
        success: function (data) {
            var json = JSON.parse(data);
            if(json.status == true){
                $('#AddUploadImage').modal('hide');
                swal({
                        title: "Success",
                        text: json.message,
                        type: "success",
                        showConfirmButton: "btn-success"
                    });
                    var html = json.images;
                    $("#imageUpload"+json.order_detail_id).append(html);

            }else{
                $('#msgError').text(json.message);
                $('#ShowErrMsg').modal('show');
            }
        },
        cache: false,
        contentType: false,
        processData: false
    });

    return false;
});

$("form#form_upload_finish").submit(function(){

    var formData = new FormData(this);
    $.ajax({
        url: BASE_URL + '/order/upload_finish',
        type: 'POST',
        data: formData,
        async: false,
        success: function (data) {
            var json = JSON.parse(data);
            if(json.status == true){
                $('#AddUploadImage').modal('hide');
                swal({
                        title: "Success",
                        text: json.message,
                        type: "success",
                        showConfirmButton: "btn-success"
                    });
                    var html = json.images;
                    $("#imageUpload"+json.order_detail_id).append(html);

            }else{
                $('#msgError').text(json.message);
                $('#ShowErrMsg').modal('show');
            }
        },
        cache: false,
        contentType: false,
        processData: false
    });

    return false;
});

$( "#type" ).change(function() {
    if($(this).val() == 1){
        var grandtotal      = $('#grandtotal').val();
        $('#payment').val(grandtotal);
        $('#payment_left').val(0);
    }else{
        var grandtotal      = $('#grandtotal').val();
        $('#payment').val('');
        $('#payment_left').val(grandtotal);
    }


});

$("#payment").keyup(function() {
    var total           = $('#grandtotal').val();
    var payment         = $('#payment').val();

    $("#loadcalculateresult").html('<img alt" src="'+ IMG_SPINNER +'">');

    $.post(
        BASE_URL + '/order/calculate_result',
    {
        total           : total,
        payment         : payment,
        _token          : CSRF_TOKEN
    },
    function(output) {
        $('#payment_left').val(output);
        $("#loadcalculateresult").html('');
    });
});

$("#shipping_costs").keyup(function() {
    var price           = $('#price').val();
    var discount        = $('#discount').val();
    var additional      = $('#additional').val();
    var shipping_costs  = $('#shipping_costs').val();
    var down_payment    = $('#payment').val();


    $("#loadcalculateresult").html('<img alt" src="'+ IMG_SPINNER +'">');

    $.post(
        BASE_URL + '/order/calculate_cost',
    {
        price           : price,
        discount        : discount,
        additional      : additional,
        shipping_costs  : shipping_costs,
        down_payment    : down_payment,
        _token          : CSRF_TOKEN
    },
    function(data) {
        var json = JSON.parse(data);
        $("#loadcalculateresult").html('');
        if(json.status == true){
            $('#total').val(json.total);
            $('#payment_left').val(json.sisa);
            $('#full_payment').val(json.sisa);
        }else{
            swal({
                title: "Warning",
                text: "Maaf, ada kesalahan teknis. Mohon hubungi web developer (11)",
                type: "warning",
                showConfirmButton: "btn-warning"
            });

        }
    });
});

function viewOrder(order_id){
    $.post(
        BASE_URL + '/order/get_detailorder',
    {
        order_id        : order_id,
        _token          : CSRF_TOKEN
    },
    function(data) {
        $('#ViewOrder').modal('show');
        var json = JSON.parse(data);
        if(json.status == true){
            $('#ref_number').text(json.output.ref_number);
            $('#date_transaction').val(json.output.date_transaction);
            $('#customer_name').val(json.output.customer_name);
            $('#total').val(json.output.total);
            $('#discount').val(json.output.discount);
            $('#nominaldiscount').val(json.output.nominaldiscount);
            $('#additional').val(json.output.additional);
            $('#down_payment').val(json.output.down_payment);
            $('#full_payment').val(json.output.full_payment);
            $('#payment_left').val(json.output.payment_left);
            $('#status_changed').val(json.output.status);
            $('#paid').val(json.output.paid);
            $('#order_id').val(order_id);
            if(json.output.type == 'Full Payment'){
                $('#btn-Repayable').prop('disabled',true);
            }
        }else{
            swal({
                title: "Warning",
                text: json.message,
                type: "warning",
                showConfirmButton: "btn-warning"
            });
        }

    });
}

$("#btn-Repayable").click(function() {
    var order_id       = $('#order_id').val();

    window.location = BASE_URL + "/order/repayable/" + order_id;
});

$("#btn-TakeItems").click(function() {
    var order_id       = $('#order_id').val();
    $.post(
        BASE_URL + '/order/get_detailorder',
    {
        order_id        : order_id,
        _token          : CSRF_TOKEN
    },
    function(data) {
        var json = JSON.parse(data);
        if(json.status == true){
            if(json.output.status == 'Sudah Diambil'){
                $('#msgError').text('Tidak dapat mengubah status barang sudah diambil');
                $('#ShowErrMsg').modal('show');

            }else{
                $('#ChangedStatus').modal('show');
            }
        }else{
        }

    });
});

$("#btn-ChangedStatusNow").click(function() {
    var order_id            = $('#order_id').val();
    var status              = $('#status_changed').val();

    if(status == null){
        $('#msgError').text('Status Wajib diisi');
        $('#ShowErrMsg').modal('show');
    }else{
        if(status == "6"){ // changed status Done //
            window.open(BASE_URL + "/order/upload_image_done/" + order_id,"","width=800,height=600");
        }else{
            $.post(
                BASE_URL + '/order/set_changedstatus',
            {
                order_id        : order_id,
                status          : status,
                _token          : CSRF_TOKEN
            },
            function(data) {
                var json = JSON.parse(data);
                if(json.status == true){
                    $('#ViewOrder').modal('hide');
                    swal({
                        title: "Success",
                        text: json.message,
                        type: "success",
                        showConfirmButton: "btn-success"
                    });
                }else{
                    toastr.error(json.message);
                }
            });
        }
    }
});

$("#btn-PrintInvoice").click(function(){
    var order_id       = $('#order_id').val();
    window.open(BASE_URL + "/order/invoice/" + order_id,'_blank');
});

function showImage(order_detail_id){
    window.open(BASE_URL + "/order/show_imagedetail/" + order_detail_id,"","width=800,height=600");
}

$("#btn-ViewDetailOrder").click(function(){
    var order_id       = $('#order_id').val();
    window.open(BASE_URL + "/order/details/" + order_id,'_blank');
});



$("#btn-setFinish").click(function(){
    $.post(
        BASE_URL + '/order/form_upload_finish',
    $("#form_upload_finish").serialize(), function(data) {
        var json = JSON.parse(data);
        if(json.status == true){
            toastr.info(json.message);
            setTimeout(function(){
                window.opener.showMessageSuccess(json.message);
                window.close();
            }, 2000);
        }else{
            toastr.error(json.message);
        }
    });
});

function showMessageSuccess(msg){
    $('#ViewOrder').modal('hide');
    swal({
            title: "Success",
            text: msg,
            type: "success",
            showConfirmButton: "btn-warning"
        });
}


// SHOW REPORT ORDER //
$("#a-ShowReportOrder").click(function(){
    App.startPageLoading({animate: true});
    var branch              = $('#branch').val();
    var customer            = $('#customer').val();
    var from                = $('#from').val();
    var to                  = $('#to').val();
    var status              = $('#status').val();
    var param               = btoa(branch + " | " + customer + " | " + from + " | " + to + " | " + status + " | show" );

    window.location.href=BASE_URL + "/report/order/retrieve/" + param;
});
// SHOW REPORT ORDER //

// EXCEL REPORT ORDER //
$("#a-ExcelReportOrder").click(function(){
    App.startPageLoading({animate: true});
    var branch              = $('#branch').val();
    var customer            = $('#customer').val();
    var from                = $('#from').val();
    var to                  = $('#to').val();
    var status              = $('#status').val();
    var param               = btoa(branch + " | " + customer + " | " + from + " | " + to + " | " + status + " | excel" );

    window.setTimeout(function() {
        App.stopPageLoading();
    }, 2000);
    window.open(BASE_URL + "/report/order/retrieve/" + param,"_blank");
});
// EXCEL REPORT ORDER //

// PDF REPORT ORDER //
$("#a-PDFReportOrder").click(function(){
    App.startPageLoading({animate: true});
    var branch              = $('#branch').val();
    var customer            = $('#customer').val();
    var from                = $('#from').val();
    var to                  = $('#to').val();
    var status              = $('#status').val();
    var param               = btoa(branch + " | " + customer + " | " + from + " | " + to + " | " + status + " | pdf" );

    window.setTimeout(function() {
        App.stopPageLoading();
    }, 2000);
    window.open(BASE_URL + "/report/order/retrieve/" + param,"_blank");
});
// PDF REPORT ORDER //



// SHOW REPORT TRANSACTION //
$("#a-ShowReportTransaction").click(function(){
    App.startPageLoading({animate: true});
    var branch              = $('#branch').val();
    var from                = $('#from').val();
    var to                  = $('#to').val();
    var param               = btoa(branch + " | " +  from + " | " + to + " | show" );

    window.location.href=BASE_URL + "/report/transaction/retrieve/" + param;
});
// SHOW REPORT TRANSACTION //

// EXCEL REPORT TRANSACTION //
$("#a-ExcelReportTransaction").click(function(){
    App.startPageLoading({animate: true});
    var branch              = $('#branch').val();
    var from                = $('#from').val();
    var to                  = $('#to').val();
    var param               = btoa(branch + " | " +  from + " | " + to + " | excel" );

    window.setTimeout(function() {
        App.stopPageLoading();
    }, 2000);
    window.open(BASE_URL + "/report/transaction/retrieve/" + param,"_blank");
});
// EXCEL REPORT TRANSACTION //

// PDF REPORT TRANSACTION //
$("#a-PDFReportTransaction").click(function(){
    App.startPageLoading({animate: true});
    var branch              = $('#branch').val();
    var from                = $('#from').val();
    var to                  = $('#to').val();
    var param               = btoa(branch + " | " +  from + " | " + to + " | pdf" );

    window.setTimeout(function() {
        App.stopPageLoading();
    }, 2000);
    window.open(BASE_URL + "/report/transaction/retrieve/" + param,"_blank");
});
// PDF REPORT TRANSACTION //
