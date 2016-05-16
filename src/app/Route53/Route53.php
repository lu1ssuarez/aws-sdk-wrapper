<?php
    
    namespace Lu1sSuarez\AWS\Route53;

    use Illuminate\Support\Facades\Config;

    /**
     * Class Route53
     *
     * @package Lu1sSuarez\AWS\Http\Controller
     */
    class Route53 {

        protected $access_key;
        protected $secret_key;
        public    $host;

        const API_VERSION = '2010-10-01';

        /**
         * Route53 constructor.
         *
         * @param null $access_key
         * @param null $secret_key
         */
        public function __construct($access_key = null, $secret_key = null) {

            $this->access_key = Config::get('aws_sdk.access_key', $access_key);
            $this->secret_key = Config::get('aws_sdk.secret_key', $secret_key);
            $this->host       = Config::get('aws_sdk.route53_host', 'route53.amazonaws.com');

        }

        public function get_host() {
            return $this->host;
        }

    }
