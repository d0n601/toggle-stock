jQuery(document).ready(function( $ ) {
    var toggle_stock = $("#wp-admin-bar-toggle_product_stock");

    if(toggle_stock.length) {
        toggle_stock.click(function() {
            var pid = $('#tsock_seed').attr('data-pid');
            toggle_product_stock(pid);
        });
    }
});



function toggle_product_stock(pid) {

    var url = "/wp-json/toggle-stock/v1/toggle";

    var json = {"pid": pid};

    jQuery.ajax(
        {
            url: url,
            type: 'POST',
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
            },
            dataType: 'json',
            data: json,
            success: function (data) {
                console.log(data);
                document.location.reload();
            },
            // If something gone wrong'd
            error: function (errorThrown) {
                console.log(errorThrown);
            }
        });
}