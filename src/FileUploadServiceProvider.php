<?php
namespace SarCubet\FileUpload;

use Illuminate\Support\ServiceProvider;

class FileUploadServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'fileUpload');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        // $this->mergeConfigFrom(
        //     __DIR__.'/config/aws.php', 'fileUpload'
        // );
    }

    public function register()
    {

    }
}

