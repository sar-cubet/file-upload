<?php
namespace SarCubet\FileUpload\Features;

use SarCubet\FileUpload\Upload;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use Intervention\Image\Facades\Image;
use SarCubet\FileUpload\UploadTrait;

class ImageOptimize extends Upload
{
    use UploadTrait;

    public function __construct()
    {
        
    }
    
    public function optimizeImage(UploadedFile $uploadedImage, string $qualityVal) : UploadedFile
    {
        if (!$this->validateImage($uploadedImage)) {
            throw new InvalidArgumentException('Invalid image file provided.');
        }

        if (!$this->validateQuality($qualityVal)) {
            throw new InvalidArgumentException('Invalid quality provided.');
        }

        $quality = [
            'excellent' => 100,
            'moderate' => 60,
            'average' => 30,
        ];

        $image = Image::make($uploadedImage);
        $image->encode($uploadedImage->getClientOriginalExtension(), $quality[$qualityVal]);

        $optimizedImage = $image->getEncoded();
        $optimizedImage = Image::make($optimizedImage);

        $optimizedImage = $this->convertToUploadedFile($optimizedImage);
        return $optimizedImage;
    }

    private function validateImage($file)
    {
        if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'])) {
            return false;
        } else {
            return true;
        }
    }

    private function validateQuality($quality)
    {
        $validQualities = ['excellent', 'moderate', 'average'];

        if (!in_array($quality, $validQualities)) {
            return false;
        } else {
            return true;
        }
    }
}