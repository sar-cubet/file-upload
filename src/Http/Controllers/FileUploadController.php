<?php

namespace SarCubet\FileUpload\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SarCubet\FileUpload\Models\UploadedFile;

class FileUploadController extends Controller
{
    public function fileUpload()
    {
        return view('fileUpload::file-upload');
    }

    public function uploadProcess(Request $request)
    {
        $rules = [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'errors' => $validator->errors()]);
        }
        $path = UploadedFile::optimizeImage($request->file('image'), $request->quality);
        
        UploadedFile::create(['path' => $path]);

        return response()->json(['status' => 1]);
    }

    public function getFiles()
    {
        $data = UploadedFile::orderByDesc('id')->get();
        return response()->json(['status' => 1, 'data' => $data]);
    }
}