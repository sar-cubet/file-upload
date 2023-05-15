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

        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/file-upload'),

        ]);
        $this->publishes([
            __DIR__.'/database/migrations/' => database_path('migrations')
        ]);
    }

    public function register()
    {

    }
}

