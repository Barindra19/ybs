$("#btn-formGenerate").click(function() {
    $('#FormGenerate').modal('show');
});


$("form#form_barcode").submit(function(){
    App.startPageLoading({animate: true});
    var formData = new FormData(this);
    $.ajax({
        url: BASE_URL + '/barcode/generate',
        type: 'POST',
        data: formData,
        async: false,
        success: function (data) {
            var json = JSON.parse(data);
            if(json.status == true){
                toastr.success(json.message);
                console.log(json.output.barcode_count);
                $('#FormGenerate').modal('hide');
            }else{
                toastr.warning(json.message);
                console.log(json.output.barcode_count);
            }
            window.setTimeout(function() {
                App.stopPageLoading();
                window.location = BASE_URL + "/barcode/show";
            }, 1000);
        },
        cache: false,
        contentType: false,
        processData: false
    });

    return false;
});