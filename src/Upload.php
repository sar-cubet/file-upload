<?php

namespace SarCubet\FileUpload;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use InvalidArgumentException;
use RuntimeException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class Upload
{
    private $chunkSize;
    private $totalChunkCount;
    private $chunkFileName;
    private $chunkFileUploadCompleteStatus;
    private $lastUploadedChunkIndex;
    private $uploadProgressInPercentage;
    private $chunkFileExtension;
    private $isVirusInfected;
    private $malwareName;

    public function __construct()
    {
        $this->chunkSize = null;
        $this->totalChunkCount = null;
        $this->chunkFileName = '';
        $this->chunkFileUploadCompleteStatus = false;
        $this->lastUploadedChunkIndex = null;
        $this->uploadProgressInPercentage = 0;
        $this->chunkFileExtension = null;
        $this->isVirusInfected = false;
        $this->malwareName = '';
    }

    public function optimizeImage(UploadedFile $uploadedImage, string $qualityVal)
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

    public function store(UploadedFile $file, string $disk, string $path = '')
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

    public function validateFile(UploadedFile $file, array $rulesAndMessagesArray = [])
    {
        $rulesArray = array_keys($rulesAndMessagesArray);
        $messagesArray = array_values($rulesAndMessagesArray);

        $rules = [];
        $messages = [];

        if (count($rulesArray)) {
            if (count($messagesArray)) {
                if (!$this->checkRulesAndMessagesCount($rulesArray, $messagesArray)) {
                    throw new InvalidArgumentException("Number of rules doesn't match number of messages.");
                }
                foreach ($rulesArray as $key => $rule) {
                    if (strpos($rule, ':') !== false) {
                        $rule = explode(':', $rule)[0];
                    }
                    $messages['file.' . $rule] = $messagesArray[$key];
                }
            }

            $rules = [
                'file' => implode('|', $rulesArray)
            ];
        } else {
            $allowedImageExtensions = config('fileUpload.allowed_file_extensions.image');
            $allowedDocumentExtensions = config('fileUpload.allowed_file_extensions.doc');
            $allowedTextExtensions = config('fileUpload.allowed_file_extensions.text');
            $allowedOtherExtensions = config('fileUpload.allowed_file_extensions.others');
            $sizeLimit = config('fileUpload.default_file_size_limit');
            $extension = $file->getClientOriginalExtension();

            if (in_array($extension, $allowedImageExtensions)) {
                $mimes = implode(',', $allowedImageExtensions);
                $rules = [
                    'file' => 'required|image|mimes:' . $mimes . '|max:' . $sizeLimit,
                ];
            } elseif (in_array($extension, $allowedDocumentExtensions)) {
                $mimes = implode(',', $allowedDocumentExtensions);
                $rules = [
                    'file' => 'required|mimes:' . $mimes . '|max:' . $sizeLimit,
                ];
            } elseif (in_array($extension, $allowedTextExtensions)) {
                $mimes = implode(',', $allowedTextExtensions);
                $rules = [
                    'file' => 'required|mimes:' . $mimes . '|max:' . $sizeLimit,
                ];
            } elseif (in_array($extension, $allowedOtherExtensions)) {
                $mimes = implode(',', $allowedOtherExtensions);
                $rules = [
                    'file' => 'required|mimes:' . $mimes . '|max:' . $sizeLimit,
                ];
            } else {
                $mimes = '';
                if (count($allowedImageExtensions)) {
                    $mimes .= implode(',', $allowedImageExtensions);
                    $mimes = $this->trimChar(',', $mimes);
                }

                if (count($allowedDocumentExtensions)) {
                    $mimes .= ',';
                    $mimes .= implode(',', $allowedDocumentExtensions);
                    $mimes = $this->trimChar(',', $mimes);
                }

                if (count($allowedTextExtensions)) {
                    $mimes .= ',';
                    $mimes .= implode(',', $allowedTextExtensions);
                    $mimes = $this->trimChar(',', $mimes);
                }

                if (count($allowedOtherExtensions)) {
                    $mimes .= ',';
                    $mimes .= implode(',', $allowedOtherExtensions);
                    $mimes = $this->trimChar(',', $mimes);
                }

                $rules = [
                    'file' => 'required|mimes:' . $mimes . '|max:' . $sizeLimit,
                ];
            }
        }

        $validator = $this->validate($file, $rules, $messages);
        return $validator;
    }

    public function resize(int $width = null, int $height = null, UploadedFile $file, bool $constraint_aspect_ratio = false)
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

    public function receiveChunks(Request $request)
    {
        if($request->has('metadata')){
            $this->setChunkMetadata($request->metadata);
            $this->setChunkFileName();
        }else{
            
            if(! $this->checkChunkSetupStatus()){
                throw new RuntimeException('Chunk setup not configured.');
            }
            
            $destinationPath = config('fileUpload.chunk_file_upload_path');

            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            
            $fileName = $this->chunkFileName;
            $chunkNumber = $request->chunk_index;
            $file = $request->file('chunk');

            $chunkFileName = $fileName . '_' . $chunkNumber;
            $filePath = $destinationPath .'/'. $chunkFileName;
            // rename() is used to move an already stored file into another location
            // This function is used for testing purpose. May need to change in the case of Http uploaded files 
            $moved = rename($file->getPathname(), $filePath);

            if($moved){
                $this->lastUploadedChunkIndex = $chunkNumber;
                $this->calculateUploadProgress();
                
                if(($chunkNumber + 1) == $this->totalChunkCount){
                    $this->combineChunks($destinationPath);
                }
            }
        }
        return $this;
    }

    public function isUploadComplete()
    {
        return $this->chunkFileUploadCompleteStatus;
    }

    public function getFile()
    {
        if($this->chunkFileUploadCompleteStatus){
            $filePath = config('fileUpload.chunk_file_upload_path') . '/combined/' . $this->chunkFileName . '.' . $this->chunkFileExtension;
            if (file_exists($filePath)) {
                return new UploadedFile($filePath, $this->chunkFileName);
            } else {
                throw new RuntimeException('File not found.');
            }
        }else{
            throw new RuntimeException('Call to function getFile() before chunk upload completion.');
        }
    }

    public function getLastUploadedChunkIndex()
    {
        return $this->lastUploadedChunkIndex;
    }
    
    public function getUploadProgressInPercentage()
    {
        return $this->uploadProgressInPercentage;
    }

    public function scanFile(UploadedFile $file)
    {
        $command = "clamscan --infected {$file->getPathname()}";
        $output = shell_exec($command);
        
        $infected = false;
        $malwareName = '';

        if (preg_match('/^(.*): (.*) FOUND$/m', $output, $matches)) {
            $infected = true;
            $malwareName = $matches[2];
        }

        $this->isVirusInfected = $infected;
        $this->malwareName = $malwareName;
        
        return $this;
    }

    public function isFileInfected()
    {
        return $this->isVirusInfected;
    }

    public function getMalwareName()
    {
        return $this->malwareName;
    }

    // ----------------- PRIVATE FUNCTIONS -------------------- 

    private function calculateUploadProgress()
    {
        $this->uploadProgressInPercentage = (($this->lastUploadedChunkIndex + 1) / $this->totalChunkCount)  * 100;
    }

    private function checkChunkSetupStatus()
    {
        return $this->chunkSize !== null && $this->totalChunkCount !== null && $this->chunkFileName !== null;
    }

    private function combineChunks($destinationPath)
    {
        $combinedDirectory = $destinationPath . '/combined';
        if (!is_dir($combinedDirectory)) {
            mkdir($combinedDirectory, 0777, true);
        }

        $combinedFile = fopen($destinationPath . '/combined/' . $this->chunkFileName . '.' . $this->chunkFileExtension, 'ab');

        for ($i = 0; $i <= $this->totalChunkCount - 1; $i++) {
            $chunkFileName = $this->chunkFileName . '_' . $i;
            $chunkPath = $destinationPath . '/' . $chunkFileName;

            $chunk = fopen($chunkPath, 'rb');
            stream_copy_to_stream($chunk, $combinedFile);
            fclose($chunk);

            unlink($chunkPath);
        }

        fclose($combinedFile);
        $this->chunkFileUploadCompleteStatus = true;
    }

    private function setChunkMetadata(Array $metadata)
    {
        if (isset($metadata['chunk_size']) && isset($metadata['total_chunk_count']) && isset($metadata['file_extension']) && $metadata['chunk_size'] && $metadata['total_chunk_count'] && $metadata['file_extension']) {
            $this->chunkSize = $metadata['chunk_size'];
            $this->totalChunkCount = $metadata['total_chunk_count'];
            $this->chunkFileExtension = $metadata['file_extension'];
        }else{
            throw new InvalidArgumentException("Invalid metadata provided.");
        }
    }

    private function setChunkFileName()
    {
        $this->chunkFileName = uniqid() . time();
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

    private function validateDisk($disk)
    {
        $validDisks = array_keys(config('filesystems.disks'));

        if (!in_array($disk, $validDisks)) {
            return false;
        } else {
            return true;
        }
    }

    // To convert image of type "Intervention\\Image\\Image" or any type to "Illuminate\\Http\\UploadedFile"
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

    private function checkRulesAndMessagesCount($rules, $messages)
    {
        return count($rules) === count($messages);
    }

    private function validate($file, $rules, $messages)
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