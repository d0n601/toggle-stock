<?php
/*
Plugin Name: Toggle Stock
Plugin URI: https://github.com/d0n601/toggle-stock/
Description: Adds an "out of stock" or "in stock" button to wordpress admin panel on WooCommerce product pages, for quick toggling by shop managers.
Author: Ryan Kozak
Version: 0.0.1
Author URI: https://theseedgroup.com
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Enqueue our JS
 *
 */
function toggle_stock_assets() {
    wp_enqueue_script( 'toggle_stock_js',  plugins_url( 'toggle-stock.js', __FILE__ ), [ 'wp-api' ]);
} add_action('wp_enqueue_scripts', 'toggle_stock_assets');




/**
 * Toggle's a non-managed product's stock to in/out of stock.
 *
 */
function toggle_product_stock() {

    if ( empty($_POST) || !isset($_POST['pid'])) return "No Post";


    $product = wc_get_product( $_POST['pid']);

    if(!$product) return "No Product!";


    if($product->is_in_stock()) {
        $product->set_stock_status('outofstock');
    }
    else {
        $product->set_stock_status('instock');
    }

    $product->save();

    return $product;


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

    if($product->is_in_stock()) {
        $button_html = "<p id='tsock_seed' class='stock out-of-stock' style='margin-top:-32px; cursor:pointer;' data-pid='$pid'>Set Out of stock</p>";
    }
    else {
        $button_html = "<p id='tsock_seed' class='stock in-stock' style='margin-top:-32px; cursor:pointer;' data-pid='$pid'>Set In of stock</p>";
    }

    $wp_admin_bar->add_node( array('id' => 'toggle_product_stock', 'meta'  => array('html'  => $button_html) ));

} add_action( 'admin_bar_menu', 'toggle_product_stock_link', 999 );



/**
 * Add an authenticated WP REST API endpoint for toggling the product's stock.
 *
 */
add_action( 'rest_api_init', function () {
    register_rest_route('toggle-stock/v1', '/toggle', array(
        'methods' => 'POST',
        'callback' => 'toggle_product_stock',
        'permission_callback' => function () {
            return array_intersect(array('editor', 'administrator', 'author'), wp_get_current_user()->roles);
        }
    ));
});