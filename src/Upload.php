<?php 
namespace SarCubet\FileUpload;

use Intervention\Image\Facades\Image;
use InvalidArgumentException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class Upload
{

    private $file;

    public function __construct()
    {
        $this->file = null;
    }

    public function optimizeImage(UploadedFile $uploadedImage, string $qualityVal)
    {   
        if(! $this->validateImage($uploadedImage)){
            throw new InvalidArgumentException('Invalid image file provided.');
        }

        if(!$this->validateQuality($qualityVal)){
            throw new InvalidArgumentException('Invalid quality provided.');
        }

        $quality = [
            'excellent' => 100,
            'moderate' => 60,
            'average' => 30
        ];

        $image = Image::make($uploadedImage);
        $image->encode($uploadedImage->getClientOriginalExtension(), $quality[$qualityVal]);

        $optimizedImage = $image->getEncoded();
        $optimizedImage = Image::make($optimizedImage);

        $optimizedImage = $this->convertToUploadedFile($optimizedImage);
        return $optimizedImage;
    }

    public function store(UploadedFile $file, string $disk, string $path='')
    {
        if(! $this->validateDisk($disk)){
            throw new InvalidArgumentException('Invalid disk provided.');
        }

        $path = Storage::disk($disk)->putFile($path, $file);
        $url = Storage::disk($disk)->url($path);
        
        # Unlink the file from tmp path
        if ($path) {
            unlink($file->getPathname());
        }

        return $url;
    }

    private function validateImage($file)
    {
        if (!$file->isValid() || !in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'])) {
            return false;
        }else{
            return true;
        }
    }


    private function validateQuality($quality)
    {
        $validQulaities = ['excellent', 'moderate', 'average'];

        if(! in_array($quality, $validQulaities)){
            return false;
        }else{
            return true;
        }
    }

    private function validateDisk($disk)
    {
        $validDisks =  array_keys(config('filesystems.disks'));

        if(! in_array($disk, $validDisks)){
            return false;
        }else{
            return true;
        }
    }

    # To convert image of type "Intervention\\Image\\Image" or any type to "Illuminate\\Http\\UploadedFile"
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
        if ($mime == 'image/jpeg')
            $extension = '.jpeg';
        elseif ($mime == 'image/jpg')
            $extension = '.jpg';
        elseif ($mime == 'image/png')
            $extension = '.png';
        elseif ($mime == 'image/gif')
            $extension = '.gif';
        else
            $extension = '';

        return $extension;
    }
}