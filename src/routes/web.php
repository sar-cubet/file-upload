<?php

use Illuminate\Support\Facades\Route;
use SarCubet\FileUpload\Http\Controllers\FileUploadController;

Route::post('/upload-process', [FileUploadController::class, 'uploadProcess'])->name('uploadProcess');
Route::post('/chunk-file-upload', [FileUploadController::class, 'chunkFileUpload'])->name('chunkFileUpload');