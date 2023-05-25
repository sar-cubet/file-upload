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
        // $rules = [];

        // $rules = [
        //     'required'           => 'File is mandatory',
        //     'mimes:jpeg,jpg,png' => 'The file must be a file of following types: jpeg,jpg,png.',
        //     'max:5120'           => 'The file must not be greater than 5 MB.'
        // ];
        
        // $rules = ['max:5120'];
        // $messages = ['The file must not be greater than 5 MB.'];
        
        $validator = Upload::validateFile($request->file('file'));
        dd($validator->errors()->all());

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'errors' => $validator->errors()]);
        }

        $file = Upload::optimizeImage($request->file('file'), $request->quality);
        $resized_file = Upload::resize(200, null, $file, false);
        $url  = Upload::store($resized_file, 'public');
        
        UploadedFile::create(['path' => $url]);

        return response()->json(['status' => 1]);
    }

    public function getFiles()
    {
        $data = UploadedFile::orderByDesc('id')->get();
        return response()->json(['status' => 1, 'data' => $data]);
    }
}