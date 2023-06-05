<?php
namespace SarCubet\FileUpload;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator as ValidateObj;
use SarCubet\FileUpload\Features\ChunkFileUpload;
use SarCubet\FileUpload\Features\VirusScan;

Interface UploadInterface
{
    public function validateFile(UploadedFile $file, array $rulesAndMessagesArray = []) : ValidateObj; 
    public function optimizeImage(UploadedFile $uploadedImage, string $qualityVal) : UploadedFile;
    public function store(UploadedFile $file, string $disk, string $path = '') : string;
    public function resize(int $width = null, int $height = null, UploadedFile $file, bool $constraint_aspect_ratio = false) : UploadedFile;
    public function receiveChunks(Request $request) : ChunkFileUpload;
    public function isUploadComplete() : bool;
    public function getFile() : UploadedFile;
    public function getLastUploadedChunkIndex() : ?int;
    public function getUploadProgressInPercentage() : int;
    public function scanFile(UploadedFile $file) : VirusScan;
    public function isFileInfected() : bool;
    public function getMalwareName() : string;
}