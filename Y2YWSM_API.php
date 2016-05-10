<?php

if (!class_exists('WP_Http'))
    include_once( ABSPATH . WPINC . '/class-http.php' );


if (!class_exists('Y2YWSM_API')) {

    class Y2YWSM_API {

        protected $host = 'localhost:9050/api';
        protected $request;
        protected $url = '';
        protected $method = '';
        protected $get_params = '';
        protected $body_params = array();
        private $api_key;
        private $api_secret;
        protected $protocol = 'http';

        public function __construct($api_key, $api_secret, $options = array()) {
            $this->api_key = $api_key;
            $this->api_secret = $api_secret;

            $this->request = new WP_Http();

            $defaults = array(
                'protocol' => 'http',
                'host' => $this->host,
            );

            $options = array_merge($defaults, $options);

            $this->protocol = $options['protocol'];
            $this->host = $options['host'];
        }

        public function get($url, $params = array()) {
            $this->method = 'GET';

            $this->url = $url;

            $this->get_params = $params;

            return $this->exec();
        }

        public function post($url, $params = array()) {
            $this->method = 'POST';

            $this->url = $url;

            $this->body_params = $params;

            return $this->exec();
        }

        public function put($url, $params = array()) {
            $this->method = 'PUT';

            $this->url = $url;

            $this->body_params = $params;

            return $this->exec();
        }

        private function exec() {

            if (empty($this->method) || empty($this->url)) {
                return array(
                );
            }

            $url = $this->buildUrl();
            $params = $this->getDefaultParams();
            $result = $this->request->request($url, $params);
            
            return array(
                'result' => (isset($result['body']) ? $result['body'] : ''),
                'response' => (isset($result['response']) ? $result['response'] : self::getDefaultResponse())
            );
        }

        private function getDefaultParams() {
            return array(
                'method' => $this->method,
                'body' => $this->body_params,
                'headers' => array('api_key' => $this->api_key,
                    'api_secret' => $this->api_secret
                )
            );
        }

        private static function getDefaultResponse() {
            return array('code' => 500, 'message' => 'Server Error');
        }

        private function buildUrl() {
            $url = $this->protocol . '://' . $this->host . '/' . $this->url;

            if (!empty($this->get_params)) {
                $getParams = "";
                foreach ($this->get_params as $field_name => $field_value) {
                    if ($getParams != "") {
                        $getParams .= '&';
                    }
                    $getParams .= $field_name . '=' . urlencode($field_value);
                }

                $url .= '?' . $getParams;
            }


            //$url = $this->protocol . '://' . $this->host . '/' . $this->url . '?access_token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MSwicm9sZSI6Ikluc2NyaXQiLCJpYXQiOjE0NTQ0MzIxNTAsImV4cCI6MTQ1NDQzNTc1MH0.fBGNYzaNzqsd_e78DcXDHnzT8FFIFLFrMm6ATVl_eKI';
            return $url;
        }

        public function test_connection() {
            return true;
            if (empty($this->api_secret) || empty($this->api_key)) {
                return false;
            }

            $result = $this->get('auth/test');
            
            if (in_array($result['response']['code'], array(401, 403, 404, 500))) {
                return false;
            }

            return true;
        }

    }

}


