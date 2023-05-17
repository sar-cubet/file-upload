# Laravel File Upload Package

[![License](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

This is a Laravel package that provides file upload functionality with ease. It simplifies the process of handling file uploads in your Laravel application.

## Features

- Simple and intuitive file upload handling.
- Integration with Laravel's validation system.
- Example code and usage instructions.

## Requirements

- PHP >= 8.0
- Laravel >= 9.52.7

## Installation

You can install the package via Composer. Run the following command:

#composer require sar-cubet/file-upload

#add this configuration to config/filesystems.php

    'file_upload_package' => [
        'driver' => 'local',
        'root' => base_path('public/vendor/file_upload_package/images'),
        'url' => env('APP_URL') . '/public/vendor/file_upload_package/images',
        'visibility' => 'public',
    ]

#publish the package

    php artisan vendor:publish --provider="SarCubet\FileUpload\FileUploadServiceProvider"

#migrate

    php artisan migrate

## Implementation

You can optimize the image by using the SarCubet\FileUpload\Facades\Upload Facade. Optimization is provided in 3 levels (excellent, moderate and average). 

    use SarCubet\FileUpload\Facades\Upload;
    $file = Upload::optimizeImage($request->file('image'), 'moderate'); 

File storage is also possible through Upload facade.

    $url = Upload::store($file, 's3');

For those who use blade template engine along with laravel, we provide an additional UI for uploading image and listing the uploaded images. Just need to add these 2 routes in web.php (use the route name "getFiles" itself)

    Route::get('/file-upload', [YourController::class, 'fileUpload']);
    Route::get('/get-files', [YourController::class, 'getFiles'])->name('getFiles');




