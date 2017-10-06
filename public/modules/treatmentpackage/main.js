$(document).ready(function() {

    $("#treatment").change( function() {
    $("#retrieve").html('load data');
        $.post(
            BASE_URL + '/treatmentcategory/searchbyparent',
        {
            treatment_id : $(this).val(),
            _token : CSRF_TOKEN
        },
        function( msg ) {
            if(msg != '<option value="0">Choose Treatment Category</option>'){
                $("#treatmentcategory").html(msg).show();
                $("#retrieve").html('');
                $("#treatmentcategory").prop('disabled',false);
                $("#treatmentcategorybackup").prop('disabled',true);
            }else{
                $("#treatmentcategory").html(msg).show();
                $("#retrieve").html('<span class="help-block">Data is not specific</span>');
                $("#treatmentcategory").prop('disabled',true);
                $("#treatmentcategorybackup").prop('disabled',false);

            }
        });
    });
});
