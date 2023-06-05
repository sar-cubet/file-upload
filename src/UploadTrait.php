<?php
namespace SarCubet\FileUpload;

use Illuminate\Http\UploadedFile;

trait UploadTrait
{
    private function convertToUploadedFile($image)
    {
        $tempPath = sys_get_temp_dir() . '/' . uniqid() . $this->getExtension($image);
        $image->save($tempPath);
        $uploadedFile = new UploadedFile($tempPath, $image->basename);

        return $uploadedFile;
    }

    private function getExtension($image)
    {
        $mime = $image->mime();
        if ($mime == 'image/jpeg') {
            $extension = '.jpeg';
        } elseif ($mime == 'image/jpg') {
            $extension = '.jpg';
        } elseif ($mime == 'image/png') {
            $extension = '.png';
        } elseif ($mime == 'image/gif') {
            $extension = '.gif';
        } else {
            $extension = '';
        }

        return $extension;
    }
}