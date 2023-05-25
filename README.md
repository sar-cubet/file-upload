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

You can install the package via **Composer**. Run the following command:

    composer require sar-cubet/file-upload

## Publish the package

    php artisan vendor:publish --provider="SarCubet\FileUpload\FileUploadServiceProvider"

## Migrate

    php artisan migrate

## Implementation

We provide `SarCubet\FileUpload\Facades\Upload` Facade which you can use to perform operations like file validation, image optimization, file storage etc. 
You can validate your file by using the `Upload::validate()` method. The `Upload::validateFile()` accepts a file of type `Illuminate\Http\UploadedFile` and return a `Illuminate\Support\Facades\Validator` object you can use as per the application requirement. This `validateFile()` currently supports file types **(jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,exe) of size upto (5 mb)**.

```php
use SarCubet\FileUpload\Facades\Upload;

$validator = Upload::validateFile($request->file('file'));
if ($validator->fails()) {
    return response()->json(['status' => 0, 'errors' => $validator->errors()]);
}
```

If you want to use custom validation and/or validation messages you can pass the rules and messages as an associative array (optional)

```php
$rules = [
    'required'      => 'The file is required',
    'mimes:jpg,png' => 'The file types should be any of the following: jpg,png',
    'max:5120'      => 'The file size should not exceed 5MB'
];

$validator = Upload::validateFile($request->file('file'), $rules);
```

You can optimize the image by using `Upload::optimizeImage()` method. Optimization is provided in 3 levels **(excellent, moderate and average)**. 

```php
$file = Upload::optimizeImage($request->file('image'), 'moderate'); 
```

You can resize the image by using `Upload::resize()` method. The `resize()` method accepts 4 parameters: **width, height, file and preserve_aspect_ratio (optional)**

```php
Upload::resize(200, null, $file);
```

or

```php
Upload::resize(200, null, $file, true);
```

File storage is also possible through `Upload::store()` method. `store()` accepts 3 parameters: **file, disk_name, path (optional)**

```php
$url = Upload::store($file, 's3');
```

or

```php
$url = Upload::store($file, 's3', 'your_path');
```


For those who use **blade template engine** along with laravel, we provide an additional UI for uploading image and listing the uploaded images. Just need to add these 2 **routes** in **web.php** (use the route name **"getFiles"** itself)

```php
Route::get('/file-upload', [YourController::class, 'fileUpload']);
Route::get('/get-files', [YourController::class, 'getFiles'])->name('getFiles');
```

Load view file for file upload

```php
return view('fileUpload::file-upload');
```

Function for listing files

```php
public function getFiles()
{
    $data = UploadedFile::orderByDesc('id')->get();
    // Your logic
    return response()->json(['status' => 1, 'data' => $data]);
}
```


