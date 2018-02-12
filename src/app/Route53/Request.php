<?php

namespace Lu1sSuarez\AWS\Route53;

final class Request
{
    private $route53;
    private $action;
    private $verb;
    private $data;
    private $parameters = [];
    public $response;

    public function __construct($route53, $action, $verb, $data = '')
    {
        $this->route53 = $route53;
        $this->action = $action;
        $this->verb = $verb;
        $this->data = $data;
        $this->response = new \stdClass();
        $this->response->error = false;
    }

    public function set_parameter($key, $value, $replace = true)
    {
        if (!$replace && isset($this->parameters[$key])) {
            $temp = (array) $this->parameters[$key];
            $temp[] = $value;
            $this->parameters[$key] = $temp;
        } else {
            $this->parameters[$key] = $value;
        }
    }

    public function response()
    {
        $params = [];
        foreach ($this->parameters as $var => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $params[] = $var.'='.$this->url_encode($v);
                }
            } else {
                $params[] = $var.'='.$this->url_encode($value);
            }
        }

        sort($params, SORT_STRING);

        $query = implode('&', $params);

        // must be in format 'Sun, 06 Nov 1994 08:49:37 GMT'
        $date = gmdate('D, d M Y H:i:s e');

        $headers = [];
        $headers[] = 'Date: '.$date;
        $headers[] = 'Host: '.$this->route53->get_host();

        $auth = 'AWS3-HTTPS AWSAccessKeyId='.$this->route53->get_access_key();
        $auth .= ',Algorithm=HmacSHA256,Signature='.$this->get_signature($date);
        $headers[] = 'X-Amzn-Authorization: '.$auth;

        $url = 'https://'.$this->route53->get_host().'/'.$this->route53->get_api_version().'/'.$this->action.'?'.$query;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, 'Route53/Lu1sSuarez-AWS-SDK');

        @curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($curl, CURLOPT_WRITEFUNCTION, [&$this, 'response_callback']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        switch ($this->verb) {
                case 'GET':
                    break;
                case 'POST':
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->verb);
                    if (strlen($this->data) > 0) {
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
                        $headers[] = 'Content-Type: text/plain';
                        $headers[] = 'Content-Length: '.strlen($this->data);
                    }
                    break;
                case 'DELETE':
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                    break;
                default:
                    break;
            }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, false);

        if (curl_exec($curl)) {
            $this->response->code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        } else {
            $this->response->error = ['curl' => true, 'code' => curl_errno($curl), 'message' => curl_error($curl), 'resource' => $this->resource];
        }

        @curl_close($curl);

        if ($this->response->error === false && isset($this->response->body)) {
            $this->response->body = @simplexml_load_string($this->response->body);

            if (!in_array($this->response->code, [200, 201, 202, 204]) && isset($this->response->body->Error)) {
                $error = $this->response->body->Error;
                $output = [];
                $output['curl'] = false;
                $output['Error'] = [];
                $output['Error']['Type'] = (string) $error->Type;
                $output['Error']['Code'] = (string) $error->Code;
                $output['Error']['Message'] = (string) $error->Message;
                $output['RequestId'] = (string) $this->response->body->RequestId;

                $this->response->error = $output;
                unset($this->response->body);
            }
        }

        return $this->response;
    }

    private function response_callback(&$curl, &$data)
    {
        @$this->response->body .= $data;

        return strlen($data);
    }

    private function url_encode($var)
    {
        return str_replace('%7E', '~', rawurlencode($var));
    }

    private function get_signature($string)
    {
        return base64_encode(hash_hmac('sha256', $string, $this->route53->get_secret_key(), true));
    }
}
