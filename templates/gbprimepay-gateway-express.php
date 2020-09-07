<?php
/*
*
Template Name: Full-Width
*/
get_header();
    ?>
    <?php
    // Remove some checkout billing fields
    function kia_filter_billing_fields($fields){
        // unset( $fields["billing_first_name"] );
        // unset( $fields["billing_last_name"] );
        // unset( $fields["billing_phone"] );
        // unset( $fields["billing_email"] );
        // unset( $fields["billing_country"] );
        // unset( $fields["billing_company"] );
        //     unset( $fields["billing_address_1"] );
        //     unset( $fields["billing_address_2"] );
        //     unset( $fields["billing_city"] );
        //     unset( $fields["billing_state"] );
        //     unset( $fields["billing_postcode"] );
        unset( $fields["order_comments_field"] );
        return $fields;
    }
    // add_filter( 'woocommerce_billing_fields', 'kia_filter_billing_fields' );
    add_filter( 'woocommerce_checkout_registration_enabled', '__return_false' );
    add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
    add_filter( 'woocommerce_savedpaymentmethods_savenew', '__return_false' );
    // remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );
  function custom_wc_translations($translated){
    $text = array(
    'Your order' => 'Pay Now',
    'Billing details' => 'Express Payments Form',
    );
    $translated = str_ireplace(  array_keys($text),  $text,  $translated );
    return $translated;
}
add_filter( 'gettext', 'custom_wc_translations', 20 );
// Add a new checkout field
function kia_filter_checkout_fields($fields){
  $fields['extra_fields'] = array();
  return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'kia_filter_checkout_fields' );
// display the extra field on the checkout form
function kia_extra_checkout_fields(){ 
  $checkout = WC()->checkout(); ?>
  <div class="extra-fields">
  <h3><?php _e( '' ); ?></h3>
  <?php 
  // because of this foreach, everything added to the array in the previous function will display automagically
  foreach ( $checkout->checkout_fields['extra_fields'] as $key => $field ) : ?>
          <?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
 <?php endforeach; ?>
<?php 
global $woocommerce;
$amount = WC()->session->get('gbp_express_pay_session');
$name = WC()->session->get('gbp_express_name_session');
?>
<?php woocommerce_form_field( 'extra_product_name', array(
             'type' => 'text',
             'required'      => true,
             'label' => __( 'Product Name' )
             ), $name ); ?>
 <?php woocommerce_form_field( 'extra_product_price', array(
              'type' => 'text',
              'required'      => true,
              'label' => __( 'Price' )
              ), $amount ); ?>
 <?php woocommerce_form_field( 'order_comments', array(
              'type' => 'textarea',
              'required'      => false,
              'label' => __( 'Order notes' )
              ), '' ); ?>
  </div>
<?php 
}
add_action( 'woocommerce_checkout_after_customer_details' ,'kia_extra_checkout_fields' );
function kia_save_extra_checkout_fields( $order, $data ){
    // don't forget appropriate sanitization if you are using a different field type
    if( isset( $data['extra_product_price'] ) ) {
        $order->update_meta_data( '_extra_product_price', sanitize_text_field( $data['extra_product_price'] ) );
    }
    if( isset( $data['extra_product_name'] ) ) {
        $order->update_meta_data( '_extra_product_name', sanitize_text_field( $data['extra_product_name'] ) );
    }
}
add_action( 'woocommerce_checkout_create_order', 'kia_save_extra_checkout_fields', 10, 2 );
add_filter( 'woocommerce_checkout_fields' , 'misha_checkout_fields_styling', 9999 );
function misha_checkout_fields_styling( $f ) {
	$f['billing']['billing_first_name']['class'][0] = 'form-row-wide';
	$f['billing']['billing_last_name']['class'][0] = 'form-row-wide';
	return $f;
}
        $checkout = WC()->checkout();
        wc_get_template( 'checkout/form-checkout.php', array( 'checkout' => $checkout ) );
get_footer();
?>
