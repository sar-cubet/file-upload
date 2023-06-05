<?php

namespace SarCubet\FileUpload;

use Illuminate\Http\Request;
use Illuminate\Validation\Validator as ValidateObj;
use Illuminate\Http\UploadedFile;
use SarCubet\FileUpload\Features\ChunkFileUpload;
use SarCubet\FileUpload\Features\FileStorage;
use SarCubet\FileUpload\Features\FileValidate;
use SarCubet\FileUpload\Features\ImageOptimize;
use SarCubet\FileUpload\Features\ImageResize;
use SarCubet\FileUpload\Features\VirusScan;

class Upload implements UploadInterface
{
    private $fileValidator;
    private $imageOptimizer;
    private $fileStorage;
    private $imageResizer;
    private $chunkFileUploader;
    private $virusScanner;

    public function __construct()
    {
        $this->fileValidator = new FileValidate();
        $this->imageOptimizer = new ImageOptimize();
        $this->fileStorage = new FileStorage();
        $this->imageResizer = new ImageResize();
        $this->chunkFileUploader = new ChunkFileUpload();
        $this->virusScanner = new VirusScan();
    }

    public function validateFile(UploadedFile $file, array $rulesAndMessagesArray = []) : ValidateObj
    {
        return $this->fileValidator->validateFile($file, $rulesAndMessagesArray);
    }

    public function optimizeImage(UploadedFile $uploadedImage, string $qualityVal) : UploadedFile
    {
        return $this->imageOptimizer->optimizeImage($uploadedImage, $qualityVal);
    }

    public function store(UploadedFile $file, string $disk, string $path = '') : string
    {
        return $this->fileStorage->store($file, $disk, $path);
    }
    
    public function resize(int $width = null, int $height = null, UploadedFile $file, bool $constraint_aspect_ratio = false) : UploadedFile
    {
        return $this->imageResizer->resize($width, $height, $file, $constraint_aspect_ratio);
    }

    public function receiveChunks(Request $request) : ChunkFileUpload
    {
        return $this->chunkFileUploader->receiveChunks($request);
    }

    public function isUploadComplete() : bool
    {
        return $this->chunkFileUploader->isUploadComplete();
    }
    
    public function getFile() : UploadedFile
    {
        return $this->chunkFileUploader->getFile();
    }

    public function getLastUploadedChunkIndex() : ?int
    {
        return $this->chunkFileUploader->getLastUploadedChunkIndex();
    }

    public function getUploadProgressInPercentage() : int
    {
        return $this->chunkFileUploader->getUploadProgressInPercentage();
    }

    public function scanFile(UploadedFile $file) : VirusScan
    {
        return $this->virusScanner->scanFile($file);
    }
 
    public function isFileInfected() : bool
    {
        return $this->virusScanner->isFileInfected();
    }

    public function getMalwareName() : string
    {
        return $this->virusScanner->getMalwareName();
    }
}