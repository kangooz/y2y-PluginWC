<?php
/**
 * Plugin Name: You2you_plugin
 * Plugin URI: http://partner-it-group.com
 * Description: An e-commerce toolkit that helps you sell anything. Beautifully.
 * Version: 1.0.0
 * Author: Partner-IT-Group
 * Author URI: http://partner-it-group.com
 *
 * Text Domain: y2ywsm
 * Domain Path: /i18n/languages/
 *
 * @author Partner IT Group
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


class Y2YWSM_Admin{

    public function __construct() {
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            add_action( 'woocommerce_shipping_init', array($this,'init_shipping') );
            add_filter( 'woocommerce_shipping_methods', array($this, 'add_shipping_method') );
            //add_action( 'woocommerce_order_status_processing', array($this, 'confirm_delivery'));
            //add_action( 'woocommerce_checkout', array($this, 'checkout'));
            
            //Position the calendar
            add_action( 'woocommerce_after_shipping_rate', array($this, 'add_shipping_date'));
            //add_action( 'woocommerce_before_order_notes', array( $this, 'add_shipping_date' ) );
            
            add_action( 'wp_enqueue_scripts', array($this, 'enqueue_all_scripts') );
            add_action( 'admin_enqueue_scripts', array($this, 'enqueue_all_scripts'));
            
            add_filter( 'woocommerce_cart_shipping_method_full_label', array($this,'add_image_to_available_methods'), 10, 2 );
            add_action( 'woocommerce_order_details_after_order_table', array($this,'display_custom_fields'), 10, 1 );
            
            /** Process the order and comunicate with the you2you api */
            add_action( 'woocommerce_after_checkout_validation', array($this, 'validate_you2you_fields_before_checkout'));
        }
        
    }
    
    
    public function add_shipping_date( $method ) {
        if(!$method->id === Y2YWSM_ID){
            return;
        }
    }
    
    
    public function init_shipping(){
        require_once (Y2YWSM_PLUGIN_DIR . '/Y2YWSM_Shipping_Method.php');
    }
    
    public function add_shipping_method( $methods ) {
	$methods[] = 'Y2YWSM_Shipping_Method';
	return $methods;
        
    }
    
    public function validate_you2you_fields_before_checkout($data){
        if(in_array(Y2YWSM_ID, $data['shipping_method'])){
            wc_add_notice( "sim", 'error' );
        }else{
            wc_add_notice( "nao", 'error' );
        }
        
    }
    
    public function enqueue_all_scripts(){
        wp_enqueue_style( 'datetimepicker-css', Y2YWSM_PLUGIN_URL . '/assets/css/DateTimePicker.css', '', Y2YWSM_VERSION, false );
        //wp_enqueue_script( 'datetimepicker-js', '/wp-content/DateTimePicker/src/i18n/DateTimePicker-i18n-fr.js', '', '4.4.1', false );
        wp_enqueue_script( 'datetimepicker-js',  Y2YWSM_PLUGIN_URL . '/assets/js/DateTimePicker.js', array('jquery'), Y2YWSM_VERSION, true );
        wp_enqueue_script( 'main-scripts', Y2YWSM_PLUGIN_URL . '/assets/js/y2ywsm.js', array('jquery', 'datetimepicker-js'), Y2YWSM_VERSION, true );
        wp_enqueue_style('y2ywsm-css', Y2YWSM_PLUGIN_URL . '/assets/css/y2ywsm.css', '', Y2YWSM_VERSION, 'all');
        //wp_enqueue_script( 'anytime.5.1.2-js',  Y2YWSM_PLUGIN_URL . '/assets/js/anytime.5.1.2.js', array('jquery'), Y2YWSM_VERSION, true );
        //wp_enqueue_style('anytime.5.1.2-css', Y2YWSM_PLUGIN_URL . '/assets/css/anytime.5.1.2.css', '', Y2YWSM_VERSION, 'all');
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

        if ( $method->id == "You2You" ) {
            $label = '<img src="' . plugins_url( '/assets/img/logo.png', __FILE__ ) . '" width="30px">&nbsp;&nbsp;'.$label.'<br>'
                . 'Livraison collaborative.';

        } 

        return $label;
    }
    
    public function display_custom_fields($order){
        echo '<p><strong>'.__('Pickup Location').':</strong> ' . get_post_meta( $order->id, 'Pickup Location', true ). '</p>';
        echo '<p><strong>'.__('Pickup Date').':</strong> ' . get_post_meta( $order->id, 'Pickup Date', true ). '</p>';
    }
}
new Y2ywsm_Admin;
