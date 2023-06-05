# Laravel File Upload Package

[![License](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

This is a Laravel package that provides file upload functionality with ease. It simplifies the process of handling file uploads in your Laravel application.

## Features

- Simple and intuitive file upload handling.
- Integration with Laravel's validation system.
- Image Optimization.
- Image Resize.
- File Storage.
- Chunk File Upload for large files.
- Virus scan.

## Requirements

- PHP >= 8.0
- Laravel >= 9.52.7

## Installation

You can install the package via **Composer**. Run the following command:

    composer require sar-cubet/file-upload

## Installing the package (publishing the resources)

    php artisan fileupload:install

## Implementation

We provide `SarCubet\FileUpload\Facades\Upload` Facade which you can use to perform operations like file validation, image optimization, file storage etc. 
You can validate your file by using the `Upload::validate()` method. The `Upload::validateFile()` accepts a file of type `Illuminate\Http\UploadedFile` and return a `Illuminate\Support\Facades\Validator` object that you can use as per the application requirement. All the supported file types can be found inside the config file **config/fileupload.php** (which will be published). You can modify the config as per your requirement. Also if you wish to add any other file types, you can add it up inside **allowed_file_extensions.others** list in the config file.

```php
'allowed_file_extensions' => [
    'image' => ['jpeg', 'jpg', 'png', 'gif'],
    'doc' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
    'text' => ['txt'],
    'others' => []
]
```

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

If you wish to scan the uploaded file for any type of malwares. You can use the `Upload::scanFile()` method(**Note: Its recomented to use before using `validateFile()` or any other services.**). Under the hood we are making use of **ClamAV anti-virus scanner**. So you need to install ClamAV in your machine. 
Here are the commands for installation (Ubuntu):

```
# Install clamav virus scanner
sudo apt-get update && sudo apt-get install -y clamav-daemon
```

```
# Update virus definitions
sudo freshclam
```

```
# Start the scanner service
sudo systemctl enable --now clamav-daemon clamav-freshclam
```

Additionally you can use `isFileInfected()` and `getMalwareName()` methods identify and provide information of any malware like below:

```php
$scan_file = Upload::scanFile($request->file('file'));
        
if ($scan_file->isFileInfected()) {
    return "This file is found with the malware :" . $scan_file->getMalwareName() . '.';
} else {
    return "This file is safe to upload.";
}
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

File storage is possible through `Upload::store()` method. `store()` accepts 3 parameters: **file, disk_name, path (optional)**

```php
$url = Upload::store($file, 's3');
```

or

```php
$url = Upload::store($file, 's3', 'your_path');
```

Chunk file upload functionlaity is provided. You can use `Upload::receiveChunks()` method to receive chunks and its metadata. You can pass the `$request` instance into the method. The function will automatically recieve the metadata and each chunks and combine the chunks into a new file when all chunks are received. 

```php
use Illuminate\Http\Request;

public function chunkFileUpload(Request $request)
{
    $recieve = Upload::receiveChunks($request);
}
```

The first Http POST request should contain the metadata and then the chunks should be send. The metadata should contain the data as shown below.

```javascript
metadata:{
    chunk_size: chunk_size,
    total_chunk_count: total_chunk_count,
    file_size: file_size,
    file_extension: file_extension
}
```

You can check upload status and store the file like below.

```php
if($receive->isUploadComplete()){
    Upload::store($receive->getFile(), 'public');
}
```

We also provide two methods for getting the last uploaded chunk and upload progress in percentage.

```php
$receive->getLastUploadedChunkIndex();
$receive->getUploadProgressInPercentage();
```







