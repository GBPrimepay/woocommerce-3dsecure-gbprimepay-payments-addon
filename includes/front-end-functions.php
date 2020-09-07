<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Add custom field to the checkout page
 */
add_action('woocommerce_after_order_notes', 'extra_product_name');
function extra_product_name($checkout)
{
    global $wp_query;
    $pagename = get_query_var('pagename');
    if (get_post_type() && get_post_type() === 'page') {
        if ($pagename && $pagename === 'express-payments') {
            echo '<div id="divextra_product_name"><h2>' . __('Product information') . '</h2>';
            woocommerce_form_field('extra_product_name', array(
                'type' => 'text',
                'class' => array(
                    'form-row form-row-wide',
                ),
                'required' => true,
                'label' => __('Product Name'),
                'placeholder' => __('Product Name'),
            ),
                $checkout->get_value('extra_product_name'));
            woocommerce_form_field('extra_product_price', array(
                'type' => 'number',
                'class' => array(
                    'form-row form-row-wide',
                ),
                'required' => true,
                'label' => __('Product Price'),
                'placeholder' => __('Product Price'),
            ),
                $checkout->get_value('extra_product_price'));
            echo '</div>';
        }
    }
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'bbloomer_show_new_checkout_field_order', 10, 1 );
function bbloomer_show_new_checkout_field_order( $order ) {    
   $order_id = $order->get_id();
   if ( get_post_meta( $order_id, 'extra_product_name', true ) ) echo '<p><strong>Product:</strong> ' . get_post_meta( $order_id, 'extra_product_name', true ) . '</p>';
   if ( get_post_meta( $order_id, 'extra_product_price', true ) ) echo '<p><strong>Price:</strong> ' . get_post_meta( $order_id, 'extra_product_price', true ) . '</p>';
}
add_action( 'woocommerce_email_after_order_table', 'bbloomer_show_new_checkout_field_emails', 20, 4 );
function bbloomer_show_new_checkout_field_emails( $order, $sent_to_admin, $plain_text, $email ) {
  if ( get_post_meta( $order->get_id(), 'extra_product_name', true ) ) echo '<p><strong>Product:</strong> ' . get_post_meta( $order->get_id(), 'extra_product_name', true ) . '</p>';
  if ( get_post_meta( $order->get_id(), 'extra_product_price', true ) ) echo '<p><strong>Price:</strong> ' . get_post_meta( $order->get_id(), 'extra_product_price', true ) . '</p>';
}
add_action( 'woocommerce_email_after_order_table', 'bbloomer_display_payment_type_name_emails', 15 );
function bbloomer_display_payment_type_name_emails( $order ) {
   echo '<p><strong>Express Payments:</strong> ' . $order->get_payment_method_title() . '</p>';
}
add_action( 'template_redirect', 'misha_add_to_cart_on_custom_page');
function misha_add_to_cart_on_custom_page(){
	if( is_page( 'express-payments' ) ) { // "my-page" is a page slug
    $gbp_express_product_id = get_option('gbp_express_product_id');

    global $woocommerce;		
$extra_product_price = WC()->session->get('gbp_express_pay_session');
$extra_product_name = WC()->session->get('gbp_express_name_session');
    // WC()->cart->empty_cart();
    // WC()->cart->add_to_cart( $gbp_express_product_id ); 
				$product_cart_id = WC()->cart->generate_cart_id( $gbp_express_product_id );
					if(!WC()->cart->find_product_in_cart( $product_cart_id )) {
    // WC()->cart->add_to_cart($gbp_express_product_id, 1, NULL, NULL, array('price' => $extra_product_price,'sale_price' => $extra_product_price,'regular_price' => $extra_product_price));
    
    WC()->cart->add_to_cart( $gbp_express_product_id ); 
  }
	}
}

add_action( 'woocommerce_before_calculate_totals', array( $this, 'wk_update_price' ) );