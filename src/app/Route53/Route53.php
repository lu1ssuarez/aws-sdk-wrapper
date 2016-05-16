<?php
    
    namespace Lu1sSuarez\AWS\Route53;

    use Illuminate\Support\Facades\Config;

    /**
     * Class Route53
     *
     * @package Lu1sSuarez\AWS\Http\Controller
     */
    class Route53 extends Route53Extends {

        protected $access_key;
        protected $secret_key;
        public    $host;
        public    $api_version = '2010-10-01';

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

        public function list_hz($maxItems = 100, $marker = null) {
            $request = new Request($this, 'hostedzone', 'GET');

            if ($marker !== null) {
                $request->set_parameter('marker', $marker);
            }

            $maxItems = (int)$maxItems;
            if ($maxItems !== 0 && $maxItems !== 100) {
                $request->set_parameter('maxitems', $maxItems);
            }

            $request = $request->response();

            if ($request->error === false && $request->code !== 200) {
                $request->error = [
                    'code'    => $request->code,
                    'message' => 'Unexpected HTTP status',
                ];
            }

            if ($request->error !== false) {
                $this->__triggerError('list_hz', $request->error);

                return false;
            }

            $response = [];
            if (!isset($request->body)) {
                return $response;
            }

            $zones = [];
            foreach ($request->body->HostedZones->HostedZone as $hz) {
                $zones[] = $this->parse_hz($hz);
            }
            $response['HostedZone'] = $zones;

            if (isset($request->body->MaxItems)) {
                $response['MaxItems'] = (int)$request->body->MaxItems;
            }

            if (isset($request->body->IsTruncated)) {
                $response['IsTruncated'] = (bool)$request->body->IsTruncated;
                if ($response['IsTruncated'] === true) {
                    $response['NextMarker'] = (string)$request->body->NextMarker;
                }
            }

            return $response;
        }

        public function get_host() { return $this->host; }

        public function get_access_key() { return $this->access_key; }

        public function get_secret_key() { return $this->secret_key; }

        public function get_api_version() { return $this->api_version; }

    }
