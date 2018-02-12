<?php

namespace Lu1sSuarez\AWS\Facades;

use Illuminate\Support\Facades\Facade;

    /**
     * Class SDKFacade.
     */
    class Route53 extends Facade
    {
        /**
         * Get the registered name of the component.
         *
         * @return string
         *
         * @method \Lu1sSuarez\AWS\Http\Controller\Route53 get_access_key()
         */
        protected static function getFacadeAccessor()
        {
            return 'route53';
        }
    }
