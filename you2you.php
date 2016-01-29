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

class Y2YWSM_Admin{

    public function __construct() {
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            add_action( 'woocommerce_shipping_init', array($this,'init_shipping') );
            add_filter( 'woocommerce_shipping_methods', array($this, 'add_shipping_method') );
            add_action( 'woocommerce_order_status_processing', array($this, 'confirm_delivery'));
            add_action( 'woocommerce_after_shipping_rate', array($this, 'after_shipping_rate'));
            add_action( 'woocommerce_checkout_update_order_review', array($this, 'update_order_review'));
            
            //Position the calendar
            add_action( 'woocommerce_before_order_notes', array( &$this, 'my_custom_checkout_field' ) );
        }
        
    }
    
    
    function my_custom_checkout_field( $checkout ) {
        global $orddd_lite_weekdays;

        wp_enqueue_script( 'jquery' );
        wp_deregister_script( 'jqueryui');
        wp_enqueue_script( 'jquery-ui-datepicker' );

        $calendar_theme = get_option( 'orddd_lite_calendar_theme' );
        if ( $calendar_theme == '' ) {
            $calendar_theme = 'base';
        }
        wp_dequeue_style( 'jquery-ui' );
        wp_enqueue_style( 'jquery-ui-orddd', "//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/$calendar_theme/jquery-ui.css" , '', '', false );
        wp_enqueue_style( 'datepicker', plugins_url('/assets/css/datepicker.css', __FILE__) , '', '', false);

        wp_enqueue_script(
            'initialize-datepicker.js',
            plugins_url('/assets/js/initialize-datepicker.js', __FILE__),
            '',
            '',
            false
        );

        if ( isset( $_GET[ 'lang' ] ) && $_GET[ 'lang' ] != '' && $_GET[ 'lang' ] != null ) {
            $language_selected = $_GET['lang'];
        } else {
            $language_selected = get_option( 'orddd_lite_language_selected' );
            if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
                if( constant( 'ICL_LANGUAGE_CODE' ) != '' ) {
                    $wpml_current_language = constant( 'ICL_LANGUAGE_CODE' );
                    if ( !empty( $wpml_current_language ) ) {
                        $language_selected = $wpml_current_language;
                    } else {
                        $language_selected = get_option( 'orddd_lite_language_selected' );
                    }
                }
            }
            if ( $language_selected == "" ) $language_selected = "en-GB";
        }

        wp_enqueue_script(
            $language_selected,
            plugins_url( "/assets/js/i18n/jquery.ui.datepicker-$language_selected.js", __FILE__ ),
            '',
            '',
            false );
        $first_day_of_week = '1';
        if( get_option( 'orddd_lite_start_of_week' ) != '' ) {
            $first_day_of_week = get_option( 'orddd_lite_start_of_week' );
        }

        echo '<script language="javascript">
            jQuery( document ).ready( function(){
                jQuery( "#e_deliverydate" ).attr( "readonly", true );
                var formats = ["MM d, yy","MM d, yy"];
                jQuery("#e_deliverydate").val("").datepicker({dateFormat: "' . get_option( 'orddd_lite_delivery_date_format' ) . '", firstDay: parseInt( ' . $first_day_of_week . ' ), minDate:1, beforeShow: avd, beforeShowDay: chd,
                    onClose:function( dateStr, inst ) {
                        if ( dateStr != "" ) {
                            var monthValue = inst.selectedMonth+1;
                            var dayValue = inst.selectedDay;
                            var yearValue = inst.selectedYear;
                            var all = dayValue + "-" + monthValue + "-" + yearValue;
                            jQuery( "#h_deliverydate" ).val( all );
                        }
                    }
                });
            jQuery( "#e_deliverydate_y2y" ).attr( "readonly", true );
                var formats = ["MM d, yy","MM d, yy"];
                jQuery("#e_deliverydate_y2y").val("").datepicker({dateFormat: "' . get_option( 'orddd_lite_delivery_date_format' ) . '", firstDay: parseInt( ' . $first_day_of_week . ' ), minDate:1, beforeShow: avd, beforeShowDay: chd,
                    onClose:function( dateStr, inst ) {
                        if ( dateStr != "" ) {
                            var monthValue = inst.selectedMonth+1;
                            var dayValue = inst.selectedDay;
                            var yearValue = inst.selectedYear;
                            var all = dayValue + "-" + monthValue + "-" + yearValue;
                            jQuery( "#h_deliverydate_y2y" ).val( all );
                        }
                    }
                 })';
        if ( get_option( 'orddd_lite_delivery_date_field_note' ) != '' ) {
            echo 'jQuery("#e_deliverydate").parent().append("<br><small style=font-size:10px;>' . addslashes( __( get_option( 'orddd_lite_delivery_date_field_note' ), 'order-delivery-date' ) ) . '</small>" );';
        }
        echo '} );
        </script>';

        if ( get_option( 'orddd_lite_date_field_mandatory' ) == 'checked' ) {
            $validate_wpefield = true;
        } else {
            $validate_wpefield = '';
        }
        
        /** YOU2You */
        echo '<div>Quand souhaites-tu Ãªtre livrÃ© ? &nbsp; <img src="' . plugins_url( '/assets/img/logo.png', __FILE__ ) . '" width="30px"></div>';
        woocommerce_form_field( 'e_deliverydate_y2y', array(
            'type'          => 'text',
            'label'         => '',
            'placeholder'   => 'Chose a date',
        ),
        $checkout->get_value( 'e_deliverydate_y2y' ) );
        
        woocommerce_form_field( 'h_deliverydate_y2y', array(
            'type' => 'text',
            'custom_attributes' => array( 'style'=>'display: none !important;' ) 
        ),
        $checkout->get_value( 'h_deliverydate_y2y' ) );
        
        woocommerce_form_field( 'hours_deliverydate', array(
            'type' => 'text',
            'placeholder' => 'hours',
            'custom_attributes' => array(
                                    'style'=>"width:4em"
                ),
        ),
        $checkout->get_value( 'hours_deliverydate' ) );
        woocommerce_form_field( 'minutes_deliverydate', array(
            'type' => 'text',
            'placeholder' => 'minutes',
            'custom_attributes' => array(
                                    'style'=>"width:4em"
                ),
        ),
        $checkout->get_value( 'minutes_deliverydate' ) );
        
        /** END **/
        
        
        
        woocommerce_form_field( 'h_deliverydate', array(
            'type' => 'text',
            'custom_attributes' => array( 'style'=>'display: none !important;' ) 
        ),
        $checkout->get_value( 'h_deliverydate' ) );

        $alldays_orddd_lite = array();
//        foreach ( $orddd_lite_weekdays as $n => $day_name ) {
//            $alldays_orddd_lite[ $n ] = get_option( $n );
//        }
        $alldayskeys_orddd_lite = array_keys( $alldays_orddd_lite );
        $checked = "No";
        foreach( $alldayskeys_orddd_lite as $key ) {
            if( $alldays_orddd_lite[ $key ] == 'checked' ) {
               $checked = "Yes";
            }
        }

        if( $checked == 'Yes' ) {
            foreach( $alldayskeys_orddd_lite as $key ) {
                print( '<input type="hidden" id="' . $key . '" value="' . $alldays_orddd_lite[ $key ] . '">' );
            }
        } else if( $checked == 'No') {
            foreach( $alldayskeys_orddd_lite as $key )  {
                print( '<input type="hidden" id="' . $key . '" value="checked">' );
            }
        }

        $min_date = '';
        $current_time = current_time( 'timestamp' );

        $delivery_time_seconds = get_option( 'orddd_lite_minimumOrderDays' ) *60 *60;
        $cut_off_timestamp = $current_time + $delivery_time_seconds;
        $cut_off_date = date( "d-m-Y", $cut_off_timestamp );
        $min_date = date( "j-n-Y", strtotime( $cut_off_date ) );

        print( '<input type="hidden" name="orddd_lite_minimumOrderDays" id="orddd_lite_minimumOrderDays" value="' . $min_date . '">' );
        print( '<input type="hidden" name="orddd_lite_number_of_dates" id="orddd_lite_number_of_dates" value="' . get_option( 'orddd_lite_number_of_dates' ) . '">' );
        print( '<input type="hidden" name="orddd_lite_date_field_mandatory" id="orddd_lite_date_field_mandatory" value="' . get_option( 'orddd_lite_date_field_mandatory' ) . '">' );
        print( '<input type="hidden" name="orddd_lite_number_of_months" id="orddd_lite_number_of_months" value="' . get_option( 'orddd_lite_number_of_months' ) . '">' );

        $lockout_days_str = '';
        if ( get_option( 'orddd_lite_lockout_date_after_orders' ) > 0 ) {
            $lockout_days_arr = array();
            $lockout_days = get_option( 'orddd_lite_lockout_days' );
            if ( $lockout_days != '' && $lockout_days != '{}' && $lockout_days != '[]' ) {
                $lockout_days_arr = json_decode( get_option( 'orddd_lite_lockout_days' ) );
            }
            foreach ( $lockout_days_arr as $k => $v ) {
                if ( $v->o >= get_option( 'orddd_lite_lockout_date_after_orders' ) ) {
                    $lockout_days_str .= '"' . $v->d . '",';
                }
            }
            $lockout_days_str = substr( $lockout_days_str, 0, strlen( $lockout_days_str ) -1 );
        }
        print( '<input type="hidden" name="orddd_lite_lockout_days" id="orddd_lite_lockout_days" value=\'' . $lockout_days_str . '\'>' );
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
    
    public function after_shipping_rate($method ){
        //se este method é o da you2you, cria as caixas de texto para guardar a data de entrega
    }
    
    public function update_order_review($form_data){
        //O utilizador confirmou a compra por isso Ã© preciso ver se o shipping method Ã© o da you2you
        //e se for fazer request Ã  api da y2y para inserir delivery "provisoria"
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