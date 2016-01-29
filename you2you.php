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

class Y2YWSM_Admin{

    public function __construct() {
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            add_action( 'woocommerce_shipping_init', array($this,'init_shipping') );
            add_filter( 'woocommerce_shipping_methods', array($this, 'add_shipping_method') );
            add_action( 'woocommerce_order_status_processing', array($this, 'confirm_delivery'));
            add_action( 'woocommerce_checkout_update_order_review', array($this, 'update_order_review'));
            
            //Position the calendar
            add_action( 'woocommerce_after_shipping_rate', array($this, 'add_shipping_date'));
            //add_action( 'woocommerce_before_order_notes', array( $this, 'add_shipping_date' ) );
            
            add_action( 'wp_enqueue_scripts', array($this, 'enqueue_all_scripts') );
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
    
    public function confirm_delivery(){
        //confirmar a delivery através da api da you2you
    }
    
    public function update_order_review($form_data){
        //O utilizador confirmou a compra por isso é preciso ver se o shipping method é o da you2you
        //e se for fazer request à api da y2y para inserir delivery "provisoria"
    }
    
    public function enqueue_all_scripts(){
        
        
        
        
    }
}
new Y2ywsm_Admin;



function y2y_woocommerce_cart_shipping_method_full_label( $label, $method ) {
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

add_filter( 'woocommerce_cart_shipping_method_full_label', 'y2y_woocommerce_cart_shipping_method_full_label', 10, 2 );


add_action( 'woocommerce_order_details_after_order_table', 'y2y_custom_field_display_cust_order_meta', 10, 1 );

function y2y_custom_field_display_cust_order_meta($order){
    echo '<p><strong>'.__('Pickup Location').':</strong> ' . get_post_meta( $order->id, 'Pickup Location', true ). '</p>';
    echo '<p><strong>'.__('Pickup Date').':</strong> ' . get_post_meta( $order->id, 'Pickup Date', true ). '</p>';
}