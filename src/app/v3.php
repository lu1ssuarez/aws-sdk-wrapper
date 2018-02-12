<?php

namespace Lu1sSuarez\AWS;

use Lu1sSuarez\AWS\Route53\Route53;

    class v3
    {
        public $route53;

        /**
         * v3 constructor.
         *
         * @param $route53
         */
        public function __construct()
        {
            $this->route53 = new Route53();
        }
    }
