<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Add custom field to the checkout page
 */
add_filter( 'manage_edit-shop_order_columns', 'bbloomer_add_new_order_admin_list_column' );
 
function bbloomer_add_new_order_admin_list_column( $columns ) {
    $columns['extra_product_price'] = 'Express Payments';
    return $columns;
}
 
add_action( 'manage_shop_order_posts_custom_column', 'bbloomer_add_new_order_admin_list_column_content' );
 
function bbloomer_add_new_order_admin_list_column_content( $column ) {
   
    global $post;
 
    if ( 'extra_product_price' === $column ) {
        $order_meta = get_post_meta($post->ID);
        if(!empty($order_meta['extra_product_price'][0])) {
          echo '';
          echo '<span style="height: .8em;width: .8em;background-color: #7ad03a;border-radius: .4em;display: inline-block;"></span>'; 
          echo ' ';
          echo str_replace('NUMBER', number_format($order_meta['extra_product_price'][0], 2), '฿NUMBER');
        }else{
          echo '';
        }
      
    }
}
