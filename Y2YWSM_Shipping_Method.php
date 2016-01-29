<?php
if (!class_exists('Y2YWSM_Shipping_Method')) {

    class Y2YWSM_Shipping_Method extends WC_Shipping_Method {

        /**
         * Constructor for your shipping class
         *
         * @access public
         * @return void
         */
        public function __construct() {
            $this->id = 'You2You';
            $this->title = __('You2you');
            $this->method_description = __('Description of your shipping method'); // 
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
            $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
            $this->init_settings(); // This is part of the settings API. Loads settings you previously init.
            // Save settings in admin if you have any defined
            
            // Define user set variables
            $this->enabled      = $this->get_option( 'enabled' );
            $this->api_key      = $this->get_option( 'api_key' );
            $this->api_secret   = $this->get_option( 'api_secret' );
            $this->teste   = $this->get_option( 'teste' );
            
            $this->timeout   = $this->get_option( 'timeout' );
            
            $this->openning_hours_beginning_h_0   = $this->get_option( 'openning_hours_beginning_h_0' );
            $this->openning_hours_beginning_h_1   = $this->get_option( 'openning_hours_beginning_h_1' );
            $this->openning_hours_beginning_h_2   = $this->get_option( 'openning_hours_beginning_h_2' );
            $this->openning_hours_beginning_h_3   = $this->get_option( 'openning_hours_beginning_h_3' );
            $this->openning_hours_beginning_h_4   = $this->get_option( 'openning_hours_beginning_h_4' );
            $this->openning_hours_beginning_h_5   = $this->get_option( 'openning_hours_beginning_h_5' );
            $this->openning_hours_beginning_h_6   = $this->get_option( 'openning_hours_beginning_h_6' );
            
            $this->openning_hours_beginning_m_0   = $this->get_option( 'openning_hours_beginning_m_0' );
            $this->openning_hours_beginning_m_1   = $this->get_option( 'openning_hours_beginning_m_1' );
            $this->openning_hours_beginning_m_2   = $this->get_option( 'openning_hours_beginning_m_2' );
            $this->openning_hours_beginning_m_3   = $this->get_option( 'openning_hours_beginning_m_3' );
            $this->openning_hours_beginning_m_4   = $this->get_option( 'openning_hours_beginning_m_4' );
            $this->openning_hours_beginning_m_5   = $this->get_option( 'openning_hours_beginning_m_5' );
            $this->openning_hours_beginning_m_6   = $this->get_option( 'openning_hours_beginning_m_6' );
            
            $this->openning_hours_endding_h_0   = $this->get_option( 'openning_hours_endding_h_0' );
            $this->openning_hours_endding_h_1   = $this->get_option( 'openning_hours_endding_h_1' );
            $this->openning_hours_endding_h_2   = $this->get_option( 'openning_hours_endding_h_2' );
            $this->openning_hours_endding_h_3   = $this->get_option( 'openning_hours_endding_h_3' );
            $this->openning_hours_endding_h_4   = $this->get_option( 'openning_hours_endding_h_4' );
            $this->openning_hours_endding_h_5   = $this->get_option( 'openning_hours_endding_h_5' );
            $this->openning_hours_endding_h_6   = $this->get_option( 'openning_hours_endding_h_6' );
            
            $this->openning_hours_endding_m_0   = $this->get_option( 'openning_hours_endding_m_0' );
            $this->openning_hours_endding_m_1   = $this->get_option( 'openning_hours_endding_m_1' );
            $this->openning_hours_endding_m_2   = $this->get_option( 'openning_hours_endding_m_2' );
            $this->openning_hours_endding_m_3   = $this->get_option( 'openning_hours_endding_m_3' );
            $this->openning_hours_endding_m_4   = $this->get_option( 'openning_hours_endding_m_4' );
            $this->openning_hours_endding_m_5   = $this->get_option( 'openning_hours_endding_m_5' );
            $this->openning_hours_endding_m_6   = $this->get_option( 'openning_hours_endding_m_6' );
            
            
            $this->lunch_time_beginning_h_0   = $this->get_option( 'lunch_time_beginning_h_0' );
            $this->lunch_time_beginning_h_1   = $this->get_option( 'lunch_time_beginning_h_1' );
            $this->lunch_time_beginning_h_2   = $this->get_option( 'lunch_time_beginning_h_2' );
            $this->lunch_time_beginning_h_3   = $this->get_option( 'lunch_time_beginning_h_3' );
            $this->lunch_time_beginning_h_4   = $this->get_option( 'lunch_time_beginning_h_4' );
            $this->lunch_time_beginning_h_5   = $this->get_option( 'lunch_time_beginning_h_5' );
            $this->lunch_time_beginning_h_6   = $this->get_option( 'lunch_time_beginning_h_6' );
            
            
            $this->lunch_time_beginning_m_0   = $this->get_option( 'lunch_time_beginning_m_0' );
            $this->lunch_time_beginning_m_1   = $this->get_option( 'lunch_time_beginning_m_1' );
            $this->lunch_time_beginning_m_2   = $this->get_option( 'lunch_time_beginning_m_2' );
            $this->lunch_time_beginning_m_3   = $this->get_option( 'lunch_time_beginning_m_3' );
            $this->lunch_time_beginning_m_4   = $this->get_option( 'lunch_time_beginning_m_4' );
            $this->lunch_time_beginning_m_5   = $this->get_option( 'lunch_time_beginning_m_5' );
            $this->lunch_time_beginning_m_6   = $this->get_option( 'lunch_time_beginning_m_6' );
            
            
            $this->lunch_time_endding_h_0   = $this->get_option( 'lunch_time_endding_h_0' );
            $this->lunch_time_endding_h_1   = $this->get_option( 'lunch_time_endding_h_1' );
            $this->lunch_time_endding_h_2   = $this->get_option( 'lunch_time_endding_h_2' );
            $this->lunch_time_endding_h_3   = $this->get_option( 'lunch_time_endding_h_3' );
            $this->lunch_time_endding_h_4   = $this->get_option( 'lunch_time_endding_h_4' );
            $this->lunch_time_endding_h_5   = $this->get_option( 'lunch_time_endding_h_5' );
            $this->lunch_time_endding_h_6   = $this->get_option( 'lunch_time_endding_h_6' );
            
            
            $this->lunch_time_endding_m_0   = $this->get_option( 'lunch_time_endding_m_0' );
            $this->lunch_time_endding_m_1   = $this->get_option( 'lunch_time_endding_m_1' );
            $this->lunch_time_endding_m_2   = $this->get_option( 'lunch_time_endding_m_2' );
            $this->lunch_time_endding_m_3   = $this->get_option( 'lunch_time_endding_m_3' );
            $this->lunch_time_endding_m_4   = $this->get_option( 'lunch_time_endding_m_4' );
            $this->lunch_time_endding_m_5   = $this->get_option( 'lunch_time_endding_m_5' );
            $this->lunch_time_endding_m_6   = $this->get_option( 'lunch_time_endding_m_6' );
            
            add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
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
                'label' => $this->title,
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
                    'title' => __('API Key', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Api key description', 'y2ywsm'),
                ),
                'api_secret' => array(
                    'title' => __('API Secret', 'yeywsm'),
                    'type' => 'text',
                    'description' => __('API secret description', 'y2ywsm'),
                ),
                'timeout' => array(
                    'title' => __('Time Out', 'yeywsm'),
                    'type' => 'text',
                    'description' => __('Time in hours', 'y2ywsm'),
                ),
                /* Openning hours beginning*/
//                'openning_hours_beginning_h' => array(
//                    'title' => __('Openning hours beginning (H)', 'yeywsm'),
//                    'type' => 'text',
//                    'value' => '09',
//                    'css' => 'width:30px;',
//                    'description' => __('Hours where the shop is open to public in hours. IE: 09h30 -> 09', 'y2ywsm'),
//
//                ),
                /*
                'openning_hours_beginning_m' => array(
                    'title' => __('Openning hours beginning (M)', 'yeywsm'),
                    'type' => 'text',
                    'value' => '30',
                    'description' => __('Hours where the shop is open to public in minutes. IE: 09h30 -> 30', 'y2ywsm'),
                ),
                'openning_hours_endding_h' => array(
                    'title' => __('Closing hours endding (H)', 'yeywsm'),
                    'type' => 'text',
                    'value' => '18',
                    'description' => __('Hours where the shop closes to public in hours. IE: 18h30 -> 18', 'y2ywsm'),
                ),
                'openning_hours_endding_m' => array(
                    'title' => __('Closing hours endding (M)', 'yeywsm'),
                    'type' => 'text',
                    'value' => '30',
                    'description' => __('Hours where the shop closes to public in minutes. IE: 18h30 -> 30', 'y2ywsm'),
                ),
                'openning_hours_endding_h' => array(
                    'title' => __('Closing hours endding (H)', 'yeywsm'),
                    'type' => 'text',
                    'value' => '18',
                    'description' => __('Hours where the shop closes to public in hours. IE: 18h30 -> 18', 'y2ywsm'),
                ),
                'openning_hours_endding_m' => array(
                    'title' => __('Closing hours endding (M)', 'yeywsm'),
                    'type' => 'text',
                    'value' => '30',
                    'description' => __('Hours where the shop closes to public in minutes. IE: 18h30 -> 30', 'y2ywsm'),
                ),
                
                'lunch_time_beginning_m' => array(
                    'title' => __('Lunch time', 'yeywsm'),
                    'type' => 'label',
                ),
                'lunch_time_beginning_h' => array(
                    'title' => __('Lunch Time Beginning (H)', 'yeywsm'),
                    'type' => 'text',
                    'value' => '12',
                    'description' => __('Hours where the shop closes to public in hours during lunch. IE: 12h30 -> 12', 'y2ywsm'),
                ),
                'lunch_time_beginning_m' => array(
                    'title' => __('Lunch Time Beginning (M)', 'yeywsm'),
                    'type' => 'text',
                    'value' => '30',
                    'description' => __('Hours where the shop closes to public in hours during lunch. IE: 12h30 -> 30', 'y2ywsm'),
                ),
                
                'lunch_time_endding_h' => array(
                    'title' => __('Lunch Time Endding (H)', 'yeywsm'),
                    'type' => 'text',
                    'value' => '13',
                    'description' => __('Hours where the shop reopens to public in hours after lunch. IE: 13h30 -> 13', 'y2ywsm'),
                ),
                'lunch_time_endding_m' => array(
                    'title' => __('Lunch Time Endding (M)', 'yeywsm'),
                    'type' => 'text',
                    'value' => '30',
                    'description' => __('Hours where the shop reopens to public in hours after lunch. IE: 13h30 -> 30', 'y2ywsm'),
                ),
                'select' => array(
                    'title' => __('select', 'yeywsm'),
                    'type' => 'select',
                    'values' => array(
                        '1' => 'ola',
                        '2' => 'ole',
                    ),
                    'description' => __('Hours where the shop reopens to public in hours after lunch. IE: 13h30 -> 30', 'y2ywsm'),
                ),*/
            );
        }

        public function admin_options() {
            ?>
            <h2><?php _e('You2You shipping method', 'y2ywsm'); ?></h2>
            <table class="form-table">
                <?php $this->generate_settings_html(); ?>
            </table>
            <?php /*
            <table class="form-table">
                <tr>
                <td>teste</td>
                  <td>
                      <input type="text" class="input-text regular-input" id="woocommerce_You2You_teste" name="woocommerce_You2You_teste" value="<?php echo $this->teste;?>" size="2">
                  </td>
                </tr>
            </table>
            */ ?>
            
            <?php
            /*
            echo 'aqui: ';
echo var_dump($this);
$this->api_secret1 = "ola";
$i=1;
echo $this->{api_secret.$i};*/

                    $days = array(
                        0 => "Sunday",
                        1 => "Monday",
                        2 => "Tuesday",
                        3 => "Wednesday",
                        4 => "Thursday",
                        5 => "Friday",
                        6 => "Saturday",
                    );
                    
                    /*for($i=0;$i<7;$i++)
                    {*/


                        ?>
            <table class="form-table" border="1" style="width:70%;">
                <?php /*
                <h2></h2>
                <tr>
                  <td>teste</td>
                  <td>
                      <input type="text" id="woocommerce_You2You_teste" name="woocommerce_You2You_teste" value="<?php echo $this->teste;?>" size="2">
                      until
                      <input type="text" id="woocommerce_You2You_teste" name="woocommerce_You2You_teste" value="<?php echo $this->teste;?>" size="2">
                  </td>
                </tr>
                <tr>
                  <td>Openning Hours</td>
                  <td>
                      <input type="text" id="woocommerce_You2You_openning_hours_beginning_h_<?php echo $i; ?>" name="woocommerce_You2You_openning_hours_beginning_h_<?php echo $i; ?>" value="<?php echo $this->{openning_hours_beginning_h.$i}?>" size="2">h<input type="text" id="woocommerce_You2You_openning_hours_beginning_m_<?php echo $i; ?>" name="woocommerce_You2You_openning_hours_beginning_m_<?php echo $i; ?>" value="<?php echo $this->{woocommerce_You2You_openning_hours_beginning_m_.$i}; ?>" size="2">m
                      until
                    <input type="text" id="woocommerce_You2You_openning_hours_endding_h_<?php echo $i; ?>" name="woocommerce_You2You_openning_hours_endding_h_<?php echo $i; ?>" value="<?php echo $this->{openning_hours_endding_h.$i}?>" size="2">h<input type="text" id="woocommerce_You2You_openning_hours_endding_m_<?php echo $i; ?>" name="woocommerce_You2You_openning_hours_endding_m_<?php echo $i; ?>"  value="<?php echo $this->{openning_hours_endding_m_.$i}?>" size="2">m
                  </td>
                </tr>
                <tr>
                  <td>Lunch Time</td>
                  <td>
                      <input type="text" id="woocommerce_You2You_lunch_time_beginning_h_<?php echo $i; ?>" name="woocommerce_You2You_lunch_time_beginning_h_<?php echo $i; ?>" value="<?php echo $this->{lunch_time_beginning_h_.$i}?>" size="2">h <input type="text" id="woocommerce_You2You_lunch_time_beginning_m_<?php echo $i; ?>" name="woocommerce_You2You_lunch_time_beginning_m_<?php echo $i; ?>"  value="<?php echo $this->{lunch_time_beginning_m_.$i}?>" size="2">m
                      until
                      <input type="text" id="woocommerce_You2You_lunch_time_endding_h_<?php echo $i; ?>" name="woocommerce_You2You_openning_hours_endding_h_<?php echo $i; ?>" value="<?php echo $this->{openning_hours_endding_h_.$i}?>" size="2">h<input type="text" id="woocommerce_You2You_lunch_time_endding_m_<?php echo $i; ?>" name="woocommerce_You2You_lunch_time_endding_m_<?php echo $i; ?>"  value="<?php echo $this->{openning_hours_endding_m_.$i}?>" size="2">m
                  </td>
                </tr>
                 */ ?>
                
                <?php
                ?>
                <thead>
                    <tr>
                        <td></td>
                        <td style="text-align: center">Openning Hours</td>
                        <td style="text-align: center">Lunch Time</td>
                    </tr>
                </thead>
                <tbody
                <?php
                for($i=0;$i<7;$i++)
                {
                     ?>
                        <tr>
                        <td>
                            <?php echo $days[$i] ?>
                        </td>
                          <td>
                            <input type="text" id="woocommerce_You2You_openning_hours_beginning_h_<?php echo $i; ?>" name="woocommerce_You2You_openning_hours_beginning_h_<?php echo $i; ?>" value="<?php echo $this->{openning_hours_beginning_h.$i}?>" size="2">h<input type="text" id="woocommerce_You2You_openning_hours_beginning_m_<?php echo $i; ?>" name="woocommerce_You2You_openning_hours_beginning_m_<?php echo $i; ?>" value="<?php echo $this->{woocommerce_You2You_openning_hours_beginning_m_.$i}; ?>" size="2">m
                            until
                            <input type="text" id="woocommerce_You2You_openning_hours_endding_h_<?php echo $i; ?>" name="woocommerce_You2You_openning_hours_endding_h_<?php echo $i; ?>" value="<?php echo $this->{openning_hours_endding_h.$i}?>" size="2">h<input type="text" id="woocommerce_You2You_openning_hours_endding_m_<?php echo $i; ?>" name="woocommerce_You2You_openning_hours_endding_m_<?php echo $i; ?>"  value="<?php echo $this->{openning_hours_endding_m_.$i}?>" size="2">m
                          </td>
                          <td>
                              <input type="text" id="woocommerce_You2You_lunch_time_beginning_h_<?php echo $i; ?>" name="woocommerce_You2You_lunch_time_beginning_h_<?php echo $i; ?>" value="<?php echo $this->{lunch_time_beginning_h_.$i}?>" size="2">h <input type="text" id="woocommerce_You2You_lunch_time_beginning_m_<?php echo $i; ?>" name="woocommerce_You2You_lunch_time_beginning_m_<?php echo $i; ?>"  value="<?php echo $this->{lunch_time_beginning_m_.$i}?>" size="2">m
                              until
                              <input type="text" id="woocommerce_You2You_lunch_time_endding_h_<?php echo $i; ?>" name="woocommerce_You2You_openning_hours_endding_h_<?php echo $i; ?>" value="<?php echo $this->{openning_hours_endding_h_.$i}?>" size="2">h<input type="text" id="woocommerce_You2You_lunch_time_endding_m_<?php echo $i; ?>" name="woocommerce_You2You_lunch_time_endding_m_<?php echo $i; ?>"  value="<?php echo $this->{openning_hours_endding_m_.$i}?>" size="2">m
                          </td>
                        </tr>
                <?php
                }
                ?>
                </tbody>
                <?php
                /*
                <thead>
                    <tr>
                        <th>Days</th>
                        <?php
                        for($i=0;$i<7;$i++)
                        {
                        echo '<th style="text-align: center">'.$days[$i].'</th>';
                        }
                        ?>
                    </tr>
                  </thead>
                  <tbody>
                      <tr>
                          <td></td>
                       <?php
                        for($i=0;$i<7;$i++)
                        {
                            ?>
                            <td style="text-align: center"><input type="text" id="woocommerce_You2You_openning_hours_beginning_h_<?php echo $i; ?>" name="woocommerce_You2You_openning_hours_beginning_h_<?php echo $i; ?>" value="<?php echo $this->{openning_hours_beginning_h.$i}?>" size="2">h<input type="text" id="woocommerce_You2You_openning_hours_beginning_m_<?php echo $i; ?>" name="woocommerce_You2You_openning_hours_beginning_m_<?php echo $i; ?>" value="<?php echo $this->{woocommerce_You2You_openning_hours_beginning_m_.$i}; ?>" size="2">m
                                <br>until<br>
                            <input type="text" id="woocommerce_You2You_openning_hours_endding_h_<?php echo $i; ?>" name="woocommerce_You2You_openning_hours_endding_h_<?php echo $i; ?>" value="<?php echo $this->{openning_hours_endding_h.$i}?>" size="2">h<input type="text" id="woocommerce_You2You_openning_hours_endding_m_<?php echo $i; ?>" name="woocommerce_You2You_openning_hours_endding_m_<?php echo $i; ?>"  value="<?php echo $this->{openning_hours_endding_m_.$i}?>" size="2">m</td>
                            <?php
                        } ?>
                    </tr>
                    <tr>
                        <td></td>
                       <?php
                        for($i=0;$i<7;$i++)
                        {
                            ?>
                            <td style="text-align: center"><input type="text" id="woocommerce_You2You_lunch_time_beginning_h_<?php echo $i; ?>" name="woocommerce_You2You_lunch_time_beginning_h_<?php echo $i; ?>" value="<?php echo $this->{lunch_time_beginning_h_.$i}?>" size="2">h <input type="text" id="woocommerce_You2You_lunch_time_beginning_m_<?php echo $i; ?>" name="woocommerce_You2You_lunch_time_beginning_m_<?php echo $i; ?>"  value="<?php echo $this->{lunch_time_beginning_m_.$i}?>" size="2">m
                            until
                            <input type="text" id="woocommerce_You2You_lunch_time_endding_h_<?php echo $i; ?>" name="woocommerce_You2You_openning_hours_endding_h_<?php echo $i; ?>" value="<?php echo $this->{openning_hours_endding_h_.$i}?>" size="2">h<input type="text" id="woocommerce_You2You_lunch_time_endding_m_<?php echo $i; ?>" name="woocommerce_You2You_lunch_time_endding_m_<?php echo $i; ?>"  value="<?php echo $this->{openning_hours_endding_m_.$i}?>" size="2">m</td>
                            <?php
                        } ?>
                    </tr>
                  </tbody>
                 */ ?>
            </table>
            <?php
           /*}*/
        }

    }

}