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

        public function parse_resource_record($tag) {
            $tag = json_decode(json_encode($tag));

            $rrs         = [];
            $rrs['Name'] = $tag->Name;
            $rrs['Type'] = $tag->Type;
            $rrs['TTL']  = (isset($tag->TTL) ? $tag->TTL : null);

            $rrs['ResourceRecords'] = [];
            if (isset($tag->ResourceRecords->ResourceRecord)) {
                foreach ($tag->ResourceRecords->ResourceRecord as $rr) {
                    $rrs['ResourceRecords'][] = (isset($rr->Value) ? $rr->Value : $rr);
                }
            }

            $rrs['Alias'] = [];
            if (isset($tag->AliasTarget)) {
                $rrs['Alias']                = $tag->AliasTarget;
                $rrs['Alias']->SetIdentifier = $tag->SetIdentifier;
                $rrs['Alias']->Weight        = $tag->Weight;
            }

            return $rrs;
        }

        public function parse_ChangeInfo($tag) {
            $info                = [];
            $info['Id']          = (string)$tag->Id;
            $info['Status']      = (string)$tag->Status;
            $info['SubmittedAt'] = (string)$tag->SubmittedAt;

            return $info;
        }

        /**
         * http://docs.aws.amazon.com/es_es/Route53/latest/APIReference/API_ChangeResourceRecordSets_Requests.html
         *
         * @param $action
         * @param $name
         * @param $type
         * @param $ttl
         * @param $records
         *
         * @return string
         */
        public function prepare($action, $name, $type, $ttl, array $records = []) {
            $action = strtoupper($action);
            if (!in_array($action, ['CREATE', 'DELETE', 'UPSERT',])) {
                trigger_error('The action `' . $action . '´ is not allowed (CREATE | DELETE | UPSERT)', E_USER_WARNING);
            }

            /* DOC: http://docs.aws.amazon.com/es_es/Route53/latest/DeveloperGuide/resource-record-sets-values-basic.html  */
            $type = strtoupper($type);
            if (!in_array($type, ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'PTR', 'SOA', 'SPF', 'SPF', 'SRV', 'TXT'])) {
                trigger_error('The type `' . $type . '´ is not allowed (A | AAAA | CNAME | MX | NS | PTR | SOA | SPF | SRV | TXT)', E_USER_WARNING);
            }

            $change = "<Change>\n";
            $change .= '<Action>' . $action . "</Action>\n";
            $change .= "<ResourceRecordSet>\n";
            $change .= '<Name>' . $name . "</Name>\n";
            $change .= '<Type>' . $type . "</Type>\n";
            $change .= '<TTL>' . $ttl . "</TTL>\n";
            $change .= "<ResourceRecords>\n";

            foreach ($records as $record) {
                $change .= "<ResourceRecord>\n";
                if (is_array($record)) {
                    foreach ($record as $value) {
                        $change .= '<Value>' . $value . "</Value>\n";
                    }
                } else {
                    $change .= '<Value>' . $record . "</Value>\n";
                }
                $change .= "</ResourceRecord>\n";
            }

            $change .= "</ResourceRecords>\n";
            $change .= "</ResourceRecordSet>\n";
            $change .= "</Change>\n";

            return $change;
        }

        public function prepare_alias($action, $name, $type, $HostedZoneId, $DNSName, $SetIdentifier, $EvaluateTargetHealth = true, $Weight = 0, array $records = []) {
            $action = strtoupper($action);
            if (!in_array($action, ['CREATE', 'DELETE', 'UPSERT',])) {
                trigger_error('The action `' . $action . '´ is not allowed (CREATE | DELETE | UPSERT)', E_USER_WARNING);
            }

            /* DOC: http://docs.aws.amazon.com/es_es/Route53/latest/DeveloperGuide/resource-record-sets-values-basic.html  */
            $type = strtoupper($type);
            if (!in_array($type, ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'PTR', 'SOA', 'SPF', 'SPF', 'SRV', 'TXT'])) {
                trigger_error('The type `' . $type . '´ is not allowed (A | AAAA | CNAME | MX | NS | PTR | SOA | SPF | SRV | TXT)', E_USER_WARNING);
            }

            $change = "<Change>\n";
            $change .= "<Action>" . $action . "</Action>\n";
            $change .= "<ResourceRecordSet>\n";
            $change .= "    <Name>" . $name . "</Name>\n";
            $change .= "    <Type>" . $type . "</Type>\n";
            $change .= "    <SetIdentifier>" . $SetIdentifier . "</SetIdentifier>\n";
            $change .= "    <Weight>" . $Weight . "</Weight>\n";

            $change .= "    <AliasTarget>\n";
            $change .= "        <HostedZoneId>" . $HostedZoneId . "</HostedZoneId>\n";
            $change .= "        <DNSName>" . $DNSName . "</DNSName>\n";
            $change .= "        <EvaluateTargetHealth>" . $EvaluateTargetHealth . "</EvaluateTargetHealth>\n";
            $change .= "    </AliasTarget>\n";

            $change .= "</ResourceRecordSet>\n";
            $change .= "</Change>\n";

            return $change;
        }
        
        public function change_rrs($id, array $changes, $comment = '') {
            $id = trim($id, '/');

            $data = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $data .= '<ChangeResourceRecordSetsRequest xmlns="https://route53.amazonaws.com/doc/' . Route53::API_VERSION . "/\">\n";
            $data .= "<ChangeBatch>\n";

            if (strlen($comment) > 0) {
                $data .= '<Comment>' . $comment . "</Comment>\n";
            }

            $data .= "<Changes>\n";
            foreach ($changes as $change) {
                $data .= $change;
            }
            $data .= "</Changes>\n";

            $data .= "</ChangeBatch>\n";
            $data .= "</ChangeResourceRecordSetsRequest>\n";

            $request = new Request($this, $id . '/rrset', 'POST', $data);

            $request = $request->response();
            if ($request->error === false && !in_array($request->code, [200, 201, 202, 204,])) {
                $request->error = ['code' => $request->code, 'message' => 'Unexpected HTTP status',];
            }

            if ($request->error !== false) {
                $this->__triggerError('change_rrs', $request->error);

                return false;
            }

            if (!isset($request->body)) {
                return [];
            }

            return $this->parse_changeInfo($request->body->ChangeInfo);
        }

    }