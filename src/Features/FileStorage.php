<?php
namespace SarCubet\FileUpload\Features;

use SarCubet\FileUpload\Upload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class FileStorage extends Upload
{
    public function __construct()
    {
        
    }

    
    public function store(UploadedFile $file, string $disk, string $path = '') : string
    {
        if (!$this->validateDisk($disk)) {
            throw new InvalidArgumentException('Invalid disk provided.');
        }

        $path = Storage::disk($disk)->putFile($path, $file);
        $url = Storage::disk($disk)->url($path);

        // Unlink the file from tmp path
        if ($path) {
            unlink($file->getPathname());
        }

        return $url;
    }

    private function validateDisk($disk)
    {
        $validDisks = array_keys(config('filesystems.disks'));

        if (!in_array($disk, $validDisks)) {
            return false;
        } else {
            return true;
        }
    }
}