
function DetailList(id){
    $.post(
        BASE_URL + '/archive/info',
    {
        archive_id        : id,
        _token          : CSRF_TOKEN
    },
    function(data) {
        $('#ViewOrder').modal('show');
        var json = JSON.parse(data);
        if(json.status == true){
            $('#no_transaction').val(json.output.no_transaction);
            $('#date_transaction').val(json.output.date_transaction);
            $('#customer_name').val(json.output.customer);
            $('#subtotal').val(json.output.subtotal);
            $('#additional').val(json.output.additional);
            $('#discount').val(json.output.discount);
            $('#total').val(json.output.total);
            $('#down_payment').val(json.output.dp);
            $('#sisa').val(json.output.sisa);
            $('#status').val(json.output.status);
            $('#archive_id').val(id);
            if(json.output.status == 'Lunas'){
                $('#btn-TakeItems').prop('disabled',true);
            }else{
                $('#btn-TakeItems').prop('disabled',false);
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

$("#btn-TakeItems").click(function(){
    var archive_id       = $('#archive_id').val();
    window.open(BASE_URL + "/archive/take_items/" + archive_id,'_blank');
});
