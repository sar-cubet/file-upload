# Laravel File Upload Package

[![License](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

This is a Laravel package that provides file upload functionality with ease. It simplifies the process of handling file uploads in your Laravel application.

## Features

- Simple and intuitive file upload handling.
- Integration with Laravel's validation system.
- Example code and usage instructions.

## Requirements

- PHP >= 9.52.7
- Laravel >= 8.0

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

