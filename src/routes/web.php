<?php

use Illuminate\Support\Facades\Route;
use SarCubet\FileUpload\Http\Controllers\FileUploadController;

Route::get('/file-upload', [FileUploadController::class, 'fileUpload']);
Route::post('/upload-process', [FileUploadController::class, 'uploadProcess'])->name('uploadProcess');
Route::get('/get-files', [FileUploadController::class, 'getFiles'])->name('getFiles');

Route::post('/chunk-file-upload', [FileUploadController::class, 'chunkFileUpload'])->name('chunkFileUpload');