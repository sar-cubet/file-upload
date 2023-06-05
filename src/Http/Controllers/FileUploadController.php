<?php

namespace SarCubet\FileUpload\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        # ------------------ Default validation -------------------- 
            // $validator = Upload::validateFile($request->file('file'));
            // if ($validator->fails()) {
            //     return response()->json(['status' => 0, 'errors' => $validator->errors()]);
            // }
        # -------------------------- End --------------------------- 

        # ------------------ Custom mimes validation --------------------
            // $rules = ['mimes:jpeg,jpg,png' => 'The file must be a file of following types: jpeg,jpg,png.'];
            // $validator = Upload::validateFile($request->file('file'), $rules);
            // if ($validator->fails()) {
            //     return response()->json(['status' => 0, 'errors' => $validator->errors()]);
            // }
        # -------------------------- End --------------------------- 

        # ------------------ Custom max validation --------------------
            // $rules = ['max:5120' => 'The file must not be greater than 5 MB.'];
            // $validator = Upload::validateFile($request->file('file'), $rules);
            // if ($validator->fails()) {
            //     return response()->json(['status' => 0, 'errors' => $validator->errors()]);
            // }
        # -------------------------- End --------------------------- 
        
        # ------------------ Optimize Image --------------------
            // $file = Upload::optimizeImage($request->file('file'), $request->quality);
        # -------------------------- End --------------------------- 

        # ------------------ Scan file --------------------
            // $scan_file = Upload::scanFile($request->file('file'));
            // if ($scan_file->isFileInfected()) {
            //     return "This file found with the malware :". $scan_file->getMalwareName();
            // } else {
            //     return "This file is safe to upload.";
            // }
        # -------------------------- End ---------------------------

        # ------------------ Resize Image --------------------
            // $resized_file = Upload::resize(200, null, $file, false);
            // $url  = Upload::store($resized_file, 'public');
            
            // return response()->json(['status' => 1]);
        # -------------------------- End ---------------------------
    }

    public function chunkFileUpload(Request $request)
    {
        $receive = Upload::receiveChunks($request);
        if($receive->isUploadComplete()){
            Upload::store($receive->getFile(), 'public');
            return true;
        }else{
            return false;
        }
    }
}