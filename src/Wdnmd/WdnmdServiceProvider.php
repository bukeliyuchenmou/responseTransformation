<?php


namespace Wdnmd;


use Illuminate\Support\ServiceProvider;

class WdnmdServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__."/config/wdnmd.php" => config_path("wdnmd.php"),
        ], "wdnmd-config");
    }
}