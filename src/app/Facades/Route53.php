<?php

    namespace Lu1sSuarez\AWS\Facades;

    use Illuminate\Support\Facades\Facade;

    /**
     * Class SDKFacade
     *
     * @package Lu1sSuarez\AWS\Facades
     */
    class Route53 extends Facade {

        /**
         * Get the registered name of the component.
         *
         * @return string
         */
        protected static function getFacadeAccessor() {
            return 'route53';
        }

    }
