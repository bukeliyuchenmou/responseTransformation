<?php


namespace Wdnmd;


use Illuminate\Support\ServiceProvider;

class WdnmdServiceProvider extends ServiceProvider
{
    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . "/config/wdnmd.php" => config_path("wdnmd.php"),
            ], "wdnmd-config");
            $this->commands([
                Command\WdnmdCommand::class
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/wdnmd.php', 'wdnmd'
        );
    }
}