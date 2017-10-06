$("#barcode_scan").keyup(function() {
    var barcode             = $('#barcode_scan').val();
    if(barcode.indexOf("*") < 0){
        if(barcode){
            App.startPageLoading({animate: true});
            $.post(
                BASE_URL + '/check_transaction',
            {
                barcode             : barcode,
                _token              : CSRF_TOKEN
            },
            function(output) {
                var json = JSON.parse(output);
                if(json.status == true){
                    // toastr.success(json.message);
                    if(json.checkresult == 'order'){
                        $('#ViewOrder').modal('show');
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
                        $('#order_id').val(json.output.order_id);
                        if(json.output.type == 'Full Payment'){
                            $('#btn-Repayable').prop('disabled',true);
                        }
                    }else if(json.checkresult == 'customer'){
                        $('#ViewCustomer').modal('show');
                        $('#ref_number_customer').text(json.output.ref_number);
                        $('#customer_fullname').val(json.output.name);
                        $('#customer_address').val(json.output.address);
                        $('#customer_phone').val(json.output.phone);
                        $('#customer_email').val(json.output.email);
                        $('#customer_id').val(json.output.customer_id);
                    }
                }else{
                    toastr.error(json.message);
                }
                window.setTimeout(function() {
                    App.stopPageLoading();
                }, 500);
            });
        }
    }
});

$('#form-search-barcode').on('keyup keypress', function(e) {
  var keyCode = e.keyCode || e.which;
  if (keyCode === 13) {
    e.preventDefault();
    return false;
  }
});

$("#btn-ClearTxtBarcode").click(function(){
    var order_id       = $('#barcode_scan').val("");
    $('#barcode_scan').focus();
});


$("#btn-PrintInvoice").click(function(){
    var order_id       = $('#order_id').val();
    window.open(BASE_URL + "/order/invoice/" + order_id,'_blank');
});


$("#btn-ViewDetailOrder").click(function(){
    var order_id       = $('#order_id').val();
    window.open(BASE_URL + "/order/details/" + order_id,'_blank');
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

$("#btn-Repayable").click(function() {
    var order_id       = $('#order_id').val();
    window.location = BASE_URL + "/order/repayable/" + order_id;
});

$("#btn-OrderTreatmentNow").click(function() {
    var customer_id    = $('#customer_id').val();
    window.location = BASE_URL + "/order/add/" + customer_id;
});

$("#btn-DetailCustomer").click(function() {
    var customer_id    = $('#customer_id').val();
    window.location = BASE_URL + "/customer/detail_customer/" + customer_id;
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

function showMessageSuccess(msg){
    $('#ViewOrder').modal('hide');
    $('#barcode_scan').val('');
    swal({
            title: "Success",
            text: msg,
            type: "success",
            showConfirmButton: "btn-warning"
        });
}
