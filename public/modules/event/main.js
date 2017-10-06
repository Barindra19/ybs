function ResetSaldo(id){
    $('#id_reset').val(id);
    $.post(
        BASE_URL + '/event/info',
    {
        event_id                : id,
        _token                  : CSRF_TOKEN
    },
    function(data) {
        var json = JSON.parse(data);
        if(json.status == true){
            $('#info_saldorealtime').val(json.output.saldo_realtime);
            $('#info_saldorealtime_date').val(json.output.saldo_realtime_date);
            $('#ModalResetSaldo').modal('show');
        }else{
            toastr.error(json.message);
        }
    });
}


$("#btn-ResetConfirm").click(function() {
    $('#ConfirmReset').modal('show');
});


$("#btn-ResetNow").click(function() {
    $.post(
        BASE_URL + '/event/resetsaldo',
    $("#reset-saldo").serialize(), function(data) {
        var json = JSON.parse(data);
        if(json.status == true){
            toastr.success(json.message);
            $('#ModalResetSaldo').modal('hide');
        }else{
            toastr.error(json.message);
        }
    });
});
