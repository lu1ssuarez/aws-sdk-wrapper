<?php
    
    namespace Lu1sSuarez\AWS\Http\Controller;

    use Illuminate\Support\Facades\Config;

    /**
     * Class Route53
     *
     * @package Lu1sSuarez\AWS\Http\Controller
     */
    class Route53 {

        protected static $access_key;
        protected static $secret_key;
        protected static $host;


        /**
         * Route53 constructor.
         *
         * @param null $access_key
         * @param null $secret_key
         */
        public function __construct($access_key = null, $secret_key = null) {

            self::$access_key = Config::get('aws_sdk.access_key', $access_key);
            self::$secret_key = Config::get('aws_sdk.secret_key', $secret_key);
            self::$host       = Config::get('aws_sdk.route53_host', 'route53.amazonaws.com');

        }

        public static function get_access_key() {
            return self::$access_key;
        }

    }