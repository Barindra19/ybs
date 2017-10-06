var counter = 1;
var rcounter = 1;

$(document).ready(function() {
$("#loadcustomer").html('');
$("#loadbranch").html('');

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

function inactiveList(id){
    $('#id').val(id);
    $('#ConfirmInactive').modal('show');
}

$("#btn-ActionInactive").click(function() {
    var id = $('#id').val();
    window.location = BASE_URL + "/stock/inactive/" + id;
});


function EditStock(id){
    $('#id').val(id);
    $.post(
        BASE_URL + '/stock/info',
    {
        id      : id,
        _token  : CSRF_TOKEN
    },
    function(data) {
        var json = JSON.parse(data);
        // console.log(json.status);
        if(json.status == true){
            $('#ModalEditStock').modal('show');
            $('#selling_price').val(json.output.selling_price);
            $('#cost_of_good').val(json.output.cost_of_good);
        }else{
            toastr.error(json.message);
        }
    });
}


$("#btn-UpdateStock").click(function() {
    var id      = $('#id').val();
    var stock   = $('#stock_new').val();
    console.log(stock);
    $.post(
        BASE_URL + '/stock/update_stock',
    {
        id          : id,
        stock       : stock,
        _token  : CSRF_TOKEN
    },
    function(data) {
        var json = JSON.parse(data);
        // console.log(json.status);
        if(json.status == true){
            $('#ModalEditStock').modal('hide');
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
});
