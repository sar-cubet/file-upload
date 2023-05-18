<?php

namespace SarCubet\FileUpload\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SarCubet\FileUpload\Models\UploadedFile;
use SarCubet\FileUpload\Facades\Upload;

class FileUploadController extends Controller
{
    public function fileUpload()
    {
        return view('fileUpload::file-upload');
    }

    public function uploadProcess(Request $request)
    {
        $validator = Upload::validateFile($request->file('file'));

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'errors' => $validator->errors()]);
        }

        $file = Upload::optimizeImage($request->file('image'), $request->quality);
        $url = Upload::store($file, 's3');
        
        UploadedFile::create(['path' => $url]);

        return response()->json(['status' => 1]);
    }

    public function getFiles()
    {
        $data = UploadedFile::orderByDesc('id')->get();
        return response()->json(['status' => 1, 'data' => $data]);
    }
}