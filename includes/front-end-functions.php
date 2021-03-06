<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Add custom field to the checkout page
 */

function custom_wc_translations($translated){
    if (gbp_instances('LABEL_EN') == true) {        
        $text = array(    
            'Product information' => 'Product information',
            'Product Name' => 'Product Name',
            'Product Price' => 'Product Price',
            'Use a new Credit Card' => 'Use a new Credit Card',                
        );
    }else{
        $text = array(
            'Product information' => 'ข้อมูลสินค้า',
            'Product Name' => 'ชื่อสินค้า',
            'Product Price' => 'ราคาสินค้า',
            'Use a new Credit Card' => 'ใช้บัตรเคดิต/เดบิตใหม่',        
        );
    }
    $translated = str_ireplace(  array_keys($text),  $text,  $translated );
    return $translated;
}
add_filter( 'gettext', 'custom_wc_translations', 20 );

add_filter( 'woocommerce_default_address_fields', 'misha_remove_fields' );
 
function misha_remove_fields( $fields ) {
    unset( $fields["first_name"]['required'] );
    unset( $fields["last_name"]['required'] );
    unset( $fields["country"]['required'] );
    unset( $fields["company"]['required'] );
    unset( $fields["address_1"]['required'] );
    unset( $fields["address_2"]['required'] );
    unset( $fields["city"]['required'] );
    unset( $fields["state"]['required'] );
    unset( $fields["postcode"]['required'] );
	return $fields;
 
}
add_filter( 'woocommerce_billing_fields', 'wc_npr_filter_phone', 10, 1 );
function wc_npr_filter_phone( $address_fields ) {
	$address_fields['billing_phone']['required'] = false;
    return $address_fields;
}
add_action('woocommerce_after_order_notes', 'extra_product_name');
function extra_product_name($checkout)
{
    global $wp_query;
    $pagename = get_query_var('pagename');
    if (get_post_type() && get_post_type() === 'page') {
        if ($pagename && $pagename === 'express-payments') {
            echo '<div class="woocommerce-additional-fields" style="margin-top: 2em;" id="divextra_product_name"><h3>' . __('Product information') . '</h3>';
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

add_action('wp_enqueue_scripts', 'override_woo_frontend_scripts');
function override_woo_frontend_scripts() {
    wp_deregister_script('wc-credit-card-form');
    wp_enqueue_script('wc-credit-card-form', plugin_dir_url( __DIR__ ) . '../woocommerce/assets/js/frontend/credit-card-form.js', array('jquery', 'jquery-payment'), null, true);
    wp_enqueue_script(
        'express-payments-script',
        AS_GBPRIMEPAY_PLUGIN_URL . '/assets/js/express-payments-script.js',
        array(),
        false,
        true
    );
}

add_action( 'woocommerce_admin_order_data_after_billing_address', 'bbloomer_show_new_checkout_field_order', 10, 1 );
function bbloomer_show_new_checkout_field_order( $order ) {    
   $order_id = $order->get_id();
   if ( get_post_meta( $order_id, 'extra_product_name', true ) ) echo '<p><strong>Product:</strong> ' . get_post_meta( $order_id, 'extra_product_name', true ) . '</p>';
   if ( get_post_meta( $order_id, 'extra_product_price', true ) ) echo '<p><strong>Price:</strong> ' . str_replace('NUMBER', number_format(get_post_meta( $order_id, 'extra_product_price', true ), 2), '฿NUMBER') . '</p>';
}
add_action( 'woocommerce_email_after_order_table', 'bbloomer_show_new_checkout_field_emails', 20, 4 );
function bbloomer_show_new_checkout_field_emails( $order, $sent_to_admin, $plain_text, $email ) {
  if ( get_post_meta( $order->get_id(), 'extra_product_name', true ) ) echo '<p><strong>Product:</strong> ' . get_post_meta( $order->get_id(), 'extra_product_name', true ) . '</p>';
  if ( get_post_meta( $order->get_id(), 'extra_product_price', true ) ) echo '<p><strong>Price:</strong> ' . str_replace('NUMBER', number_format(get_post_meta( $order->get_id(), 'extra_product_price', true ), 2), '฿NUMBER') . '</p>';
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
    WC()->cart->empty_cart();
    // WC()->cart->add_to_cart( $gbp_express_product_id ); 
$product_cart_id = WC()->cart->generate_cart_id( $gbp_express_product_id );
if(!WC()->cart->find_product_in_cart( $product_cart_id )) {    
    $_cart_item_key = WC()->cart->add_to_cart( $gbp_express_product_id, 1);
  }
	}
}

add_action( 'woocommerce_before_calculate_totals', array( $this, 'wk_update_price' ) );
add_action( 'template_redirect', array( $this, 'set_session' ) );
