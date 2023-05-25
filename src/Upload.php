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

    public function validateFile(UploadedFile $file, array $rulesAndMessagesArray = [])
    {
        $rulesArray = array_keys($rulesAndMessagesArray);
        $messagesArray = array_values($rulesAndMessagesArray);

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
            $allowedImageExtensions = config('fileUpload.allowed_file_extensions.image');
            $allowedDocumentExtensions = config('fileUpload.allowed_file_extensions.doc');
            $allowedTextExtensions = config('fileUpload.allowed_file_extensions.text');
            $allowedOtherExtensions = config('fileUpload.allowed_file_extensions.others');
            $sizeLimit = config('fileUpload.default_file_size_limit');
            $extension = $file->getClientOriginalExtension();
    
            if (in_array($extension, $allowedImageExtensions)) {
                $mimes = implode(',', $allowedImageExtensions);
                $rules = [
                    'file' => 'required|image|mimes:'.$mimes.'|max:'.$sizeLimit
                ];

            }else if(in_array($extension, $allowedDocumentExtensions)){
                $mimes = implode(',', $allowedDocumentExtensions);
                $rules = [
                    'file' => 'required|mimes:'.$mimes.'|max:'.$sizeLimit
                ];
            }elseif (in_array($extension, $allowedTextExtensions)) {
                $mimes = implode(',', $allowedTextExtensions);
                $rules = [
                    'file' => 'required|mimes:'.$mimes.'|max:'.$sizeLimit
                ];
            } elseif (in_array($extension, $allowedOtherExtensions)) {
                $mimes = implode(',', $allowedOtherExtensions);
                $rules = [
                    'file' => 'required|mimes:'.$mimes.'|max:'.$sizeLimit
                ];
            } else {
                $mimes = '';
                if(count($allowedImageExtensions)){
                    $mimes .= implode(',', $allowedImageExtensions); 
                    $mimes = $this->trimChar(',',$mimes);       
                }
                
                if(count($allowedDocumentExtensions)){
                    $mimes .= ',';
                    $mimes .= implode(',', $allowedDocumentExtensions);  
                    $mimes = $this->trimChar(',',$mimes);      
                }

                if(count($allowedTextExtensions)){
                    $mimes .= ',';
                    $mimes .= implode(',', $allowedTextExtensions);    
                    $mimes = $this->trimChar(',',$mimes);    
                }

                if(count($allowedOtherExtensions)){
                    $mimes .= ',';
                    $mimes .= implode(',', $allowedOtherExtensions); 
                    $mimes = $this->trimChar(',',$mimes);       
                }

                $rules = [
                    'file' => 'required|mimes:'.$mimes.'|max:'.$sizeLimit
                ];
            }
        }

        $validator = $this->validate($file, $rules, $messages);
        return $validator;
    }

    public function resize(int $width = null, int $height = null, UploadedFile $file, bool $constraint_aspect_ratio = false)
    {
        if(! $this->validateImage($file)){
            throw new InvalidArgumentException('Invalid image file provided.');
        }

        if($constraint_aspect_ratio){
            $resized_image = Image::make($file)->fit($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
        }else{
            $resized_image = Image::make($file)->resize($width, $height);
        }

        $resized_image = $this->convertToUploadedFile($resized_image);

        return $resized_image;
    }

    private function validateImage($file)
    {
        if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'])) {
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

    private function checkRulesAndMessagesCount($rules, $messages)
    {
        return count($rules) === count($messages);
    }

    private function validate($file, $rules,$messages)
    {
        $validator = Validator::make(['file' => $file], $rules, $messages);
        return $validator;
    }

    private function trimChar($char, $string)
    {
        $string = ltrim($string, $char);
        $string = rtrim($string, $char);
        return $string;
    }
}