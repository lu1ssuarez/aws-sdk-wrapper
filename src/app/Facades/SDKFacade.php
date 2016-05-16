<?php
    namespace Lu1sSuarez\AWS\Facades;

    use Illuminate\Support\Facades\Facade;

    class SDKFacade extends Facade {

        /**
         * Get the registered name of the component.
         *
         * @return string
         */
        protected static function getFacadeAccessor() {
            return 'aws_sdk';
        }

    }
