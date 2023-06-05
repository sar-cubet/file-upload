<?php

namespace SarCubet\FileUpload;

use Illuminate\Support\ServiceProvider;
use SarCubet\FileUpload\Console\InstallFileUploadPackage;

class FileUploadServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        $this->publishes([
            __DIR__.'/config/fileupload.php' => config_path('fileupload.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallFileUploadPackage::class,
            ]);
        }
    }

    public function register()
    {
        if (file_exists(config_path('fileupload.php'))) {
            $this->mergeConfigFrom(
                config_path('fileupload.php'),
                'fileUpload'
            );
        } else {
            $this->mergeConfigFrom(
                __DIR__.'/config/fileupload.php',
                'fileUpload'
            );
        }

        $this->app->bind('upload', function ($app) {
            return new Upload();
        });
    }
}
