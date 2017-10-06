function loading(){
    App.startPageLoading({animate: true});
}

// REMOVE AVATAR //
$("#btn-RemoveAvatar").click(function() {
    $('#ConfirmRemove').modal('show');
});

$("#btn-RemoveAvatarNow").click(function() {
    App.startPageLoading({animate: true});
    window.location.href=BASE_URL + "/user/delete_avatar";
});
// END REMOVE AVATAR //

// VERIFIED //
$("#btn-Verified").click(function() {

    App.startPageLoading({animate: true});
    $.post(
        BASE_URL + '/user/sendcode',
    {
        mobile          : $('#mobile').val(),
        _token          : CSRF_TOKEN
    },
    function(data) {
        var json = JSON.parse(data);

        if(json.status == true){
            window.setTimeout(function() {
                App.stopPageLoading();
            }, 2000);
            toastr.success(json.message);
            $('#ModalVerified').modal('show');
        }else{
            window.setTimeout(function() {
                App.stopPageLoading();
            }, 2000);
            toastr.error(json.message);
        }
    });
});

$( "#code1" ).keyup(function() {
  $( "#code2" ).focus();
});

$( "#code2" ).keyup(function() {
  $( "#code3" ).focus();
});
$( "#code3" ).keyup(function() {
  $( "#code4" ).focus();
});

$( "#code4" ).keyup(function() {
    alert('oke');
});

$("#btn-VerifyCodeNow").click(function() {

    App.startPageLoading({animate: true});
    $.post(
        BASE_URL + '/user/checkcode',
    {

        code          : $('#code1').val()+$('#code2').val()+$('#code3').val()+$('#code4').val(),
        _token          : CSRF_TOKEN
    },
    function(data) {
        var json = JSON.parse(data);

        if(json.status == true){
            window.setTimeout(function() {
                App.stopPageLoading();
            }, 2000);
            toastr.success(json.message);
            $('#ModalVerified').modal('show');
        }else{
            window.setTimeout(function() {
                App.stopPageLoading();
            }, 2000);
            toastr.error(json.message);
        }
    });
});




// VERIFIED //
