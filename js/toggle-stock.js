jQuery(document).ready(function( $ ) {

    var toggle_stock_simple = $("#wp-admin-bar-toggle_product_stock_simple");
    var toggle_stock_variable = $("#wp-admin-bar-toggle_product_stock_variable");


    if(toggle_stock_simple.length) {
        toggle_stock_simple.click(function() {
            var pid = $('#tsock_seed').attr('data-pid');
            toggle_product_stock(pid, null);
        });
    }    
    else if(toggle_stock_variable.length) {

        $(".tsock_seed_variable").click(function() {
            alert('Select a product option');
        });

       $('input.variation_id').change( function(){

            if( '' != $('input.variation_id').val() ) {

               var_id = $('input.variation_id').val();

                // Hide the generic button telling you to select a product variation.
                $(".tsock_seed_variable").css("display", "none");

                // Show the toggle button for selected variation.

                // Hide other variation buttons that may have been displayed from previous selections. 
                $(".tsock_seed_variation").css("display", "none");

                // Display button for selected variation
                $("*[data-variation="+var_id+"]").css("display", "inline-block");

                toggle_stock_variable.click(function() {
                    var pid = $('#tsock_seed').attr('data-pid');
                    toggle_product_stock(pid, var_id);
                });
            }
         });
    }
});


function toggle_product_stock(pid, var_id) {

    var url = "/wp-json/toggle-stock/v1/toggle";

    var json = {"pid": pid, "var_id": var_id};

    jQuery.ajax( {
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