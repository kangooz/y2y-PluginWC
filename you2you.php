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
            //add_filter('woocommerce_form_field_text', array($this, 'customize_delivery_date_field'), 10, 4);
            
            //Show the date in the backoffice when viewing the order details
            add_action('woocommerce_admin_order_data_after_shipping_address', array($this, 'show_delivery_date_in_backoffice'), 10, 1);
            
            //Show the date in the email sent to the customer
            add_filter('woocommerce_email_customer_details_fields', array($this, 'show_delivery_date_in_email'), 15, 3);
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
        wp_enqueue_script( 'calendar-i18n',  Y2YWSM_PLUGIN_URL . '/assets/js/jquery-calendar/i18n/datepicker-'.$lang_code.'.js', array('calendar-js'), Y2YWSM_VERSION, true );
        wp_enqueue_script( 'moment-js',  Y2YWSM_PLUGIN_URL . '/assets/js/moment-with-locales/moment.js', array('jquery'), Y2YWSM_VERSION, true );
        wp_enqueue_script( 'verticalradio-js',  Y2YWSM_PLUGIN_URL . '/assets/js/jquery.verticalradio/jquery.verticalradio.js', array('jquery'), Y2YWSM_VERSION, true );
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
            'messages' => array(
                'choose_delivery_date' => __('Choose delivery date','y2ywsm'),
                'choose' => __('Choose','y2ywsm'),
                'please_be_available_at' => __('Please be available at','y2ywsm'),
                'until' => __('until','y2ywsm'),
                'you_chose' => __('You chose','y2ywsm'),
                'final' => __('to receive your package from the shipper. He will ask you a code that you will receive by SMS in a few minutes','y2ywsm'),
                'no_deliveries' => __('There are more deliveries that day. Please choose another day.','y2ywsm')
                ),
            'week' => array(
               'monday' => __('Monday','y2ywsm'),
               'tuesday' => __('Tuesday','y2ywsm'),
               'wednesday' => __('Wednesday','y2ywsm'),
               'thursday' => __('Thursday','y2ywsm'),
               'friday' => __('Friday','y2ywsm'),
               'saturday' => __('Saturday','y2ywsm'),
               'sunday' => __('Sunday','y2ywsm'),
            ),
            'months' => array(
                'january' => __('January','y2ywsm'),
                'february' => __('February','y2ywsm'),
                'march' => __('March','y2ywsm'),
                'april' => __('April','y2ywsm'),
                'may' => __('May','y2ywsm'),
                'june' => __('June','y2ywsm'),
                'july' => __('July','y2ywsm'),
                'august' => __('August','y2ywsm'),
                'september' => __('September','y2ywsm'),
                'october ' => __('October','y2ywsm'),
                'november'  => __('November','y2ywsm'),
                'december ' => __('December','y2ywsm'),
            ),
            'now' => current_time( 'Y-m-d H:i:s', false ),
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
            
            $label = '<span class="amount">'.wc_price($method->cost).'</span>'
                    . '<br><div style="text-align:center">'
                    . '<img width="45px" src="' . Y2YWSM_PLUGIN_URL.'/assets/img/logo.png">'
                    . '<br>'.__('Collaborative delivery', 'y2ywsm').'<br></div><div style="font-size:12px;line-height:16px;text-align:justify">'
                    . __('To ensure a service that is both practical and environmentally friendly, your order will be delivered by our partner You2You. '
                    . 'For more information,', 'y2ywsm') . ' '
                    .__('<a href="http://www.you2you.fr/commercant/" target="blank">click here.</a>.', 'y2ywsm')
                    . '</div><br>';

        } 
        return $label;
    }
    
    public function add_delivery_date_to_checkout_fields($fields){
        $fields['billing']['y2y_delivery_date'] = array(
            'type' => 'text',
            'required' => false,
            'id'=> 'y2y_delivery_date',
            'custom_attributes' => array(
                'autocomplete' => 'off',
                'class' => 'hidden',
            ),
            /*'label' => '<br><strong>'.__('You2you, collaborative delivery','y2ywsm').'</strong>'
                . '<br>'.__('Choose your delivery date','y2ywsm'),
            'description' => __(' We will try to send the delivery within 2 hours from this time', 'y2ywsm')*/
        );
        
        $fields['billing']['y2y_hidden_date'] = array(
            'type' => 'text',
            'custom_attributes' => array(
                'readonly' => 'true',
                'class' => 'hidden',
            ),
            'id' => 'y2y_hidden_date',
            'value' => 'Choose a date',
            'label' => '<br><strong>'.__('Date','y2ywsm').'</strong>'
                . '<br>'.__('Choose your delivery date','y2ywsm')
        );
        
        $fields['billing']['y2y_hidden_time'] = array(
            'type' => 'text',
            'custom_attributes' => array(
                'readonly' => 'true',
                'class' => 'hidden',
            ),
            'id' => 'y2y_hidden_time',
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
            //$data['y2y_delivery_date'] = $data['y2y_hidden_date']." ".$data['y2y_hidden_time'];
            
            $y2y_delivery_date = $data['y2y_delivery_date'];
            if(empty($y2y_delivery_date)){
                wc_add_notice( __('We need to know the date for the delivery', 'y2ywsm'), 'error' );
                return;
            }
            
            if (!preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $y2y_delivery_date)){
                wc_add_notice( __('Invalid date', 'y2ywsm'), 'error' );
                return;
            }
            
            
            $y2y_delivery_date = date('Y-m-d H:i:s', strtotime($y2y_delivery_date));
            $today = date('Y-m-d H:i:s');
            $dayoftheweek = date('w', strtotime($y2y_delivery_date));
            $timeout = 1;
            if(date('Y-m-d') == date('Y-m-d',strtotime($y2y_delivery_date)))
            {
                $timeout = ($this->timeout >= 1) ? $this->timeout*60*60 : 1*60*60;
            }
            
            if($today > date('Y-m-d H:i:s',strtotime($y2y_delivery_date)-$timeout)){
                wc_add_notice( __('The delivery date should be after today\'s date', 'y2ywsm'), 'error' );
                return;
            }
            
            if(date('H:i:s',strtotime($this->openning_hours_beginning[$dayoftheweek])) > date('H:i:s',strtotime($y2y_delivery_date)))
            {
                wc_add_notice( __('Choose an hour after openning time', 'y2ywsm'), 'error' );
                return;
            }
            
            if(date('H:i:s',strtotime($this->openning_hours_endding[$dayoftheweek])+ 60*60) < date('H:i:s',strtotime($y2y_delivery_date)))
            {
                wc_add_notice( __('Choose an hour before closing time', 'y2ywsm'), 'error' );
                return;
            }
            
            $timestamp = strtotime($y2y_delivery_date);
            $year = date('Y', $timestamp);
            $month = date('m', $timestamp);
            $day = date('d', $timestamp);
            $hour = date('H', $timestamp);
            $minute = date('i', $timestamp);
            $dayofweek = date('w', $timestamp);
            
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
        if(in_array(Y2YWSM_ID, $data['shipping_method'])){
            $wpdb->insert($wpdb->prefix.'y2y_deliveries',
                    array(
                        'wc_order_id' => $order_id,
                        'delivery_date' => date('Y-m-d H:i:s', strtotime($data['y2y_delivery_date'])),
                         'status' => 1
                        )
                );
            wc_add_notice(__("Your delivery will be posted to you2you after the payment is made", 'y2ywsm'), 'notice');
        }
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
        
        $to      = 'contact@you2you.fr';
        $subject = __('Order added from woocommerce plugin', 'y2ywsm');
        $message = sprintf(
                __('A order has been placed from woocommerce plugin.<br>'
                        . 'Order ID: %s<br>'
                        . 'Site URL: %s<br>'
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
                $order_id,
                site_url(),
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
        if($this->email_notification=='yes' && !empty($this->email))
        {
            $headers[] = 'cc: '.$this->email;
        }
        

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
        
        $to      = 'contact@you2you.fr';
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
        if($this->email_notification=='yes' && !empty($this->email))
        {
            $headers[] = 'cc: '.$this->email;
        }
        
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
            $lang = 'fr';
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
    
    public function show_delivery_date_in_backoffice($order){
        global $wpdb;
        $db_row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}y2y_deliveries WHERE wc_order_id = {$order->id}");
        if($db_row === null){
            return;
        }
        ?>
        <div class="y2y-shipping-details">
            <?php echo sprintf("%s: %s",
                    __("Delivery date", 'y2ywsm'),
                    sprintf(__("%s between %s and %s", "y2ywssm"),
                        $this->format_the_date($db_row->delivery_date),
                        $this->format_the_time($db_row->delivery_date),
                        $this->format_the_time($db_row->delivery_date . "+ 1 hour")
                    )
                    
                );
            ?>
        </div>
        <?php
    }
    
    public function show_delivery_date_in_email($fields, $sent_to_admin, $order) {
        global $wpdb;
        $db_row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}y2y_deliveries WHERE wc_order_id = {$order->id}");
        if ($db_row !== null) {
            $fields['y2y_delivery_date'] = array(
                'label' => __('Delivery date', 'y2ywsm'),
                'value' => sprintf(__("%s between %s and %s", "y2ywssm"), 
                        wptexturize($this->format_the_date($db_row->delivery_date)), 
                        wptexturize($this->format_the_time($db_row->delivery_date)),
                        wptexturize($this->format_the_time($db_row->delivery_date . "+ 1 hour"))
                )
            );
        }
        return $fields;
    }

    private function format_full_date($sql_date){
        return $this->format_the_date($sql_date).' '.$this->format_the_time($sql_date);
    }
    
    private function format_the_date($sql_date){
        return date(wc_date_format(), strtotime($sql_date));
    }
    
    private function format_the_time($sql_date){
        return date(wc_time_format(), strtotime($sql_date));
    }

}
new Y2YWSM_CORE;
