<?php

namespace SarCubet\FileUpload\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UploadedFile extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'path'];

    public static function uploadFile($image)
    {
        $diskUrl = Storage::disk('file_upload_package')->url('');
        $diskPath = trim(parse_url($diskUrl, PHP_URL_PATH), '/');
        $diskPath = str_replace('public/', '', $diskPath);

        $filename = uniqid() . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('', $filename, 'file_upload_package');

        return $diskPath.'/'.$path;
    }
}
