<?php
/*
 * Plugin Name: GBPrimePay Payments
 * Plugin URI: https://github.com/GBPrimepay
 * Description: 3-D Secure Payment Gateway Addon By GBPrimePay
 * Author: GBPrimePay
 * Author URI: https://www.gbprimepay.com
 * Version: 2.0.0
 * Text Domain: gbprimepay-payments-gateways
 */
if (!defined('ABSPATH')) {
    exit;
}
define('AS_GBPRIMEPAY_VERSION', '2.0.0');
define('AS_GBPRIMEPAY_MIN_PHP_VER', '5.3.0');
define('AS_GBPRIMEPAY_MIN_WC_VER', '2.5.0');
define('AS_GBPRIMEPAY_MAIN_FILE', __FILE__);
define('AS_GBPRIMEPAY_PLUGIN_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));
define('AS_GBPRIMEPAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AS_GBPRIMEPAY_PLUGIN_TEMPLATE_DIR', plugin_dir_path(__FILE__) . '/templates');
if (!class_exists('AS_Gbprimepay')) {
    class AS_Gbprimepay
    {
        private static $instance;
        private static $log;
        private static $gbprimepay_plugin_activation;
        private static $gbprimepay_plugin_deactivation;
        private static $express_pay_session = "gbp_express_pay_session";
        private static $express_aname_session = "gbp_express_aname_session";
        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        private function __clone()
        {
        }
        private function __wakeup()
        {
        }
        private static $logging = '';
        private static $static_link = '';
        public $notices = array();
        protected function __construct()
        {
            add_action('admin_init', array($this, 'check_environment'));
            add_action('admin_notices', array($this, 'admin_notices'), 25);
            add_action('plugins_loaded', array($this, 'init'));
            add_action('admin_menu', array($this, 'add_plugin_page'));
            // express form
            add_action("wp_head", array($this, "wp_head"));
            add_action("wp_enqueue_scripts", array($this, "wp_enqueue_scripts"), 10);
            add_action('wp_ajax_gbp_express_pay', array($this, "update_donation"));
            add_action('wp_ajax_nopriv_gbp_express_pay', array($this, "update_donation"));
            add_filter('woocommerce_coupons_enabled', array($this, 'disable_coupon_field_on_express_checkout'));
            add_filter('woocommerce_review_order_before_payment', array($this, "express_place_order_info_display"), 10, 2);
            add_action('woocommerce_is_purchasable', array(&$this, 'pewc_filter_is_purchasable'), 10, 2);
            
            add_action("template_redirect", array($this, "remove_donation"));
            add_action( 'woocommerce_update_cart_action_cart_updated', array($this, "remove_donation"));
            // qrcode
            add_action('wp_ajax_check_qrcode_order_status', array($this, 'my_check_qrcode_order_status'));
            add_action('wp_ajax_nopriv_check_qrcode_order_status', array($this, 'my_check_qrcode_order_status'));
            add_action('init', array($this, 'create_gbprimepay_qrcode_post_type'));
            add_action('init', array($this, 'create_gbprimepay_qrcode_payment_page'));
            add_filter('template_include', array($this, 'gbprimepay_qrcode_page_template'));
            // qrcredit
            add_action('wp_ajax_check_qrcredit_order_status', array($this, 'my_check_qrcredit_order_status'));
            add_action('wp_ajax_nopriv_check_qrcredit_order_status', array($this, 'my_check_qrcredit_order_status'));
            add_action('init', array($this, 'create_gbprimepay_qrcredit_post_type'));
            add_action('init', array($this, 'create_gbprimepay_qrcredit_payment_page'));
            add_filter('template_include', array($this, 'gbprimepay_qrcredit_page_template'));
            // barcode
            add_action('wp_ajax_check_barcode_order_status', array($this, 'my_check_barcode_order_status'));
            add_action('wp_ajax_nopriv_check_barcode_order_status', array($this, 'my_check_barcode_order_status'));
            add_action('init', array($this, 'create_gbprimepay_barcode_post_type'));
            add_action('init', array($this, 'create_gbprimepay_barcode_payment_page'));
            add_filter('template_include', array($this, 'gbprimepay_barcode_page_template'));
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
            add_action('woocommerce_payment_token_deleted', array($this, 'woocommerce_payment_token_deleted'), 10, 2);
// Make filter
            function filter_woocommerce_payment_gateway_get_new_payment_method_option_html_label($this_new_method_label, $instance)
            {
                $this_new_method_label = __('Use a new Credit Card', 'woocommerce');
                return $this_new_method_label;
            }
            add_filter('woocommerce_payment_gateway_get_new_payment_method_option_html_label', 'filter_woocommerce_payment_gateway_get_new_payment_method_option_html_label', 10, 2);
            add_filter('woocommerce_variable_free_price_html', 'hide_free_price_notice');
            add_filter('woocommerce_free_price_html', 'hide_free_price_notice');
			add_filter('woocommerce_variation_free_price_html', 'hide_free_price_notice');
						






/**
 * Hides the 'Free!' price notice
 */
            function hide_free_price_notice($price)
            {
                return '';
            }
            /**
             * Process the checkout
             */
            add_action('woocommerce_checkout_process', 'my_custom_extra_product_name_checkout_field_process');
            function my_custom_extra_product_name_checkout_field_process()
            {
                // Check if set, if its not set add an error.
                if (isset($_POST['extra_product_name'])) {
                    if (!$_POST['extra_product_name']) {
if (gbp_instances('LABEL_EN') == true) {                    
    wc_add_notice( sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>Product Name</strong>' ), 'error' );
}else{          
    wc_add_notice( sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>ชื่อสินค้า</strong>' ), 'error' );
}
                    }}
						}
            add_action('woocommerce_checkout_process', 'my_custom_extra_product_price_checkout_field_process');
            function my_custom_extra_product_price_checkout_field_process()
            {
                // Check if set, if its not set add an error.
                if (isset($_POST['extra_product_price'])) {
									if (!$_POST['extra_product_price']) {
										if ($_POST['extra_product_price'] < ('1')) {
if (gbp_instances('LABEL_EN') == true) {                
    wc_add_notice( sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>Product Price</strong>' ), 'error' );
}else{          
    wc_add_notice( sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>ราคาสินค้า</strong>' ), 'error' );
}
									}}}
            }
/**
 * Update the value given in custom field
 */
            add_action('woocommerce_checkout_update_order_meta', 'extra_product_name_update_order_meta');
            function extra_product_name_update_order_meta($order_id)
            {
                if (!empty($_POST['extra_product_name'])) {
                    update_post_meta($order_id, 'extra_product_name', sanitize_text_field($_POST['extra_product_name']));
                }
                if (!empty($_POST['extra_product_price'])) {
                    update_post_meta($order_id, 'extra_product_price', sanitize_text_field($_POST['extra_product_price']));
                }
            }
            add_filter('https_ssl_verify', '__return_true', PHP_INT_MAX);
            add_filter('http_request_args', 'http_request_force_ssl_verify', PHP_INT_MAX);
            function http_request_force_ssl_verify($args)
            {
                $args['sslverify'] = true;
                return $args;
            }
            remove_action('shutdown', 'wp_ob_end_flush_all', 1);
            add_action('shutdown', function () {
                while (@ob_end_flush());
            });
            register_activation_hook(__FILE__, array(&$this, 'gbprimepay_plugin_activation'));
            register_deactivation_hook(__FILE__, array(&$this, 'gbprimepay_plugin_deactivation'));
        }
        public function init()
        {
            if (self::get_environment_warning()) {
                return;
            }
            $this->init_gateways();
        }
        public function plugin_action_links($links)
        {
            $setting_link = $this->get_setting_link();
            $plugin_links = array(
                '<a href="' . $setting_link . '">' . __('Settings', 'gbprimepay-payment-gateways') . '</a>',
            );
            return array_merge($plugin_links, $links);
        }
        public function add_plugin_page()
        {
            add_menu_page(
                'GBPrimePay Account Settings',
                'GBPrimePay',
                'manage_options',
                'wc-settings&tab=gbprimepay_settings',
                array($this, 'gbprimepay_account_settings'), 'data:image/svg+xml;base64,' . $this->gbprimepay_svg()
            );
        }
        public function gbprimepay_account_settings()
        {
        }
        public function check_gbprimepaystaticlink_payment_page()
        {
            global $wpdb;
            $post_title = 'Express Payments';
            $post_name = 'express-payments';
            $ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_name LIKE '%%%s%%'", $post_title, $post_name));
            if (count($ids) > 1) {
                return $ids;
            } else {
                return 0;
            }
        }
        // Express Form
        public function create_gbprimepaystaticlink_payment_page()
        {
            global $wpdb;
            $totalpost = $this->check_gbprimepaystaticlink_payment_page();
            $guid = home_url('/express-payments/');
            if ($totalpost) {
                return;
            } else {
                register_post_type('gbprimepaystaticlink',
                    array(
                        'labels' => array(
                            'name' => __('GBPrimePay Static Link'),
                            'singular_name' => __('GBPrimePay Static Link'),
                        ),
                        'public' => true,
                        'has_archive' => false,
                        'publicly_queryable' => true,
                        'exclude_from_search' => true,
                        'show_in_menu' => true,
                        'show_in_nav_menus' => true,
                        'show_in_admin_bar' => false,
                        'show_in_rest' => false,
                        'hierarchical' => false,
                        'supports' => false,
                    )
                );
                flush_rewrite_rules();
                $page_data = array(
                    'posts_per_page' => 1,
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_author' => '1',
                    'post_title' => 'Express Payments',
                    'menu_order' => '100',
                    'post_content' => '[woocommerce_checkout]',
                    'comment_status' => 'closed',
                    'page_template' => 'template-fullwidth.php',
                    'guid' => $guid,
                );
                $gbp_express_post_id = wp_insert_post($page_data);
                $eid = update_option('gbp_express_post_id', $gbp_express_post_id);
                $item = array(
                    'Name' => 'Custom Price',
                    'Description' => 'GBPrimePay Price',
                    'Price' => '0.001',
                    'SKU' => '100' . $gbp_express_post_id . 'GBP',
                );
                $gbp_express_product_id = wp_insert_post(array(
                    'post_author' => 1,
                    'post_title' => $item['Name'],
                    'post_content' => $item['Description'],
                    'post_status' => 'publish',
                    'post_type' => "product",
                ));
                wp_set_object_terms($gbp_express_product_id, 'simple', 'product_type');
                update_post_meta($gbp_express_product_id, '_visibility', 'hidden');
                update_post_meta($gbp_express_product_id, '_stock_status', 'instock');
                update_post_meta($gbp_express_product_id, 'total_sales', '0');
                update_post_meta($gbp_express_product_id, '_downloadable', 'no');
                update_post_meta($gbp_express_product_id, '_virtual', 'yes');
                update_post_meta($gbp_express_product_id, '_regular_price', $item['Price']);
                update_post_meta($gbp_express_product_id, '_sale_price', $item['Price']);
                update_post_meta($gbp_express_product_id, '_purchase_note', '');
                update_post_meta($gbp_express_product_id, '_featured', 'no');
                update_post_meta($gbp_express_product_id, '_weight', '');
                update_post_meta($gbp_express_product_id, '_length', '');
                update_post_meta($gbp_express_product_id, '_width', '');
                update_post_meta($gbp_express_product_id, '_height', '');
                update_post_meta($gbp_express_product_id, '_sku', $item['SKU']);
                update_post_meta($gbp_express_product_id, '_product_attributes', array());
                update_post_meta($gbp_express_product_id, '_sale_price_dates_from', '');
                update_post_meta($gbp_express_product_id, '_sale_price_dates_to', '');
                update_post_meta($gbp_express_product_id, '_price', $item['Price']);
                update_post_meta($gbp_express_product_id, '_sold_individually', '');
                update_post_meta($gbp_express_product_id, '_manage_stock', 'no');
                update_post_meta($gbp_express_product_id, '_backorders', 'no');
                update_post_meta($gbp_express_product_id, '_stock', '');
                $pid = update_option('gbp_express_product_id', $gbp_express_product_id);
                $terms = array('exclude-from-search', 'exclude-from-catalog');
                wp_set_post_terms($gbp_express_product_id, $terms, 'product_visibility', false);
            }
        }
        public function delete_gbprimepaystaticlink_payment_page()
        {
            global $wpdb;
            $gbp_express_post_id = get_option('gbp_express_post_id');
            if (isset($gbp_express_post_id)) {
                wp_delete_post($gbp_express_post_id, true);
                update_option('gbp_express_post_id', 0);
            }
            $gbp_express_product_id = get_option('gbp_express_product_id');
            if (isset($gbp_express_product_id)) {
                wp_delete_post($gbp_express_product_id, true);
                update_option('gbp_express_product_id', 0);
            }
        }
        public function pewc_filter_is_purchasable($is_purchasable, $product)
        {
            if (is_archive()) {
                return false;
            }
            return $is_purchasable;
        }
        public function wp_head()
        {
            global $wp_query;
            $pagename = get_query_var('pagename');
            if (get_post_type() && get_post_type() === 'page') {
                if ($pagename && $pagename === 'express-payments') {
                    ?>
                <script>
                    var express_ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
                </script>
                <style type="text/css">
                    .text-donation{
                        max-width: 100%;
                        display: inline-block !important;
                        min-height:28px;
                        line-height: 28px;
                        margin-right: 10px;
                    }
                    .rp-donation-block .donate-remove{display:none !important;}
                    .rp-donation-block p.message{margin-bottom: 10px;}
                    .checkout_donation{display: block;width:100%;}
                    .rpdo-error{display: none;color:red;font-size: 12px;margin-top: 5px;}
                    .rpdo_loader{display: none;height: 16px;width: 16px;}
                    .rp-donation-block .show_remove.donate-remove{display:inline-block !important;}
                </style>
								<?php
}
            }
        }
        public function set_session() {
            if ( ! is_product() || ( isset( WC()->session ) && WC()->session->has_session() ) ) {
                return;
            }

            WC()->session->set_customer_session_cookie( true );
        }
        public function wk_update_price($cart_object)
        {
            global $woocommerce;
            $extra_product_price = WC()->session->get('gbp_express_pay_session');
            $extra_product_name = WC()->session->get('gbp_express_name_session');
            $cart_items = $cart_object->cart_contents;
            if (!empty($cart_items)) {
                $price = $extra_product_price;
                $gbp_express_product_id = get_option('gbp_express_product_id');
                foreach ($cart_items as $key => $value) {
                    if ($value['product_id'] == $gbp_express_product_id) {
                        $value['data']->set_price($price);
                    }
                }
            }}
        public function express_place_order_info_display()
        {
            if( (is_page( 'express-payments' ))) {
                echo '<div id="gbprimepay_express-info" name="gbprimepay_express-info" class="form-control" style="margin:10px 0 30px 0;"></div>';
            } 
        }
        public function add_custom_donation_price($_cart)
        {
            // loop through the cart_contents
            $gbp_express_product_id = get_option('gbp_express_product_id');
            $donation_amount = $this->get_donation_amount();
            foreach ($_cart->cart_contents as $cart_item_key => &$item) {
                if ($item['product_id'] == $gbp_express_product_id && isset($donation_amount)) {
                    $custom_price = $donation_amount > 0 ? $donation_amount : 0;
                    $item['data']->set_price($custom_price);
                }
            }
        }
        
        /* add donation */
        public function update_donation()
        {   
            $raw_post = @file_get_contents('php://input');
            parse_str(html_entity_decode($raw_post), $payload);
            if( !empty($payload['amount']) ){
                $this->set_donation_amount($payload['amount']);
                parse_str($payload['amount'], $payloaddata);
            }
            if( !empty($payload['aname']) ){
                $this->set_donation_aname($payload['aname']);
                parse_str($payload['aname'], $payloaddata);
            }
            $ret = array();
            $ret['amount'] = $payload['amount'];
            $ret['aname'] = $payload['aname'];
            $ret['info_display'] = $this->display_donation($ret['amount'],$ret['aname']);
            die(json_encode($ret));

        }
        public function display_donation($extra_product_price,$extra_product_name)
        {
            if(( ($extra_product_price < 1) ) && ( empty($extra_product_name) )){
                $ret  = '';
                
                $ret  .= '<table style="border: none !important; text-align: center; vertical-align: middle; margin: 0; line-height: 1 !important;" border="0" width="100%" cellspacing="0" cellpadding="0">';
                $ret  .= '<tbody>';
                $ret  .= '<tr style="border: none !important; padding: 0;">';
                $ret  .= '<td style="border: none !important; padding: 0;background-color: transparent;line-height: 1 !important;" width="100%" height="0px;"></td>';
                $ret  .= '</tr>';
                $ret  .= '</tbody>';
                $ret  .= '</table>';
            }else{
                $ret  = '';
                
                $ret  .= '<table style="border: none !important; text-align: center; vertical-align: middle; margin: 0; line-height: 1 !important;" border="0" width="100%" cellspacing="0" cellpadding="0">';
                $ret  .= '<tbody>';
                $ret  .= '<tr style="border: none !important; padding: 0;">';
                $ret  .= '<td style="border: none !important; padding: 0;" width="2px;" height="100%">&nbsp;</td>';
                $ret  .= '<td style="border: none !important; padding: 0;" width="100%" height="100%">';
                $ret  .= '<table style="border: none !important; text-align: center; vertical-align: middle; margin: 0; padding: 20px 0px; line-height: 1 !important;" border="0" width="100%" cellspacing="0" cellpadding="0">';
                $ret  .= '<tbody>';
                $ret  .= '<tr style="border: none !important; padding: 0;">';
                $ret  .= '<td style="border: none !important; text-align: center; vertical-align: middle; margin: 0; padding: 10px 0 0 0;" colspan="3" width="100%" height="100%">';
                $ret  .= '<h1 style="border: none !important; width: 100%; margin: 0; padding: 0 5px 0 0; line-height: 1;"><strong>' . $extra_product_name . '</strong></h1>';
                $ret  .= '</td>';
                $ret  .= '</tr>';
                $ret  .= '<tr style="border: none !important; padding: 0;">';
                $ret  .= '<td style="border: none !important; text-align: center; vertical-align: middle; margin: 0; padding: 10px 0 10px 0;background-color: transparent;" colspan="3" width="100%" height="100%">';
                $ret  .= '<span style="border: none !important; margin: 0; padding: 0 15px 0 0; line-height: 1 !important;">Custom Price:</span><span style="border: none !important; margin: 0; padding: 0; line-height: 1 !important;"><strong>' . $this->display_money_format($extra_product_price) . '</strong></span></td>';
                $ret  .= '</tr>';
                $ret  .= '</tbody>';
                $ret  .= '</table>';
                $ret  .= '</td>';
                $ret  .= '<td style="border: none !important; padding: 0;" width="100%" height="100%">&nbsp;</td>';
                $ret  .= '</tr>';
                $ret  .= '</tbody>';
                $ret  .= '</table>';
                }
            return $ret;
        }
        
	function display_money_format($amount, $format = '', $digit = 2){
		if( empty($format) ){
			$format = '฿NUMBER';
		}
		if( strpos($format, 'NUMBER') === false ){
			$format .= 'NUMBER';
        }
		return str_replace('NUMBER', number_format($amount, $digit), $format);
	}
		/* remove donation */
        public function remove_donation()
        {
					if( !(is_page( 'express-payments' ))) {			
						$gbp_express_product_id = get_option('gbp_express_product_id');
						$product_cart_id = WC()->cart->generate_cart_id($gbp_express_product_id);
                        $cartItemKey = WC()->cart->find_product_in_cart( $product_cart_id );              
                        WC()->cart->remove_cart_item( $cartItemKey );
					}else{
						if( (is_page( 'express-payments' ))) {
							
							$extra_product_price = WC()->session->get('gbp_express_pay_session');
							if($extra_product_price){
								WC()->session->set( 'gbp_express_pay_session', '0.001' );
						}}
					}	
                }
                

        /* set donation amount to woo sesssion */
        public function set_donation_amount($amount)
        {
            global $woocommerce;
            $woocommerce->session->set(self::$express_pay_session, $amount);
        }
        public function set_donation_aname($aname)
        {
            global $woocommerce;
            $woocommerce->session->set(self::$express_aname_session, $aname);
        }
        /* get donation amount from woo session */
        public function get_donation_amount()
        {
            global $woocommerce;
            $amount = $woocommerce->session->get(self::$express_pay_session);
            if ($amount && is_numeric($amount)) {
                return $amount;
            }
            return "0";
        }
        public function get_donation_aname()
        {
            global $woocommerce;
            $aname = $woocommerce->session->get(self::$express_aname_session);
            if ($aname && !empty($aname)) {
                return $aname;
            }
            return "";
        }
        public function disable_coupon_field_on_express_checkout($enabled)
        {
            global $wp_query;
            $pagename = get_query_var('pagename');
            if (get_post_type() && get_post_type() === 'page') {
                if ($pagename && $pagename === 'express-payments') {
                    $enabled = false;
                }
            } else {
                return $enabled;
            }
        }
        public function wp_enqueue_scripts()
        {
            global $wp_query;
            $pagename = get_query_var('pagename');
            if (get_post_type() && get_post_type() === 'page') {
                if ($pagename && $pagename === 'express-payments') {
                    wp_enqueue_style('express-payments-style',
                        AS_GBPRIMEPAY_PLUGIN_URL . '/assets/css/express-payments-style.css');
                }
            }
        }
        public function gbprimepaystaticlink_page_template($page_template)
        {
            global $wp_query;
            $pagename = get_query_var('pagename');
            if (get_post_type() && get_post_type() === 'page') {
                if ($pagename && $pagename === 'express-payments') {
                    return dirname(__FILE__) . '/templates/gbprimepay-gateway-express.php';
                }
            }
            return $page_template;
        }
        // QR Code
        public function my_check_qrcode_order_status($order_id)
        {
            // $gateway = new AS_Gateway_Gbprimepay_Qrcode;
            $order_id = $_POST['order_id'];
            // $order_id = 685;
            $order = wc_get_order($order_id);
            $order_data = $order->get_data();
            // If order is "completed" or "processing", we can give confirmation that payment has gone through
            if ($order_data['status'] == 'completed' || $order_data['status'] == 'processing') {
                echo 1; // Payment completed
            } elseif ($order_data['status'] == 'pending') {
                echo 0; // Payment not completed
            }
            // Always end AJAX-printing scripts with die();
            die();
        }
        public function create_gbprimepay_qrcode_post_type()
        {
            register_post_type('gbprimepay_qrcode',
                array(
                    'labels' => array(
                        'name' => __('GBPrimePay QR Code'),
                        'singular_name' => __('GBPrimePay QR Code'),
                    ),
                    'public' => true,
                    'has_archive' => false,
                    'publicly_queryable' => true,
                    'exclude_from_search' => true,
                    'show_in_menu' => false,
                    'show_in_nav_menus' => false,
                    'show_in_admin_bar' => false,
                    'show_in_rest' => false,
                    'hierarchical' => false,
                    'supports' => array('title'),
                )
            );
            flush_rewrite_rules();
        }
        public function create_gbprimepay_qrcode_payment_page()
        {
            global $wpdb;
            // Get the ID of our custom payments page from settings
            $qrcode_post_id = get_option('qrcode_post_id');
            // Create a custom GUID (URL) for our custom for our payments page
            $guid = home_url('/gbprimepay_qrcode/pay');
            if ($qrcode_post_id && get_post_type($qrcode_post_id) == "gbprimepay_qrcode" && get_the_guid($qrcode_post_id) == $guid) {
                // Post already created, so return
                return;
            } else {
                // Put together data to create the custom post
                $page_data = array(
                    'post_status' => 'publish',
                    'post_type' => 'gbprimepay_qrcode',
                    'post_title' => 'pay',
                    'post_content' => 'GBPrimePay QR Code',
                    'comment_status' => 'closed',
                    'guid' => $guid,
                );
                // Create the post
                $qrcode_post_id = wp_insert_post($page_data);
                // Update our settings with the ID of the newly created post
                $ppp = update_option('qrcode_post_id', $qrcode_post_id);
            }
        }
        public function gbprimepay_qrcode_page_template($page_template)
        {
            if (get_post_type() && get_post_type() === 'gbprimepay_qrcode') {
                return dirname(__FILE__) . '/templates/gbprimepay-gateway-qrcode.php';
            }
            return $page_template;
        }
        // QR Credit
        public function my_check_qrcredit_order_status($order_id)
        {
            // $gateway = new AS_Gateway_Gbprimepay_Qrcredit;
            $order_id = $_POST['order_id'];
            // $order_id = 685;
            $order = wc_get_order($order_id);
            $order_data = $order->get_data();
            // If order is "completed" or "processing", we can give confirmation that payment has gone through
            if ($order_data['status'] == 'completed' || $order_data['status'] == 'processing') {
                echo 1; // Payment completed
            } elseif ($order_data['status'] == 'pending') {
                echo 0; // Payment not completed
            }
            // Always end AJAX-printing scripts with die();
            die();
        }
        public function create_gbprimepay_qrcredit_post_type()
        {
            register_post_type('gbprimepay_qrcredit',
                array(
                    'labels' => array(
                        'name' => __('GBPrimePay QR Code'),
                        'singular_name' => __('GBPrimePay QR Code'),
                    ),
                    'public' => true,
                    'has_archive' => false,
                    'publicly_queryable' => true,
                    'exclude_from_search' => true,
                    'show_in_menu' => false,
                    'show_in_nav_menus' => false,
                    'show_in_admin_bar' => false,
                    'show_in_rest' => false,
                    'hierarchical' => false,
                    'supports' => array('title'),
                )
            );
            flush_rewrite_rules();
        }
        public function create_gbprimepay_qrcredit_payment_page()
        {
            global $wpdb;
            // Get the ID of our custom payments page from settings
            $qrcredit_post_id = get_option('qrcredit_post_id');
            // Create a custom GUID (URL) for our custom for our payments page
            $guid = home_url('/gbprimepay_qrcredit/pay');
            if ($qrcredit_post_id && get_post_type($qrcredit_post_id) == "gbprimepay_qrcredit" && get_the_guid($qrcredit_post_id) == $guid) {
                // Post already created, so return
                return;
            } else {
                // Put together data to create the custom post
                $page_data = array(
                    'post_status' => 'publish',
                    'post_type' => 'gbprimepay_qrcredit',
                    'post_title' => 'pay',
                    'post_content' => 'GBPrimePay QR Code',
                    'comment_status' => 'closed',
                    'guid' => $guid,
                );
                // Create the post
                $qrcredit_post_id = wp_insert_post($page_data);
                // Update our settings with the ID of the newly created post
                $ppp = update_option('qrcredit_post_id', $qrcredit_post_id);
            }
        }
        public function gbprimepay_qrcredit_page_template($page_template)
        {
            if (get_post_type() && get_post_type() === 'gbprimepay_qrcredit') {
                return dirname(__FILE__) . '/templates/gbprimepay-gateway-qrcredit.php';
            }
            return $page_template;
        }
        // Bill Payment
        public function my_check_barcode_order_status($order_id)
        {
            // $gateway = new AS_Gateway_Gbprimepay_Barcode;
            $order_id = $_POST['order_id'];
            // $order_id = 685;
            $order = wc_get_order($order_id);
            $order_data = $order->get_data();
            // If order is "completed" or "processing", we can give confirmation that payment has gone through
            if ($order_data['status'] == 'completed' || $order_data['status'] == 'processing') {
                echo 1; // Payment completed
            } elseif ($order_data['status'] == 'pending') {
                echo 0; // Payment not completed
            }
            // Always end AJAX-printing scripts with die();
            die();
        }
        public function create_gbprimepay_barcode_post_type()
        {
            register_post_type('gbprimepay_barcode',
                array(
                    'labels' => array(
                        'name' => __('GBPrimePay Bill Payment'),
                        'singular_name' => __('GBPrimePay Bill Payment'),
                    ),
                    'public' => true,
                    'has_archive' => false,
                    'publicly_queryable' => true,
                    'exclude_from_search' => true,
                    'show_in_menu' => false,
                    'show_in_nav_menus' => false,
                    'show_in_admin_bar' => false,
                    'show_in_rest' => false,
                    'hierarchical' => false,
                    'supports' => array('title'),
                )
            );
            flush_rewrite_rules();
        }
        public function create_gbprimepay_barcode_payment_page()
        {
            global $wpdb;
            // Get the ID of our custom payments page from settings
            $barcode_post_id = get_option('barcode_post_id');
            // Create a custom GUID (URL) for our custom for our payments page
            $guid = home_url('/gbprimepay_barcode/pay');
            if ($barcode_post_id && get_post_type($barcode_post_id) == "gbprimepay_barcode" && get_the_guid($barcode_post_id) == $guid) {
                // Post already created, so return
                return;
            } else {
                // Put together data to create the custom post
                $page_data = array(
                    'post_status' => 'publish',
                    'post_type' => 'gbprimepay_barcode',
                    'post_title' => 'pay',
                    'post_content' => 'GBPrimePay Bill Payment',
                    'comment_status' => 'closed',
                    'guid' => $guid,
                );
                // Create the post
                $barcode_post_id = wp_insert_post($page_data);
                // Update our settings with the ID of the newly created post
                $ppp = update_option('barcode_post_id', $barcode_post_id);
            }
        }
        public function gbprimepay_barcode_page_template($page_template)
        {
            if (get_post_type() && get_post_type() === 'gbprimepay_barcode') {
                return dirname(__FILE__) . '/templates/gbprimepay-gateway-barcode.php';
            }
            return $page_template;
        }
        protected function gbprimepay_svg()
        {
            return base64_encode('<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="-279 369 55 55" style="enable-background:new -279 369 55 55;" xml:space="preserve"><style type="text/css">.st0{fill:#004071}.st1{fill:#FFF}</style><g id="Layer_2_1_"> </g> <g id="Layer_1"> </g> <g id="Layer_3"> <g> <path class="st0" d="M-226.6,395.2v-16.7c0-2.7-2.2-4.9-4.9-4.9h-40.1c-2.7,0-4.9,2.2-4.9,4.9v16.7 C-276.4,395.2-226.6,395.2-226.6,395.2z"/> <path class="st1" d="M-276.4,395.1v19.4c0,2.7,2.2,4.9,4.9,4.9h40.1c2.7,0,4.9-2.2,4.9-4.9v-19.4H-276.4z"/> <g> <path class="st1" d="M-240,391.2c0-0.3-0.2-0.6-0.6-0.6h-2.6c-0.3,0-0.6,0.2-0.6,0.6v2.6c0,0.3,0.2,0.6,0.6,0.6h2.6 c0.3,0,0.6-0.2,0.6-0.6V391.2z"/> <path class="st1" d="M-236.9,383.4c0-0.4-0.3-0.7-0.7-0.7h-3.5c-0.4,0-0.7,0.3-0.7,0.7v3.5c0,0.4,0.3,0.7,0.7,0.7h3.5 c0.4,0,0.7-0.3,0.7-0.7V383.4z"/> <path class="st1" d="M-236.4,376c0-0.3-0.2-0.6-0.6-0.6h-2.6c-0.3,0-0.6,0.2-0.6,0.6v2.6c0,0.3,0.2,0.6,0.6,0.6h2.6 c0.3,0,0.6-0.2,0.6-0.6V376L-236.4,376z"/> <path class="st1" d="M-229.1,387.4c0-0.3-0.2-0.6-0.6-0.6h-2.6c-0.3,0-0.6,0.2-0.6,0.6v2.6c0,0.3,0.2,0.6,0.6,0.6h2.6 c0.3,0,0.6-0.2,0.6-0.6V387.4z"/> <path class="st1" d="M-235.6,389.7c0-0.2-0.2-0.4-0.4-0.4h-1.9c-0.2,0-0.4,0.2-0.4,0.4v1.9c0,0.2,0.2,0.4,0.4,0.4h1.9 c0.2,0,0.4-0.2,0.4-0.4V389.7z"/> <path class="st1" d="M-232.8,380.3c0-0.2-0.1-0.3-0.3-0.3h-1.4c-0.2,0-0.3,0.1-0.3,0.3v1.4c0,0.2,0.1,0.3,0.3,0.3h1.4 c0.2,0,0.3-0.1,0.3-0.3V380.3z"/> <path class="st1" d="M-241.8,377.9c0-0.2-0.1-0.3-0.3-0.3h-1.4c-0.2,0-0.3,0.1-0.3,0.3v1.4c0,0.2,0.1,0.3,0.3,0.3h1.4 c0.2,0,0.3-0.1,0.3-0.3V377.9z"/> <path class="st1" d="M-229,376.6c0-0.2-0.1-0.3-0.3-0.3h-1.4c-0.2,0-0.3,0.1-0.3,0.3v1.4c0,0.2,0.1,0.3,0.3,0.3h1.4 c0.2,0,0.3-0.1,0.3-0.3V376.6z"/> </g> <g> <path class="st1" d="M-261,381.1h2.5c1.1,0,1.9,0.2,2.5,0.6c0.9,0.6,1.3,1.3,1.3,2.3c0,0.9-0.4,1.6-1.2,2.1 c1.2,0.5,1.8,1.4,1.8,2.7c0,1.1-0.4,1.9-1.3,2.5c-0.4,0.3-0.9,0.4-1.4,0.5c-0.3,0-0.8,0.1-1.5,0.1h-2.5V381.1z M-258.8,385.7 c0.5,0,0.9,0,1,0c0.8-0.1,1.3-0.4,1.6-0.9c0.2-0.3,0.2-0.6,0.2-0.9c0-0.6-0.3-1.1-0.9-1.5c-0.4-0.2-1-0.3-2-0.3h-1.3v3.7H-258.8z M-258.6,390.9c0.6,0,1,0,1.2,0c0.9-0.1,1.5-0.4,1.9-1c0.2-0.3,0.3-0.7,0.3-1c0-1-0.4-1.6-1.3-1.9c-0.4-0.1-1.1-0.2-1.9-0.2h-1.4 v4.2H-258.6L-258.6,390.9z"/> <path class="st1" d="M-263.2,386.9h-6.8v1h5.6c0.2,0,0.4,0.1,0.4,0.4c-0.3,0.8-0.8,1.3-1.6,1.8c-0.8,0.6-1.7,0.9-2.7,0.9 c-1.3,0-2.4-0.4-3.4-1.3c-0.9-0.9-1.4-2-1.4-3.3c0-1.3,0.5-2.4,1.5-3.3c0.9-0.8,2-1.2,3.2-1.2c0.8,0,1.5,0.2,2.2,0.5 c0.7,0.4,1.3,0.9,1.7,1.5h1.2c-0.3-0.8-0.9-1.5-1.9-2.1c-0.9-0.6-2-0.9-3.2-0.9c-1.6,0-3,0.6-4.1,1.7c-1.1,1.1-1.7,2.4-1.7,3.9 c0,1.6,0.6,2.9,1.7,4c1.1,1.1,2.5,1.6,4.1,1.6c1.5,0,2.8-0.5,3.9-1.4c1.1-0.9,1.6-1.9,1.8-3.3 C-262.8,387.1-262.9,386.9-263.2,386.9z"/> <path class="st1" d="M-264.6,384c0,0,0.4,0.8,0.9,0.7c0.6-0.1,0.4-0.6,0.3-0.9L-264.6,384z"/> </g> <g> <path class="st0" d="M-272.3,397.8c0.4,0,0.6,0.3,0.6,0.6v3.1c0,0.4-0.3,0.6-0.6,0.6h-3.1c-0.4,0-0.6-0.3-0.6-0.6v-3.1 c0-0.4,0.3-0.6,0.6-0.6L-272.3,397.8L-272.3,397.8z"/> <path class="st0" d="M-266.8,402.9c0.3,0,0.5,0.2,0.5,0.5v2.5c0,0.3-0.2,0.5-0.5,0.5h-2.5c-0.3,0-0.5-0.2-0.5-0.5v-2.5 c0-0.3,0.2-0.5,0.5-0.5H-266.8z"/> <path class="st0" d="M-272.5,405.9c0.2,0,0.4,0.2,0.4,0.4v1.9c0,0.2-0.2,0.4-0.4,0.4h-1.9c-0.2,0-0.4-0.2-0.4-0.4v-1.9 c0-0.2,0.2-0.4,0.4-0.4H-272.5z"/> <path class="st0" d="M-267.7,410c0.2,0,0.4,0.2,0.4,0.4v1.9c0,0.2-0.2,0.4-0.4,0.4h-1.9c-0.2,0-0.4-0.2-0.4-0.4v-1.9 c0-0.2,0.2-0.4,0.4-0.4H-267.7z"/> <path class="st0" d="M-272.1,413.4c0.1,0,0.2,0.1,0.2,0.2v1.2c0,0.1-0.1,0.2-0.2,0.2h-1.2c-0.1,0-0.2-0.1-0.2-0.2v-1.2 c0-0.1,0.1-0.2,0.2-0.2H-272.1z"/> <path class="st0" d="M-267.6,398.1c0.1,0,0.2,0.1,0.2,0.2v1.2c0,0.1-0.1,0.2-0.2,0.2h-1.2c-0.1,0-0.2-0.1-0.2-0.2v-1.2 c0-0.1,0.1-0.2,0.2-0.2H-267.6z"/> </g> <g> <path class="st0" d="M-263.8,399.1h3.6c1.6,0,2.9,0.3,3.7,0.8c1.2,0.7,1.8,1.9,1.8,3.4c0,1-0.3,1.9-0.9,2.6 c-0.6,0.7-1.4,1.1-2.4,1.3c-0.5,0.1-1.1,0.2-1.9,0.2h-2.6v5.6h-1.4V399.1z M-260,406.1c0.7,0,1.1,0,1.4-0.1 c0.5-0.1,0.9-0.2,1.3-0.5c0.8-0.5,1.2-1.3,1.2-2.3c0-1.2-0.6-2.1-1.7-2.6c-0.5-0.2-1.2-0.3-2.1-0.3c-0.1,0-0.2,0-0.4,0 c-0.2,0-0.3,0-0.4,0h-1.8v5.7L-260,406.1L-260,406.1z"/> <path class="st0" d="M-248,399.1h1.5l5.9,13.8h-1.5l-2-4.6h-6.4l-2,4.6h-1.5L-248,399.1z M-244.6,407l-2.7-6.3l-2.7,6.3H-244.6z" /> <path class="st0" d="M-235.5,408.3l-4.8-9.2h1.5l4,7.7l4-7.7h1.5l-4.8,9.2v4.6h-1.4L-235.5,408.3L-235.5,408.3z"/> </g> </g> </g> </svg>');
        }
        public function add_admin_notice($slug, $class, $style, $message)
        {
            $this->notices[$slug] = array(
                'class' => $class,
                'style' => $style,
                'message' => $message,
            );
        }
        public function check_environment()
        {
            $static_link_warning = __('Missing, Express Payments. Please, Deactivate and Activate GBPrimePay Payments', 'gbprimepay-payment-gateways');
            $environment_warning = self::get_environment_warning();
            $available_warning = AS_Gbprimepay_API::check_is_available();
            if ($environment_warning && is_plugin_active(plugin_basename(__FILE__))) {
                $this->add_admin_notice('gbprimepay_bad_environment', 'error', 'normal', $environment_warning);
            }
            if (!$environment_warning && $available_warning != 0) {
                $get_tab = (isset($_GET['tab'])) ? $_GET['tab'] : '';
                if ($get_tab != 'gbprimepay_settings') {
                    $setting_link = $this->get_setting_link();
                    $this->add_admin_notice('gbprimepay_prompt_connect', 'notice notice-warning', 'defective', sprintf(__('Error!, Missing credentials, GBPrimePay Payments will not work until you <br><br><a href="%s">configure your GB Prime Pay api keys</a>.', 'gbprimepay-payment-gateways'), $setting_link));
                }
            }
            if (self::get_static_link() && get_option('gbp_express_post_id') == 0) {
                $this->add_admin_notice('gbprimepay_bad_environment', 'error', 'normal', $static_link_warning);
            }
        }
        public static function get_logging()
        {
            if (empty(self::$logging)) {
                $options = get_option('gbprimepay_account_settings');
                if (isset($options['logging'])) {
                    self::$logging = ('yes' === $options['logging'] ? $options['logging'] : '');
                }
            }
            return self::$logging;
        }
        public static function get_static_link()
        {
            if (empty(self::$static_link)) {
                $options = get_option('gbprimepay_account_settings');
                if (isset($options['static_link'])) {
                    self::$static_link = ('yes' === $options['static_link'] ? $options['static_link'] : '');
                }
            }
            return self::$static_link;
        }
        public static function get_environment_warning()
        {
            if (version_compare(phpversion(), AS_GBPRIMEPAY_MIN_PHP_VER, '<')) {
                /* translators: %1$s is replaced with the php version %2$s is replaced with the current php version */
                $message = __('GBPrimePay Payments - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'gbprimepay-payment-gateways');
                return sprintf($message, AS_GBPRIMEPAY_MIN_PHP_VER, phpversion());
            }
            if (!defined('WC_VERSION')) {
                return __('GBPrimePay Payments requires WooCommerce to be activated to work.', 'gbprimepay-payment-gateways');
            }
            if (version_compare(WC_VERSION, AS_GBPRIMEPAY_MIN_WC_VER, '<')) {
                /* translators: %1$s is replaced with the woocommerce version %2$s is replaced with the current woocommerce version */
                $message = __('GBPrimePay Payments - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'gbprimepay-payment-gateways');
                return sprintf($message, AS_GBPRIMEPAY_MIN_WC_VER, WC_VERSION);
            }
            if (!function_exists('curl_init')) {
                return __('GBPrimePay Payments - cURL PHP extension is not installed.', 'gbprimepay-payment-gateways');
            }
            if (!function_exists('json_decode')) {
                return __('GBPrimePay Payments - JSON PHP extension is not installed.', 'gbprimepay-payment-gateways');
            }
            return false;
        }
        public function get_setting_link()
        {
            return admin_url('admin.php?page=wc-settings&tab=gbprimepay_settings');
        }
        public function admin_notices()
        {
            foreach ((array) $this->notices as $notice_key => $notice) {
                if ($notice['style'] == 'defective') {
                    echo "<div class='" . esc_attr($notice['class']) . "'  style='padding: 12px 12px;'><p><strong>";
                    echo wp_kses($notice['message'], array(
                        'a' => array(
                            'href' => array(),
                        ),
                    ));
                    echo '</strong></p></div>';
                } else {
                    echo "<div class='" . esc_attr($notice['class']) . "'><p>";
                    echo wp_kses($notice['message'], array(
                        'a' => array(
                            'href' => array(),
                        ),
                    ));
                    echo '</p></div>';
                }
            }
        }
        private static function _update_plugin_version()
        {
            update_option('as_gbprimepay_version', AS_GBPRIMEPAY_VERSION);
            return true;
        }
        public function install()
        {
            if (!defined('AS_GBPRIMEPAY_INSTALLING')) {
                define('AS_GBPRIMEPAY_INSTALLING', true);
            }
            $this->_update_plugin_version();
        }
        public function woocommerce_payment_token_deleted($token_id, $token)
        {
            if ('gbprimepay' === $token->get_gateway_id()) {
                $gbprimepay_api_obj = new AS_Gbprimepay_API();
                $gbprimepay_api_obj->deleteCardAccount($token->get_token());
            }
        }
        public function init_gateways()
        {
            if (!class_exists('WC_Payment_Gateway')) {
                return;
            }
            include_once dirname(__FILE__) . '/includes/class-as-gbprimepay-api.php';
            include_once dirname(__FILE__) . '/includes/customer/class-as-gbprimepay-user-account.php';
            include_once dirname(__FILE__) . '/includes/include-code/instances.php';
            include_once dirname(__FILE__) . '/includes/class-as-gbprimepay-account.php';
            include_once dirname(__FILE__) . '/includes/class-as-gbprimepay-gateway.php';
            include_once dirname(__FILE__) . '/includes/class-as-gbprimepay-gateway-installment.php';
            include_once dirname(__FILE__) . '/includes/class-as-gbprimepay-gateway-qrcode.php';
            include_once dirname(__FILE__) . '/includes/class-as-gbprimepay-gateway-qrcredit.php';
            include_once dirname(__FILE__) . '/includes/class-as-gbprimepay-gateway-barcode.php';
            include_once dirname(__FILE__) . '/includes/front-end-functions.php';
            include_once dirname(__FILE__) . '/includes/back-end-functions.php';
            add_filter('woocommerce_payment_gateways', array($this, 'add_gateways'));
        }
        public function add_gateways($methods)
        {
            $methods[] = 'AS_Gateway_Gbprimepay';
            $methods[] = 'AS_Gateway_Gbprimepay_Installment';
            $methods[] = 'AS_Gateway_Gbprimepay_Qrcode';
            $methods[] = 'AS_Gateway_Gbprimepay_Qrcredit';
            $methods[] = 'AS_Gateway_Gbprimepay_Barcode';
            return $methods;
        }
        public static function log($message)
        {
            if (empty(self::$log)) {
                self::$log = new WC_Logger();
            }
            if (empty(self::get_logging())) {
                return false;
            } else {
                self::$log->debug($message, array('source' => 'gbprimepay-payment-gateways'));
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log($message);
                }
            }
        }
        public function gbprimepay_plugin_activation()
        {
            global $wpdb;
            $version = get_option('as_gbprimepay_version');
            if (version_compare($version, AS_GBPRIMEPAY_VERSION, '<>')) {
                update_option('as_gbprimepay_version', AS_GBPRIMEPAY_VERSION);
            }
            $options = get_option('gbprimepay_account_settings');
            $gbp_express_post_id = get_option('gbp_express_post_id');
            if (isset($options['static_link']) and ('yes' === $options['static_link'])) {
                $totalpost = self::check_gbprimepaystaticlink_payment_page();
                if ($totalpost) {} else {self::create_gbprimepaystaticlink_payment_page();}
            } else {
            }
        }
        public function gbprimepay_plugin_deactivation()
        {
            global $wpdb;
            $options = get_option('gbprimepay_account_settings');
            $gbp_express_post_id = get_option('gbp_express_post_id');
            if (isset($gbp_express_post_id)) {
                self::delete_gbprimepaystaticlink_payment_page();
            }
        }
    }
    $GLOBALS['as_gbprimepay'] = AS_Gbprimepay::get_instance();
}
