<?php
/*
Plugin Name: Toggle Stock
Plugin URI: https://github.com/d0n601/toggle-stock/
Description: Adds an "out of stock" or "in stock" button to wordpress admin panel on WooCommerce product pages, for quick toggling by shop managers.
Author: Ryan Kozak
Version: 1.0.0
Author URI: https://theseedgroup.com
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Enqueue our Assets
 *
 */
function toggle_stock_assets() {
    wp_enqueue_script( 'toggle_stock_js',  plugins_url( '/js/toggle-stock.js', __FILE__ ), [ 'wp-api' ]);
    wp_register_style('toggle_stock_css', plugin_dir_url(__FILE__).'/css/toggle_stock.css');
    wp_enqueue_style('toggle_stock_css');
} add_action('wp_enqueue_scripts', 'toggle_stock_assets');



/**
 * Add an authenticated WP REST API endpoint for toggling the product's stock.
 *
 */
add_action( 'rest_api_init', function () {
    register_rest_route('toggle-stock/v1', '/toggle', array(
        'methods' => 'POST',
        'callback' => 'toggle_product_stock',
        'permission_callback' => function () {
            return array_intersect(array('editor', 'administrator', 'author', 'shop_manager'), wp_get_current_user()->roles);
        }
    ));
});



/**
 * Show an admin notice if WooCommerce isn't enabled.
 *
 */
function check_woocommerce_active() {
    if ( !is_plugin_active( 'woocommerce/woocommerce.php' )) {
        add_action('admin_notices', function() { ?>
            <div class="notice notice-error" >
                <p> Please Enable WooCommerce, or Toggle Stock is Useless!</p>
            </div><?php
        });
    }
} add_action( 'admin_init', 'check_woocommerce_active' );



/**
 * Toggle's a non-managed product's stock to in/out of stock.
 *
 */
function toggle_product_stock() {

    if ( empty($_POST) || !isset($_POST['pid'])) return "No Post";

    $product = wc_get_product( $_POST['pid']);

    if(!$product) return "No Product!";

    // For simple products
    if($product->is_type( 'simple')) {

        if($product->is_in_stock()) {
            $product->set_stock_status('outofstock');
        }
        else {
            $product->set_stock_status('instock');
        }


        $product->save();

        return $product;
    }

    elseif($product->is_type( 'variable')) {

        $variation = wc_get_product($_POST['var_id']);

        if($variation->is_in_stock()) {
            $variation->set_stock_status('outofstock');
        }
        else {
            $variation->set_stock_status('instock');
        }

        $variation->save();

        return $variation;
    }
}



/**
 * Adds toggle button to WP Admin Toolbar for single product pages.
 *
 * @param $wp_admin_bar
 */
function toggle_product_stock_link( $wp_admin_bar ) {

    // Only show on product pages
    if(is_admin() || !is_product())  return;

    global $product;

    $nonce = wp_create_nonce( 'wp_rest' );
    echo "<input type='hidden' id='_nonce' value=" . $nonce ."/>";

    $pid = $product->get_id();

    if($product->is_type( 'simple')) {

        if($product->is_in_stock()) {
            $button_html = "<p id='tsock_seed' class='stock out-of-stock tsock_seed_out' data-pid='$pid'>Set to Out Stock</p>";
        }
        else {
            $button_html = "<p id='tsock_seed' class='stock in-stock tsock_seed_in' data-pid='$pid'>Set to In Stock</p>";
        }
        $wp_admin_bar->add_node( array('id' => 'toggle_product_stock_simple', 'meta'  => array('html'  => $button_html) ));
    }
    elseif($product->is_type( 'variable')) {

        $button_html = "<p id='tsock_seed' class='tsock_seed_variable' data-pid='$pid'>Select Variation</p>";

        $all_variations = $product->get_available_variations();

        foreach($all_variations as $variation) {

            $var_id = $variation['variation_id'];

            if($variation['is_in_stock']) {
                $button_html .= "<p id='tsock_seed_variation' class='stock out-of-stock tsock_seed_out tsock_seed_variation' data-pid='$pid' data-variation='$var_id'>Set to Out Stock</p>";
            }
            else {
                $button_html .= "<p id='tsock_seed_variation' class='stock in-stock tsock_seed_in tsock_seed_variation' data-pid='$pid' data-variation='$var_id'>Set to In Stock</p>";
            }
        }

        $wp_admin_bar->add_node( array('id' => 'toggle_product_stock_variable', 'meta'  => array('html'  => $button_html) ));
    }

} add_action( 'admin_bar_menu', 'toggle_product_stock_link', 999 );
