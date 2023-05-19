<?php 
namespace SarCubet\FileUpload;

use Intervention\Image\Facades\Image;
use InvalidArgumentException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

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

    public function validateFile(UploadedFile $file, array $rulesArray = [], array $messagesArray = [])
    {
        $rules = [];
        $messages = [];

        if(count($rulesArray)){
            if(count($messagesArray)){
                if(! $this->checkRulesAndMessagesCount($rulesArray, $messagesArray)){
                    throw new InvalidArgumentException("Number of rules doesn't match number of messages.");
                }
                foreach ($rulesArray as $key => $rule) {
                    if(strpos($rule, ':') !== false){
                        $rule = explode(':', $rule)[0];
                    }
                    $messages['file.'.$rule] = $messagesArray[$key];
                }
            }

            $rules = [
                'file' => implode('|', $rulesArray)
            ];
        }else{
            $allowedImageExtensions = ['jpeg', 'jpg', 'png', 'gif'];
            $allowedDocumentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
            $allowedTextExtensions = ['txt'];
            $allowedExecutableExtensions = ['exe'];
            $extension = $file->getClientOriginalExtension();
    
            if (in_array($extension, $allowedImageExtensions)) {
                $rules = [
                    'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120'
                ];
            }else if(in_array($extension, $allowedDocumentExtensions)){
                $rules = [
                    'file' => 'required|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:5120'
                ];
            }elseif (in_array($extension, $allowedTextExtensions)) {
                $rules = [
                    'file' => 'required|mimes:txt|max:5120'
                ];
            } elseif (in_array($extension, $allowedExecutableExtensions)) {
                $rules = [
                    'file' => 'required|mimes:exe|max:5120'
                ];
            } else {
                $rules = [
                    'file' => 'required|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,exe|max:5120'
                ];
            }
        }

        $validator = $this->validate($file, $rules, $messages);
        return $validator;
    }

    private function checkRulesAndMessagesCount($rules, $messages)
    {
        return count($rules) === count($messages);
    }

    private function validate($file, $rules,$messages)
    {
        $validator = Validator::make(['file' => $file], $rules, $messages);
        return $validator;
    }
}