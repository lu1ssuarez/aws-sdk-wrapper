<?php

    namespace Lu1sSuarez\AWS\Route53;

    class Route53Extends {

        public function __triggerError($function_name, $error) {
            if ($error == false) {
                trigger_error(sprintf("Lu1sSuarez\\AWS\\Route53\\Route53::%s(): Encountered an error, but no description given", $function_name), E_USER_WARNING);
            } else {
                if (isset($error['curl']) && $error['curl']) {
                    trigger_error(sprintf("Lu1sSuarez\\AWS\\Route53\\Route53::%s(): %s %s", $function_name, $error['code'], $error['message']), E_USER_WARNING);
                } else {
                    if (isset($error['Error'])) {
                        $e       = $error['Error'];
                        $message = sprintf("Lu1sSuarez\\AWS\\Route53\\Route53::%s(): %s - %s: %s\nRequest Id: %s\n", $function_name, $e['Type'], $e['Code'], $e['Message'], $error['RequestId']);
                        trigger_error($message, E_USER_WARNING);
                    }
                }
            }
        }

        public function parse_hz($tag) {
            $zone                    = [];
            $zone['Id']              = (string)str_replace('/hostedzone/', '', $tag->Id);
            $zone['Slug']            = (string)$tag->Id;
            $zone['Name']            = (string)$tag->Name;
            $zone['CallerReference'] = (string)$tag->CallerReference;

            if (isset($tag->Config) && isset($tag->Config->Comment)) {
                $zone['Config'] = ['Comment' => (string)$tag->Config->Comment];
            }

            return $zone;
        }

        public function parse_delegation_hz($tag) {
            $servers = [];
            foreach ($tag->NameServers->NameServer as $ns) {
                $servers[] = (string)$ns;
            }

            return $servers;
        }

    }