<?php
/**
 * Plugin Name: You2You WooCommerce Shipping Method
 * Plugin URI: http://www.you2you.fr
 * Description: This plugin allows user's to choose You2You as the shipping method in WooCommerce
 * Version: 0.0.1
 * Author: You2You
 * Author URI: http://www.you2you.fr
 *
 * Text Domain: y2ywsm
 * Domain Path: /i18n/
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

    public static $validPostCodes = array(
        '75',
        '92',
        '93',
        '94'
    );
    
    public static $cost = 5;
            
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
            add_action( 'woocommerce_checkout_order_processed', array($this, 'insert_delivery_in_db'), 10, 2);
            
            //Add delivery to the you2you using the you2you api
            //add_action('woocommerce_order_status_processing', array($this, 'add_delivery_to_y2y'));
            
            //Trigger order status changed
            add_action('woocommerce_order_status_changed', array($this, 'order_status_changed'),10, 3);
            
            //Customize appearence of the delivery_date in the checkout form
            add_filter('woocommerce_form_field_text', array($this, 'customize_delivery_date_field'), 10, 4);
        }
        
    }
    
    public function load_options(){
        $options = get_option('woocommerce_'.Y2YWSM_ID.'_settings', array());
        
        foreach($options as $key => $value){
            $this->$key = $value;
        }
        /*$this->api_secret = $options['api_secret'];
        $this->api_key = $options['api_key'];
        $this->openning_hours_beginning = $options['openning_hours_beginning'];
        $this->openning_hours_endding= $options['openning_hours_endding'];
        $this->lunch_time_beginning = $options['lunch_time_beginning'];
        $this->lunch_time_endding = $options['lunch_time_endding'];
        $this->closed_day = $options['closed_day'];
        $this->timeout = isset($options['timeout']) ? $options['timeout'] : 0;*/
        
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
    
    public static function isValidPostCode($postcode){
        foreach (self::$validPostCodes as $frpostcode) {
            if (substr($postcode, 0, 2) == $frpostcode) {
                return true;
            }
        }
        
        return false;
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
        wp_enqueue_script( 'calendar-js',  Y2YWSM_PLUGIN_URL . '/assets/js/jquery-calendar/jquery-ui.js', array('jquery'), Y2YWSM_VERSION, true );
        wp_enqueue_script( 'moment-js',  Y2YWSM_PLUGIN_URL . '/assets/js/moment-with-locales/moment.js', array('jquery'), Y2YWSM_VERSION, true );
        
        wp_enqueue_script( 'y2ywsm-js', Y2YWSM_PLUGIN_URL . '/assets/js/y2ywsm.js', array('jquery', 'calendar-js'), Y2YWSM_VERSION, true );
        
        $minDate = $this->getMinDate();
        $options = get_option('woocommerce_'.Y2YWSM_ID.'_settings', array());
        foreach($options as $key => $value){
            $this->$key = $value;
        }
        wp_localize_script('y2ywsm-js', 'options', array(
            'lang' => $lang_code,
            'hours' => $this,
            'dateTimePicker' => array(
                'defaultValue' => $this->getMinDateForJS($minDate), 
                'minDateTime' => $minDate
                ),
            'calendar_img' => Y2YWSM_PLUGIN_URL.'/assets/js/jquery-calendar/images/calendar.gif',
            )
        );
        
        
        /** Styles **/
        wp_enqueue_style( 'datetimepicker-css', Y2YWSM_PLUGIN_URL . '/assets/css/DateTimePicker.css', '', Y2YWSM_VERSION, 'all' );
        wp_enqueue_style( 'calendar-css', Y2YWSM_PLUGIN_URL . '/assets/js/jquery-calendar/jquery-ui.css', '', Y2YWSM_VERSION, 'all' );
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
            'id'=> 'delivery_date',
            'custom_attributes' => array(
                'autocomplete' => 'off',
                'class' => 'hidden',
            ),
            /*'label' => '<br><strong>'.__('You2you, collaborative delivery','y2ywsm').'</strong>'
                . '<br>'.__('Choose your delivery date','y2ywsm'),
            'description' => __(' We will try to send the delivery within 2 hours from this time', 'y2ywsm')*/
        );
        
        $fields['billing']['hidden_date'] = array(
            'type' => 'text',
            'custom_attributes' => array(
                'readonly' => 'true',
                'class' => 'hidden',
            ),
            'id' => 'hidden_date',
            'value' => 'Choose a date',
            'label' => '<br><strong>'.__('Date','y2ywsm').'</strong>'
                . '<br>'.__('Choose your delivery date','y2ywsm')
        );
        
        $fields['billing']['hidden_time'] = array(
            'type' => 'text',
            'custom_attributes' => array(
                'readonly' => 'true',
                'class' => 'hidden',
            ),
            'id' => 'hidden_time',
            'value' => 'Choose a time',
            'label' => '<br><strong>'.__('Time','y2ywsm').'</strong>'
                . '<br>'.__('Choose your delivery time','y2ywsm')
        );
        
        
        
        return $fields;
    }
    
    public function validate_you2you_fields_before_checkout($data){
        if(in_array(Y2YWSM_ID, $data['shipping_method'])){
            //Validate the postal code
            $postcode = (!empty($data['shipping_postcode']) ? $data['shipping_postcode'] : $data['billing_postcode']);
            
            if(!self::isValidPostCode($postcode)){
                wc_add_notice(sprintf(__('You2You is only available for postcodes beggining with %s', 'y2ywsm'), implode(', ',self::$validPostCodes)), 'error');
                return;
            }
            //$data['delivery_date'] = $data['hidden_date']." ".$data['hidden_time'];
            
            $delivery_date = $data['delivery_date'];
            if(empty($delivery_date)){
                wc_add_notice( __('We need to know the date for the delivery', 'y2ywsm'), 'error' );
                return;
            }
            
            $time = date("H:i:s",strtotime($delivery_date));
            if($time=='00:00:00')
            {
                wc_add_notice( __('Please select a delivery time', 'y2ywsm'), 'error' );
                return;
            }
            
            $delivery_date = date('Y-m-d H:i:s', strtotime($delivery_date));
            $today = date('Y-m-d H:i:s');

            if($today > $delivery_date){
                wc_add_notice( __('The delivery date should be after today\'s date', 'y2ywsm'), 'error' );
                return;
            }
            
            $timestamp = strtotime($delivery_date);
            $year = date('Y', $timestamp);
            $month = date('m', $timestamp);
            $day = date('d', $timestamp);
            $hour = date('H', $timestamp);
            $minute = date('i', $timestamp);
            $dayofweek = date('w', $timestamp);
            
            $timeout = ($this->timeout > 2) ? $this->timeout*60*60 : 2*60*60;
            $order_hour = date('H:i',$timestamp);
            $delivery_hour = strtotime(date('H:i',$timestamp));
            $delivery_day_hour = strtotime(date('Y/m/d H:i',$timestamp+$timeout));
            
            //Closed day
            if($this->closed_day[$dayofweek] == 'yes'){
                wc_add_notice( __('The shop is closed in this day', 'y2ywsm'), 'error' );
                return;
            }
            
            //Before opening hours
            if(strtotime($order_hour) < strtotime($this->openning_hours_beginning[$dayofweek])){
                wc_add_notice( __('The shop is closed at that hour in the morning', 'y2ywsm'), 'error' );
                return;
            }
            
            //After opening hours
            if( $delivery_hour > strtotime($this->openning_hours_endding[$dayofweek])){
                wc_add_notice( __('The shop is closed at that hour in the afternoon', 'y2ywsm'), 'error' );
                return;
            }
            
            /*
            //Lunch Time
            $lunch_beg = strtotime($this->lunch_time_beginning[$dayofweek]);
            $lunch_end = strtotime($this->lunch_time_ending[$dayofweek]);
            if($delivery_hour > $lunch_beg && $delivery_hour < $lunch_end){
                wc_add_notice( __('The shop is closed for lunch', 'y2ywsm'), 'error' );
                return;
            }
            */
            
            
        }
        
    }
    
    public function insert_delivery_in_db($order_id, $data){
        global $wpdb;
        //$data['delivery_date'] = $data['hidden_date']." ".$data['hidden_time'];
        
        $wpdb->insert($wpdb->prefix.'y2y_deliveries',
                array(
                    'wc_order_id' => $order_id,
                    'delivery_date' => date('Y-m-d H:i:s', strtotime($data['delivery_date'])),
                     'status' => 1
                    )
            );
        wc_add_notice(__("Your delivery will be posted to you2you after the payment is made", 'y2ywsm'), 'notice');
    }
    
    public function order_status_changed($order_id, $old_status, $new_status){
        global $wpdb;
        $db_row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}y2y_deliveries WHERE wc_order_id = {$order_id}");
        if($db_row === null){
            return;
        }
        
        if($db_row->status == 1 && ($new_status == 'processing' 
                || $new_status == 'completed' 
                || $new_status == 'on-hold')){
            $this->add_delivery_to_y2y($order_id, $db_row);
        }else if($db_row->status == 2 && ($new_status == 'pending' 
                || $new_status == 'failed' 
                || $new_status == 'cancelled')){
            $this->remove_delivery_from_y2y($order_id, $db_row);
        }
    }
    
    protected function add_delivery_to_y2y($order_id, $db_row = null){
        global $wpdb;
        $order = wc_get_order($order_id);
        
        $order_details = '';
        $items = $order->get_items();
        foreach($items as $item){
            $product_id = $item['product_id'];
            $order_details .= sprintf(
                    __(
                      '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Name: %s<br>'
                    . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Quantity: %s<br>',
                    'y2ywsm'),
                    $item['name'],
                    $item['qty']);
            
            $product = wc_get_product($product_id);
            if($product != false){
                $order_details .= sprintf(
                        __(
                            '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Width: %s<br>'
                          . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Length: %s<br>'
                          . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Height: %s<br>'
                          . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Weight: %s<br>', 
                        'y2ywsm'),
                        (empty($product->width) ? __("undefined", 'y2ywsm') : $product->width),
                        (empty($product->length) ? __("undefined", 'y2ywsm') : $product->length),
                        (empty($product->height) ? __("undefined", 'y2ywsm') : $product->height),
                        (empty($product->weight) ? __("undefined", 'y2ywsm') : $product->weight)
                        );
            }
        }
        
        /*$this->api->post('deliveries', array(
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
            
            
        ));*/
        
        $to      = 'support@partner-it-group.com';
        $subject = __('Order added from woocommerce plugin', 'y2ywsm');
        $message = sprintf(
                __('A order has been placed from woocommerce plugin.<br>'
                        . 'Store: %s, %s %s %s<br>'
                        . 'Store information: %s<br>'
                        . 'Order details: %s<br>'
                        . 'Destination: %s, %s %s %s <br>'
                        . '%s<br>'
                        . 'Destination information: %s<br>'
                        . 'Company: %s<br>'
                        . 'Firstname: %s<br>'
                        . 'Lastname: %s<br>'
                        . 'Shipstart: %s<br>'
                        . 'Shipend: %s<br>'
                        . 'Compensation: %s&euro;', 
                'y2ywsm'),
                $this->store_address,
                $this->store_postalcode,
                $this->store_city,
                $this->store_country,
                $this->store_information,
                $order_details,
                $order->shipping_address_1,
                $order->shipping_postcode,
                $order->shipping_city,   
                $order->shipping_country,
                $order->shipping_address_2,
                $order->customer_message,
                $order->shipping_company,
                $order->shipping_first_name,
                $order->shipping_last_name,
                date('d/m/Y H:i:s',strtotime($db_row->delivery_date)),
                date('d/m/Y H:i:s', strtotime('+2 hours', strtotime($db_row->delivery_date))),
                self::$cost
        );
        
        
        $headers = array('Content-Type: text/html; charset=UTF-8', 'Bcc: support@partner-it-group.com');

        wp_mail($to, $subject, $message, $headers);
        $wpdb->update(
                $wpdb->prefix.'y2y_deliveries',
                array( 'status' => 2),
                array( 'wc_order_id' => $order_id)
            );
        
    }
    
    protected function remove_delivery_from_y2y($order_id, $db_row){
        global $wpdb;
        $order = wc_get_order($order_id);
        
        $to      = 'support@partner-it-group.com';
        $subject = __('Order canceled from woocommerce plugin', 'y2ywsm');
        $message = sprintf(
                __('Order canceled from woocommerce plugin.<br>'
                        . 'Store address: %s, %s %s %s<br>'
                        . 'Shipping address: %s, %s %s %s<br>'
                        . 'Shipstart: %s',
                'y2ywsm'),
                $this->store_address,
                $this->store_postalcode,
                $this->store_city,
                $this->store_country,
                $order->shipping_postcode,
                $order->shipping_city,   
                $order->shipping_country,
                $order->shipping_address_2,
                date('d/m/Y H:i:s',strtotime($db_row->delivery_date))
                
        );
        
        
        $headers = array('Content-Type: text/html; charset=UTF-8', 'Bcc: support@partner-it-group.com');

        wp_mail($to, $subject, $message, $headers);
        $wpdb->update(
                $wpdb->prefix.'y2y_deliveries',
                array( 'status' => 1),
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
    
    private function getMinDateForJS($date = ''){
        if($date == ''){
            return date('D M d Y H:i:s O', strtotime($this->getMinDate()));
        }else{
            return date('D M d Y H:i:s O', strtotime($date));
        }
        
        //return date('d-m-Y H:i', strtotime($this->getMinDate()));
    }
    
    private function getMinDate(){
        $todayWithTimeout = date('d-m-Y H:i', strtotime('+'.$this->timeout.' hours'));
        return $todayWithTimeout;
        /*$weekDay = date('w');
        
        if($this->closed_day[$weekDay] == 'no'){
            
        }
        $closingAt = isset($this->openning_hours_endding[$weekDay]);
        if($todayWithTimeout < $closedAt){
            $nextDay = $this->getNextAvailableDayOfWeek();
        }*/
    }
    
    private function getNextAvailableDayOfWeek($weekDay = ''){
        if($weekDay == ''){
            $weekDay = date('w');
        }
        
        $availableWeekDay = ($weekDay + 1 < 7) ? $weekDay + 1 : 0;
        
        if( isset($this->closed_day[$availableWeekDay]) && $this->closed_day[$availableWeekDay] == 'yes'){
            return $this->getNextAvailableDayOfWeek($availableWeekDay);
        }else{
            return $weekDay;
        }
        
    }
}
new Y2YWSM_CORE;
