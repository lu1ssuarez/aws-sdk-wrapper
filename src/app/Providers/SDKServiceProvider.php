<?php
    namespace Lu1sSuarez\AWS\Providers;

    use Illuminate\Foundation\Application as LaravelApplication;
    use Illuminate\Support\ServiceProvider;
    use Laravel\Lumen\Application as LumenApplication;

    /**
     * AWS SDK for PHP service provider for Laravel applications
     */
    class SDKServiceProvider extends ServiceProvider {
        const VERSION = '3.1.0';

        /**
         * Indicates if loading of the provider is deferred.
         *
         * @var bool
         */
        protected $defer = true;

        /**
         * Bootstrap the configuration
         *
         * @return void
         */
        public function boot() {
            $source = realpath(__DIR__ . 'config/aws_sdk.php');

            if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
                $this->publishes([$source => config_path('aws_sdk.php')]);
            } elseif ($this->app instanceof LumenApplication) {
                $this->app->configure('aws_sdk');
            }

            $this->mergeConfigFrom($source, 'aws_sdk');
        }

        /**
         * Register the service provider.
         *
         * @return void
         */
        public function register() {
            $this->app->singleton('aws_sdk', function ($app) {
                $config = $app->make('config')->get('aws_sdk');

                return new AWS_SDK($config);
            });

            $this->app->alias('aws_sdk', 'Lu1sSuarez\SDK');
        }

        /**
         * Get the services provided by the provider.
         *
         * @return array
         */
        public function provides() {
            return [
                'aws_sdk',
                'Lu1sSuarez\SDK',
            ];
        }

    }
