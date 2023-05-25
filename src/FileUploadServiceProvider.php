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

        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/file-upload'),
            __DIR__.'/database/migrations/' => database_path('migrations'),
            __DIR__.'/config/fileupload.php' => config_path('fileupload.php')
        ]);
    }

    public function register()
    {
        if(file_exists(config_path('fileupload.php'))){
            $this->mergeConfigFrom(
                config_path('fileupload.php'), 'fileUpload'
            );
        }else{
            $this->mergeConfigFrom(
                __DIR__.'/config/fileupload.php', 'fileUpload'
            );
        }

        $this->app->bind('upload', function($app) {
            return new Upload();
        });
    }
}

