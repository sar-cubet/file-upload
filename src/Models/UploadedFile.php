<?php

namespace SarCubet\FileUpload\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;


class UploadedFile extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'path'];

    public static function storeFile($image, $extension)
    {
        $diskUrl = Storage::disk('file_upload_package')->url('');
        $diskPath = trim(parse_url($diskUrl, PHP_URL_PATH), '/');
        $diskPath = str_replace('public/', '', $diskPath);

        $tempPath = sys_get_temp_dir() . '/' . Str::random(40);
        $image->save($tempPath);

        $filename = uniqid() . '.' . $extension;
        // $path = $image->storeAs('', $filename, 'file_upload_package');
        $path = Storage::disk('file_upload_package')->putFileAs('', $tempPath, $filename);

        unlink($tempPath);

        return $diskPath.'/'.$path;
    }

    public static function optimizeImage($uploadedImage, $qualityVal)
    {   
        $quality = [
            'excellent' => 100,
            'moderate' => 60,
            'average' => 30
        ];

        $image = Image::make($uploadedImage);
        $image->encode($uploadedImage->getClientOriginalExtension(), $quality[$qualityVal]);

        $optimizedImage = $image->getEncoded();
        $optimizedImage = Image::make($optimizedImage);

        $path = UploadedFile::storeFile($optimizedImage, $uploadedImage->getClientOriginalExtension());
        return $path;
    }
}
