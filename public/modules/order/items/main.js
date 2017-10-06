var counter = 1;
var rcounter = 1;

$(document).ready(function() {
$("#loadcustomer").html('');
$("#loadbranch").html('');
$("#loadstock").html('');
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

    $("#btn-Add").on('click', function(e){
        $("#stock").select2('val', "0");
        $("#quantity").val(0);
        $("#price").val(0);
        $("#subtotal").val(0);
        $("#discount").val(0);
        $("#nominaldiscount").val(0);
        $("#additional").val(0);
        $("#total").val(0);
        $('#AddDetail').modal('show');
    });

});

$("#quantity").keyup(function() {
    var quantity    = $('#quantity').val();
    var price       = $('#price').val();
    var discount    = $('#discount').val();
    var additional  = $('#additional').val();
    $("#loadcalculate").html('<img alt" src="'+ IMG_SPINNER +'">');

    $.post(
        BASE_URL + '/order/items/calculate',
    {
        quantity        : quantity,
        price           : price,
        discount        : discount,
        additional      : additional,
        _token          : CSRF_TOKEN
    },
    function(output) {
        var json = JSON.parse(output);
        $("#loadcalculate").html('');
        $('#nominaldiscount').val(json.nominaldiscount);
        $('#subtotal').val(json.subtotal);
        $('#total').val(json.total);
        $('#labelTotal').text(json.total);
        $('#payment').val(json.total);
    });
});

$("#discount").keyup(function() {
    var quantity    = $('#quantity').val();
    var price       = $('#price').val();
    var discount    = $('#discount').val();
    var additional  = $('#additional').val();
    $("#loadcalculate").html('<img alt" src="'+ IMG_SPINNER +'">');

    $.post(
        BASE_URL + '/order/items/calculate',
    {
        quantity        : quantity,
        price           : price,
        discount        : discount,
        additional      : additional,
        _token          : CSRF_TOKEN
    },
    function(output) {
        var json = JSON.parse(output);
        $("#loadcalculate").html('');
        $('#nominaldiscount').val(json.nominaldiscount);
        $('#subtotal').val(json.subtotal);
        $('#total').val(json.total);
        $('#labelTotal').text(json.total);
        $('#payment').val(json.total);
    });
});


$("#additional").keyup(function() {
    var quantity    = $('#quantity').val();
    var price       = $('#price').val();
    var discount    = $('#discount').val();
    var additional  = $('#additional').val();
    $("#loadcalculate").html('<img alt" src="'+ IMG_SPINNER +'">');

    $.post(
        BASE_URL + '/order/items/calculate',
    {
        quantity        : quantity,
        price           : price,
        discount        : discount,
        additional      : additional,
        _token          : CSRF_TOKEN
    },
    function(output) {
        var json = JSON.parse(output);
        $("#loadcalculate").html('');
        $('#nominaldiscount').val(json.nominaldiscount);
        $('#subtotal').val(json.subtotal);
        $('#total').val(json.total);
        $('#labelTotal').text(json.total);
        $('#payment').val(json.total);
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

$("#stock").change( function() {
$("#loadstock").html('<img alt" src="'+ IMG_SPINNER +'">');
    $.post(
        BASE_URL + '/stock/info',
    {
        id              : $(this).val(),
        _token          : CSRF_TOKEN
    },
    function( data ) {
        var json = JSON.parse(data);
        if(json.status == true){
            $('#price').val(json.output.selling_price);
            $('#readystock').val(json.output.stock);
        }
        $("#loadstock").html('');
    });
});


$("#btn-AddItem").click(function() {
    $.post(
        BASE_URL + '/order/items/save_item',
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
                $('#row-subtotal').remove();
                var trHTML      = '<tr id="row'+ json.output.id +'" class="danger">';
                        trHTML += '<td>' + json.output.name + '</td>';
                        trHTML += '<td><span class="label label-sm label-info">' + json.output.quantity + ' pcs </span></td>';
                        trHTML += '<td>' + json.output.price + '</td>';
                        if(json.output.discount > 0){
                            trHTML += '<td><span class="label label-sm label-danger"> ' + json.output.nominaldiscount + '</span></td>';
                        }else{
                            trHTML += '<td>-</td>';
                        }
                        if(json.output.additional > 0){
                            trHTML += '<td><span class="label label-sm label-info"> ' + json.output.additional + '</span></td>';
                        }else{
                            trHTML += '<td>-</td>';
                        }
                            trHTML += '<td><span class="label label-sm label-success"> ' + json.output.total + '</span></td>';
                        trHTML += '<td><a href="javascript:void(0)" class="btn btn-outline btn-circle red btn-sm black" onclick="deleteList(' + json.output.id + ')"><i class="fa fa-trash-o"></i> Delete </a></td>';
                        trHTML += '</tr>';
                        trHTML += '<tr class="active" id="row-subtotal">';
                        trHTML += '<th colspan="4"> </th>';
                        trHTML += '<th>SubTotal</th>';
                        trHTML += '<th> <span class="label label-sm label-success"> <label id="labelTotal">'+ json.output.total_header +' </label> </span></th>';
                        trHTML += '<th></th>';
                        trHTML += '</tr>';

                $('#order_detail').append(trHTML);
                $('#total_header').val(json.output.total_header);
                $('#grandtotal').val(json.output.total_header);
                $('#payment').val(json.output.total_header);
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
        BASE_URL + '/order/items/deletedetail',
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
            $('#labelTotal').text(json.output.total_header);
            $('#payment').val(json.output.total_header);


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


$("#payment").keyup(function() {
    var total           = $('#grandtotal').val();
    var payment         = $('#payment').val();

    $("#loadcalculateresult").html('<img alt" src="'+ IMG_SPINNER +'">');

    $.post(
        BASE_URL + '/order/items/calculate_result',
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
        BASE_URL + '/order/items/calculate_cost',
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

function viewOrder(order_item_id){
    $.post(
        BASE_URL + '/order/items/get_detailorder',
    {
        order_item_id        : order_item_id,
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
            $('#payment').val(json.output.payment);
            $('#payment_type').val(json.output.payment_type);
            $('#order_item_id').val(order_item_id);
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
});

$("#btn-PrintInvoice").click(function(){
    var order_item_id       = $('#order_item_id').val();
    window.open(BASE_URL + "/order/items/invoice/" + order_item_id,'_blank');
});

function showImage(order_detail_id){
    window.open(BASE_URL + "/order/show_imagedetail/" + order_detail_id,"","width=800,height=600");
}
