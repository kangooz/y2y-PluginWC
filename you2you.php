<?php
/**
 * Plugin Name: You2You WooCommerce Shipping Method
 * Plugin URI: http://www.you2you.fr
 * Description: 
 * Version: 1.0.0
 * Author: You2You
 * Author URI: http://www.you2you.fr
 *
 * Text Domain: y2ywsm
 *
 * @author You2you
 */

if( !defined('Y2YWSM_PLUGIN_DIR') )
{
    
    define('Y2YWSM_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
}

if( !defined('Y2YWSM_PLUGIN_URL'))
{
    
    define('Y2YWSM_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
}

if( !defined('Y2YWSM_VERSION'))
{
    define('Y2YWSM_VERSION', '1.0');
}

if(!defined('Y2YWSM_ID')){
    define('Y2YWSM_ID', 'You2You');
}

require_once(Y2YWSM_PLUGIN_DIR.'/Y2YWSM_API.php');


/* Runs when plugin is activated */
register_activation_hook(__FILE__, 'y2ywsm_install'); 
function y2ywsm_install(){
    global $wpdb;
    
    $wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}y2y_deliveries`("
            . "`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,"
            . "`wc_order_id` INT NOT NULL,"
            . "`status` INT,"
            . "`delivery_date` DATETIME"
        . ")");
}

class Y2YWSM_CORE{

    private $api_key = '';
    private $api_secret = '';
    
    private $api;
    
    public $available_languages = array();
    public function __construct() {
        
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            //Load the options
            add_action('woocommerce_init', array($this, 'load_options'));
            
            //Register the method in woocommerce
            add_action( 'woocommerce_shipping_init', array($this,'init_shipping') );
            add_filter( 'woocommerce_shipping_methods', array($this, 'add_shipping_method') );
            
            //Register all scripts
            add_action( 'wp_enqueue_scripts', array($this, 'enqueue_all_scripts') );
            add_action( 'admin_enqueue_scripts', array($this, 'enqueue_all_scripts'));
            
            //Register the translation
            add_action('plugins_loaded', array($this,'load_text_domain'));
            
            //Change the appearance of method in the method's list
            add_filter( 'woocommerce_cart_shipping_method_full_label', array($this,'add_image_to_available_methods'), 10, 2 );
            
            //Add delivery date to checkout fields
            add_filter('woocommerce_checkout_fields', array($this, 'add_delivery_date_to_checkout_fields'));
            
            //Validate the delivery before saving
            add_action( 'woocommerce_after_checkout_validation', array($this, 'validate_you2you_fields_before_checkout'));
            
            //Add delivery to database
            add_action( 'woocommerce_checkout_order_processed', array($this, 'insert_delivery_in_db'));
            
            //Add delivery to the you2you using the you2you api
            add_action('woocommerce_order_status_processing', array($this, 'add_delivery_to_y2y'));
            
            //Customize appearence of the delivery_date in the checkout form
            add_filter('woocommerce_form_field_text', array($this, 'customize_delivery_date_field'), 10, 4);
        }
        
    }
    
    public function load_options(){
        $options = get_option('woocommerce_'.Y2YWSM_ID.'_settings');
        
        $this->api_secret = $options['api_secret'];
        $this->api_key = $options['api_key'];
        
        $this->api = new Y2YWSM_API($this->api_key, $this->api_secret);
        
        $this->available_languages = array(
            'de' => __('German', 'y2ywsm'),
            'en' => __('English', 'y2ywsm'),
            'es' => __('Spanish', 'y2ywsm'),
            'fr' => __('French', 'y2ywsm'),
            'ja' => __('Japanese', 'y2ywsm'),
            'nl' => __('Dutch', 'y2ywsm'),
            'ro' => __('Romanian', 'y2ywsm'),
            'ru' => __('Russian', 'y2ywsm'),
            'uk' => __('Ukrainian', 'y2ywsm'),
            'zh-TW' => __('Chinese (T)', 'y2ywsm'),
            'zh-CN' => __('Chinese (S)', 'y2ywsm')
            
        );
        
    }
    
    public function init_shipping(){
        require_once (Y2YWSM_PLUGIN_DIR . '/Y2YWSM_Shipping_Method.php');
    }
    
    public function add_shipping_method( $methods ) {
	$methods[] = 'Y2YWSM_Shipping_Method';
	return $methods;
        
    }
    
    public function enqueue_all_scripts(){
        $lang_code = $this->get_language_code();
        /** Scripts **/
        wp_enqueue_script( 'datetimepicker-js',  Y2YWSM_PLUGIN_URL . '/assets/js/DateTimePicker/DateTimePicker.js', array('jquery'), Y2YWSM_VERSION, true );
        wp_enqueue_script( 'datetimepicker-i18n',  Y2YWSM_PLUGIN_URL . '/assets/js/DateTimePicker/i18n/DateTimePicker-i18n-'.$lang_code.'.js', array('datetimepicker-js'), Y2YWSM_VERSION, true );
        
        wp_enqueue_script( 'y2ywsm-js', Y2YWSM_PLUGIN_URL . '/assets/js/y2ywsm.js', array('jquery', 'datetimepicker-js'), Y2YWSM_VERSION, true );
        wp_localize_script('y2ywsm-js', 'options', array('lang' => $lang_code));
        
        
        /** Styles **/
        wp_enqueue_style( 'datetimepicker-css', Y2YWSM_PLUGIN_URL . '/assets/css/DateTimePicker.css', '', Y2YWSM_VERSION, 'all' );
        wp_enqueue_style('y2ywsm-css', Y2YWSM_PLUGIN_URL . '/assets/css/y2ywsm.css', '', Y2YWSM_VERSION, 'all');
    }
    
    public function load_text_domain(){
        
        load_plugin_textdomain( 'y2ywsm', false, dirname( plugin_basename(__FILE__) ) . '/i18n/' );
        
    }
    
    public function add_image_to_available_methods( $label, $method ) {
        $label = $method->label;

        if ( $method->cost > 0 ) {
            if ( WC()->cart->tax_display_cart == 'excl' ) {
                $label .= ': ' . wc_price( $method->cost );
                if ( $method->get_shipping_tax() > 0 && WC()->cart->prices_include_tax ) {
                    $label .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                }
            } else {
                $label .= ': ' . wc_price( $method->cost + $method->get_shipping_tax() );
                if ( $method->get_shipping_tax() > 0 && ! WC()->cart->prices_include_tax ) {
                    $label .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
                }
            }
        }

        if ( $method->id == Y2YWSM_ID ) {
            $label = '<img src="' . Y2YWSM_PLUGIN_URL.'/assets/img/logo.png' . '" width="30px">&nbsp;&nbsp;'.$label.'<br>'
                . 'Livraison collaborative.';

        } 

        return $label;
    }
    
    public function add_delivery_date_to_checkout_fields($fields){
        $fields['billing']['delivery_date'] = array(
            'type' => 'text',
            'required' => false,
            'label' => '<br><strong>'.__('You2you, collaborative delivery','y2ywsm').'</strong>'
                . '<br>'.__('Choose your delivery date','y2ywsm')
        );
        
        return $fields;
    }
    
    public function validate_you2you_fields_before_checkout($data){
        if(in_array(Y2YWSM_ID, $data['shipping_method'])){
            $delivery_date = $data['delivery_date'];
            if(empty($delivery_date)){
                wc_add_notice( __('We need to know the date for the delivery', 'y2ywsm'), 'error' );
                return;
            }
            
            $delivery_date = date('Y-m-d H:i:s', strtotime($delivery_date));
            $today = date('Y-m-d H:i:s');

            if($today > $delivery_date){
                wc_add_notice( __('The delivery date should be after today\'s date', 'y2ywsm'), 'error' );
                return;
            }
        }
        
    }
    
    public function insert_delivery_in_db($order_id, $data){
        global $wpdb;
        $wpdb->insert($wpdb->prefix.'y2y_deliveries',
                array(
                    'wc_order_id' => $order_id,
                    'delivery_date' => date('Y-m-d H:i:s', strtotime($data['delivery_date'])),
                     'status' => 1
                    )
            );
        wc_add_notice(__("Your delivery will be posted to you2you after the payment is made", 'y2ywsm'), 'notice');
    }
    
    public function add_delivery_to_y2y($order_id){
        global $wpdb;
        $db_row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}y2y_deliveries WHERE wc_order_id = {$order_id} AND status = 1");
        if(db_row === null){
            return;
        }
        
        $order = wc_get_order($order_id);
        
        $this->api->post('deliveries', array(
            'street' => $order->shipping_address_1,
            'city' => $order->shipping_city,
            'country' => $order->shipping_country,
            'postalcode' => $order->shipping_postcode,
            'information' => $order->shipping_address_2,
            'company' => $order->shipping_company,
            'firstname' => $order->shipping_first_name,
            'lastname' => $order->shipping_last_name,
            'shipstart' => $db_row->delivery_date,
            'shipend' => date('Y-m-d H:i:s', strtotime('+2 hours', strtotime($db_row->delivery_date))),
            'compensation' => 8,
            
            
        ));
        $wpdb->update(
                $wpdb->prefix.'y2y_deliveries',
                array( 'status' => 2),
                array( 'wc_order_id' => $order_id)
            );
        
    }
    
    public function customize_delivery_date_field($field, $key = '', $args = '', $value = ''){
        if($key == 'delivery_date'){
            //do something
        }
        return $field;
    }

    
    public function get_language_code(){
        $lang = $this->get_wp_language_code();
        switch($lang){
            case 'fr-FR':
                $lang = 'fr';
                break;
        }
        
        if(!in_array($lang, array_keys($this->available_languages))){
            $lang = 'es';
        }
        
        return $lang;
    }
    
    public function get_wp_language_code(){
        return 'fr-FR';
        /*if(defined(ICL_LANGUAGE_CODE)){
            return ICL_LANGUAGE_CODE;
        }
        
        return get_locale();*/
    }
}
new Y2YWSM_CORE;
