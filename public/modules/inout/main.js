$("#flow").change( function() {
    App.startPageLoading({animate: true});
    $.post(
        BASE_URL + '/account/get_listbyflow',
        {
            flow    : $(this).val(),
            _token  : CSRF_TOKEN
        },
        function( msg ) {
            $("#account").html(msg).show();
        });
    App.stopPageLoading();

});
