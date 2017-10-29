// SHOW REPORT ORDER ITEM //
$("#a-ShowReport").click(function(){
    App.startPageLoading({animate: true});
    var branch              = $('#branch').val();
    var supplier            = $('#supplier').val();
    var from                = $('#from').val();
    var to                  = $('#to').val();
    var param               = btoa(branch + " | " + supplier + " | " +  from + " | " + to + " | show" );

    window.location.href=BASE_URL + "/report/order/item/retrieve/" + param;
});
// SHOW REPORT ORDER ITEM //

// EXCEL REPORT ORDER ITEM //
$("#a-ExcelReport").click(function(){
    App.startPageLoading({animate: true});
    var branch              = $('#branch').val();
    var supplier            = $('#supplier').val();
    var from                = $('#from').val();
    var to                  = $('#to').val();
    var param               = btoa(branch + " | " + supplier + " | " +  from + " | " + to + " | excel" );

    window.setTimeout(function() {
        App.stopPageLoading();
    }, 2000);
    window.open(BASE_URL + "/report/order/item/retrieve/" + param,"_blank");
});
// EXCEL REPORT ORDER ITEM //

// PDF REPORT ORDER ITEM //
$("#a-PDFReport").click(function(){
    App.startPageLoading({animate: true});
    var branch              = $('#branch').val();
    var supplier            = $('#supplier').val();
    var from                = $('#from').val();
    var to                  = $('#to').val();
    var param               = btoa(branch + " | " + supplier + " | " +  from + " | " + to + " | pdf" );

    window.setTimeout(function() {
        App.stopPageLoading();
    }, 2000);
    window.open(BASE_URL + "/report/order/item/retrieve/" + param,"_blank");
});
// PDF REPORT ORDER ITEM //
