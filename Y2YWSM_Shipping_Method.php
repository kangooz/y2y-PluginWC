<?php
if (!class_exists('Y2YWSM_Shipping_Method')) {

    class Y2YWSM_Shipping_Method extends WC_Shipping_Method {

        /**
         *
         * @var array The fields we use to store the opening and closing hours 
         */
        public $extra_field_names = array(
            /*'openning_hours_beginning_h',
            'openning_hours_beginning_m',
            'openning_hours_endding_h',
            'openning_hours_endding_m',
            'lunch_time_beginning_h',
            'lunch_time_beginning_m',
            'lunch_time_endding_h',
            'lunch_time_endding_m',*/
            'openning_hours_beginning',
            'openning_hours_endding',
            'lunch_time_beginning',
            'lunch_time_endding',
            'closed_day',
        );
        
        /**
         * Constructor for your shipping class
         *
         * @access public
         * @return void
         */
        public function __construct() {
            $this->id = Y2YWSM_ID;
            $this->title = __('You2You shipping method', "y2ywsm");
            $this->method_description = __('Description of your shipping method', "y2ywsm"); // 
            $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : $this->enabled;
            $this->init();
            
        }

        /**
         * Init your settings
         *
         * @access public
         * @return void
         */
        public function init() {
            // Load the settings API
            $this->init_form_fields(); 
            $this->init_settings();
            
            // Define user set variablesin if you have any defined
            $this->enabled = $this->get_option('enabled');
            $this->api_key = $this->get_option('api_key');
            $this->api_secret = $this->get_option('api_secret');

            $this->timeout = $this->get_option('timeout');
            
            foreach($this->extra_field_names as $field_name){
                $this->{$field_name} = $this->get_option($field_name, array());
            }
            
            wp_enqueue_style( 'datetimepicker-css', Y2YWSM_PLUGIN_URL . '/assets/css/DateTimePicker.css', '', Y2YWSM_VERSION, false );
            wp_enqueue_script( 'datetimepicker-js',  Y2YWSM_PLUGIN_URL . '/assets/js/DateTimePicker.js', array('jquery'), Y2YWSM_VERSION, true );
            
            //Test connection
            $api = new Y2YWSM_API($this->api_key, $this->api_secret);
            if(!$api->test_connection()){
                add_action('admin_notices', function(){
                    echo '<div class="update-nag"><p>'.__("The ".$this->title." is disabled because the key and secret are wrong", "y2ywsm").'</p></div>';
                });
            }

            //Add hook to save the options
            add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
            
            //Filter for the custom fields
            add_filter('woocommerce_settings_api_sanitized_fields_'.$this->id, array($this, 'filter_update_fields'));
            
            
        }
        
        /**
         * Add the extra fields to save in the database and test if the connection with api is working
         * 
         * @param type $fields The default fields
         * @return array The fields to save in the database
         */
        public function filter_update_fields($fields){

            //for($i = 0; $i < 7; $i++){
            for($i = 0; $i < 5; $i++){
                /*

            //Add the extra fields
            for($i = 0; $i < 7; $i++){

                $this->{openning_hours_beginning_h_.$i} = $this->get_option('openning_hours_beginning_h_'.$i);
                $this->{openning_hours_beginning_m_.$i} = $this->get_option('openning_hours_beginning_m_'.$i);
                
                $this->{openning_hours_endding_h_.$i} = $this->get_option('openning_hours_endding_h_'.$i);
                $this->{openning_hours_endding_m_.$i} = $this->get_option('openning_hours_endding_m_'.$i);
                
                $this->{lunch_time_beginning_h_.$i} = $this->get_option('lunch_time_beginning_h_'.$i);
                $this->{lunch_time_beginning_m_.$i} = $this->get_option('lunch_time_beginning_m_'.$i);
                
                $this->{lunch_time_endding_h_.$i} = $this->get_option('lunch_time_endding_h_'.$i);
                $this->{lunch_time_endding_m_.$i} = $this->get_option('lunch_time_endding_m_'.$i);
                */
                $this->{closed_day_.$i} = $this->get_option('closed_day_'.$i);
                
                $this->{openning_hours_beginning_.$i} = $this->get_option('openning_hours_beginning_'.$i);
                $this->{openning_hours_endding_.$i} = $this->get_option('openning_hours_endding_'.$i);
                $this->{lunch_time_beginning_.$i} = $this->get_option('lunch_time_beginning_'.$i);
                $this->{lunch_time_endding_.$i} = $this->get_option('lunch_time_endding_'.$i);
                
                foreach($this->extra_field_names as $field){
                    
                    $input_name = 'woocommerce_'.$this->id.'_'.$field;
                    if(!empty($_POST[$input_name])){
                        $fields[$field] =  $_POST[$input_name];
                    }
                }
                
                
            }
            
            //Check the api
            $api = new Y2YWSM_API($fields['api_key'], $fields['api_secret']);
            if($api->test_connection() === false){
                $fields['enabled'] = 'no';
            }
            
            return $fields;
        }

        /**
         * calculate_shipping function.
         *
         * @access public
         * @param mixed $package
         * @return void
         */
        public function calculate_shipping($package) {
            $rate = array(
                'id' => $this->id,
                'label' => 'You2You',//$this->title,
                'cost' => '8',
            );

            // Register the rate
            $this->add_rate($rate);
        }

        /**
         * Initialise Gateway Settings Form Fields
         */
        public function init_form_fields() {

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'y2ywsm'),
                    'type' => 'checkbox',
                    'label' => __('Enable this shipping method', 'y2ywsm'),
                ),
                'api_key' => array(
                    'title' => __('API Key', 'y2ywsm'),
                    'type' => 'text',
                    'description' => __('Api key description', 'y2ywsm'),
                ),
                'api_secret' => array(
                    'title' => __('API Secret', 'y2ywsm'),
                    'type' => 'text',
                    'description' => __('API secret description', 'y2ywsm'),
                ),
                'timeout' => array(
                    'title' => __('Time Out', 'y2ywsm'),
                    'type' => 'text',
                    'description' => __('Time in hours', 'y2ywsm'),
                ),
            );
        }

        public function admin_options() {
            $days = array(
                1 => __("Lundi", "y2ywsm"),
                2 => __("Mardi", "y2ywsm"),
                3 => __("Mercredi", "y2ywsm"),
                4 => __("Jeudi", "y2ywsm"),
                5 => __("Vendredi", "y2ywsm"),
                6 => __("Samedi", "y2ywsm"),
                0 => __("Dimanche", "y2ywsm"),
            );
            ?>
            <h2><?php echo $this->title; ?></h2>
            <p><?php echo $this->method_description; ?></p>
            <table class="form-table">
                <?php $this->generate_settings_html(); ?>
            </table>

            
            <table class="form-table" id="days_table" name="days_table" border="1" style="width:70%;">
                <thead>
                    <tr>
                        <td></td>
                        <td style="text-align: center"><?php echo __("Openning Hours", "y2ywsm"); ?></td>
                        <td style="text-align: center"><?php echo __("Lunch Time", "y2ywsm"); ?></td>
                        <td style="text-align: center"><?php echo __("Day Off", "y2ywsm"); ?></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    for ($i = 0; $i < 7; $i++) {
                        ?>
                        <tr>
                            <td>
                                <?php echo $days[$i] ?>
                            </td>
                            <td>
                                <?php //echo $this->generate_custom_input($this->extra_field_names[0], $i); ?>
                                <?php //echo $this->generate_custom_input($this->extra_field_names[1], $i); ?>
                                <?php echo $this->generate_custom_input($this->extra_field_names[0], $i); ?>
                                until<br>
                                <?php echo $this->generate_custom_input($this->extra_field_names[1], $i); ?>
                                <?php //echo $this->generate_custom_input($this->extra_field_names[2], $i); ?>
                                <?php //echo $this->generate_custom_input($this->extra_field_names[3], $i); ?>
                            </td>
                            <td>
                                <?php //echo $this->generate_custom_input($this->extra_field_names[4], $i); ?>
                                <?php //echo $this->generate_custom_input($this->extra_field_names[5], $i); ?>
                                <?php echo $this->generate_custom_input($this->extra_field_names[2], $i); ?>
                                until<br>
                                <?php echo $this->generate_custom_input($this->extra_field_names[3], $i); ?>
                                <?php //echo $this->generate_custom_input($this->extra_field_names[6], $i); ?>
                                <?php //echo $this->generate_custom_input($this->extra_field_names[7], $i); ?>
                            </td>
                            <td>
                                <?php echo $this->generate_custom_input($this->extra_field_names[4], $i); ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            <?php
        }
        
        public function generate_custom_input($name, $index){
            $input_name = esc_attr("woocommerce_".$this->id.'_'.$name.'['.$index.']');
            if($name == 'closed_day')
            {
                $checked = ($this->{$name}[$index]=='on') ? 'checked="checked"' : '';
                return '<input type="checkbox" '
                    . 'id="'.$input_name.'" '
                    . 'name="'.$input_name.'" '
                    . $checked.'">';
            }
            return '<input type="text" '
                    . 'id="'.$input_name.'" '
                    . 'name="'.$input_name.'" '
                    . 'value="'.$this->{$name}[$index].'" '
                    . 'class="y2ywsm-timepicker" data-field="time" readonly>'
                    . '<div id="calendar" class="dtBox"></div>';
                    /*
            return '<input type="number" '
                    . 'id="'.$input_name.'" '
                    . 'name="'.$input_name.'" '
                    . 'value="'.$this->{$name}[$index].'" '
                    . 'min="0"'
                    . 'max="24"'
                    . 'class="y2ywsm-input-number">';*/
        }
        
    }

}