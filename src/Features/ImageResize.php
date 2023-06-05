<?php
namespace SarCubet\FileUpload\Features;

use SarCubet\FileUpload\Upload;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use Intervention\Image\Facades\Image;
use SarCubet\FileUpload\UploadTrait;

class ImageResize extends Upload
{
    use UploadTrait;

    public function __construct()
    {
        
    }
    
    public function resize(int $width = null, int $height = null, UploadedFile $file, bool $constraint_aspect_ratio = false) : UploadedFile
    {
        if (!$this->validateImage($file)) {
            throw new InvalidArgumentException('Invalid image file provided.');
        }

        if ($constraint_aspect_ratio) {
            $resized_image = Image::make($file)->fit($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
        } else {
            $resized_image = Image::make($file)->resize($width, $height);
        }

        $resized_image = $this->convertToUploadedFile($resized_image);

        return $resized_image;
    }

    private function validateImage($file)
    {
        if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'])) {
            return false;
        } else {
            return true;
        }
    }
}