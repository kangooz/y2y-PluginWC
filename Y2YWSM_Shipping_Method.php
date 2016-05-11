<?php
if (!class_exists('Y2YWSM_Shipping_Method')) {

    class Y2YWSM_Shipping_Method extends WC_Shipping_Method {

        /**
         *
         * @var array The fields we use to store the opening and closing hours 
         */
        public $extra_field_names = array(
            'openning_hours_beginning',
            'openning_hours_endding',
            'lunch_time_beginning',
            'lunch_time_endding',
            'closed_day',
        );
        public $days = array();
        public $enabled;
        public $api_secret;
        public $api_key;
        public $timeout;

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

            foreach ($this->extra_field_names as $field_name) {
                $this->{$field_name} = $this->get_option($field_name, array());
            }

            $this->days = array(
                1 => __("Monday", "y2ywsm"),
                2 => __("Tuesday", "y2ywsm"),
                3 => __("Wednesday", "y2ywsm"),
                4 => __("Thursday", "y2ywsm"),
                5 => __("Friday", "y2ywsm"),
                6 => __("Saturday", "y2ywsm"),
                0 => __("Sunday", "y2ywsm"),
            );
            //Test connection
            $this->test_connection_with_api();

            //Add hook to save the options
            add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));

            //Filter for the custom fields
            add_filter('woocommerce_settings_api_sanitized_fields_' . $this->id, array($this, 'filter_update_fields'));
        }

        /**
         * Add the extra fields to save in the database and test if the connection with api is working
         * 
         * @param type $fields The default fields
         * @return array The fields to save in the database
         */
        public function filter_update_fields($fields) {

            //Validate timeout
            if (!empty($fields['timeout'])) {
                $fields['timeout'] = str_replace(",", ".", $fields['timeout']);
                $fields['timeout'] = ($fields['timeout'] > 2) ? 2 : $fields['timeout'];
            }


            for ($i = 0; $i < 7; $i++) {
                foreach ($this->extra_field_names as $field) {
                    $input_name = 'woocommerce_' . $this->id . '_' . $field;
                    if (!empty($_POST[$input_name])) {
                        $fields[$field] = $_POST[$input_name];
                    }
                }
            }

            //validate hours of days
            $error = 0;
            $days = '';
            for ($i = 0; $i < 7; $i++) {
                //Verify Closed days
                if (empty($fields['openning_hours_beginning'][$i]) || empty($fields['openning_hours_endding'][$i])) {
                    $fields['closed_day'][$i] = 'yes';
                } else if (strtotime($fields['openning_hours_beginning'][$i]) > strtotime($fields['openning_hours_endding'][$i])) {
                    unset($fields['openning_hours_beginning'][$i], $fields['openning_hours_endding'][$i], $fields['lunch_time_beginning'][$i], $fields['lunch_time_endding'][$i]
                    );
                    $fields['closed_day'][$i] = 'yes';
                } else if (isset($fields['closed_day'][$i]) && $fields['closed_day'][$i] == 'yes') {
                    $fields['openning_hours_beginning'][$i] = "";
                    $fields['openning_hours_endding'][$i] = "";
                    $fields['lunch_time_beginning'][$i] = "";
                    $fields['lunch_time_endding'][$i] = "";
                }
            }

            //Check the api
            $api = new Y2YWSM_API($fields['api_key'], $fields['api_secret']);
            if ($api->test_connection() === false) {
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
            $destination = $package['destination'];
            if (empty($destination['country']) || empty($destination['postcode'])) {
                return;
            }
            if (strtoupper($destination['country']) == 'FR') {

                if (Y2YWSM_CORE::isValidPostCode($destination['postcode'])) {
                    $rate = array(
                        'id' => $this->id,
                        'label' => __('You2You', 'y2ywsm'), //$this->title,
                        'cost' => Y2YWSM_CORE::$cost,
                    );

                    // Register the rate
                    $this->add_rate($rate);
                }
            }
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
                ),
                'api_secret' => array(
                    'title' => __('API Secret', 'y2ywsm'),
                    'type' => 'text',
                ),
                'timeout' => array(
                    'title' => __('Time Out', 'y2ywsm'),
                    'type' => 'number',
                    'custom_attributes' => array(
                        'min' => '2',
                    ),
                    'default' => 2,
                    'description' => __('Time in hours that you need to prepare a delivery. Minimum is 2 hours', 'y2ywsm'),
                ),
                'email_notification' => array(
                    'title' => __('Enable Notifications', 'y2ywsm'),
                    'type' => 'checkbox',
                    'label' => __('Enable email notifications', 'y2ywsm'),
                ),
                'email' => array(
                    'title' => __('Email', 'y2ywsm'),
                    'type' => 'email',
                ),
                'store_country' => array(
                    'title' => __('Store country', 'y2ywsm'),
                    'type' => 'text',
                ),
                'store_city' => array(
                    'title' => __('Store city', 'y2ywsm'),
                    'type' => 'text',
                ),
                'store_address' => array(
                    'title' => __('Store address', 'y2ywsm'),
                    'type' => 'text',
                ),
                'store_postalcode' => array(
                    'title' => __('Store postal code', 'y2ywsm'),
                    'type' => 'text',
                ),
                'store_information' => array(
                    'title' => __('Store additional Information', 'y2ywsm'),
                    'type' => 'textarea',
                ),
            );
        }

        public function admin_options() {
            ?>
            <h2><?php echo $this->title; ?></h2>
            <p><?php echo $this->method_description; ?></p>
            <table class="form-table">
            <?php $this->generate_settings_html(); ?>
            </table>


            <table class="form-table y2ywsm-table">
                <thead>
                    <tr>
                        <td></td>
                        <td style="text-align: center"><?php _e("Openning Hours", "y2ywsm"); ?></td>
                        <td style="text-align: center"><?php _e("Lunch Time", "y2ywsm"); ?></td>
                        <td style="text-align: center"><?php _e("Day Off", "y2ywsm"); ?></td>
                    </tr>
                </thead>
                <tbody>
            <?php
            for ($i = 0; $i < 7; $i++) {
                ?>
                        <tr>
                            <td>
                <?php echo $this->days[$i] ?>
                            </td>
                            <td>
                        <?php
                        echo $this->generate_custom_input($this->extra_field_names[0], $i)
                        . '&nbsp;' . __('until', 'y2ywsm') . '&nbsp;'
                        . $this->generate_custom_input($this->extra_field_names[1], $i);
                        ?>
                            </td>
                            <td>
                                <?php
                                echo $this->generate_custom_input($this->extra_field_names[2], $i)
                                . '&nbsp;' . __('until', 'y2ywsm') . '&nbsp;'
                                . $this->generate_custom_input($this->extra_field_names[3], $i);
                                ?>
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

                public function generate_custom_input($name, $index) {
                    $input_name = esc_attr("woocommerce_" . $this->id . '_' . $name . '[' . $index . ']');
                    if ($name == 'closed_day') {
                        return '<input type="checkbox" '
                                . 'id="' . $input_name . '" '
                                . 'name="' . $input_name . '" '
                                . checked($this->{$name}[$index], 'yes', false) . '" '
                                . 'value="yes">';
                    }
                    return '<input type="text" '
                            . 'id="' . $input_name . '" '
                            . 'name="' . $input_name . '" '
                            . 'value="' . $this->{$name}[$index] . '" '
                            . 'class="y2ywsm-timepicker" data-field="time" readonly>'
                            . '<div  class="y2ywsm-timepicker-holder"></div>';
                }

                private function test_connection_with_api() {
                    $api = new Y2YWSM_API($this->api_key, $this->api_secret);
                    if (!$api->test_connection()) {
                        if ($this->settings['enabled'] != 'no') {
                            $this->settings['enabled'] = 'no';
                            update_option('woocommerce_' . $this->id . '_settings', $this->settings);
                        }

                        add_action('admin_notices', array($this, 'add_notice_api_connection_denied'));
                        return false;
                    }
                }

                public function add_notice_api_connection_denied() {
                    echo '<div class="update-nag"><p>' . sprintf(__("The %s is disabled because the key and secret are not valid", "y2ywsm"), $this->title) . '</p></div>';
                }

            }

        }