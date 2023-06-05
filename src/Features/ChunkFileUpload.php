<?php
namespace SarCubet\FileUpload\Features;

use Illuminate\Http\Request;
use SarCubet\FileUpload\Upload;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

class ChunkFileUpload extends Upload
{
    private $chunkSize;
    private $totalChunkCount;
    private $chunkFileName;
    private $chunkFileUploadCompleteStatus;
    private $lastUploadedChunkIndex;
    private $uploadProgressInPercentage;
    private $chunkFileExtension;

    public function __construct()
    {
        $this->chunkSize = null;
        $this->totalChunkCount = null;
        $this->chunkFileName = '';
        $this->chunkFileUploadCompleteStatus = false;
        $this->lastUploadedChunkIndex = null;
        $this->uploadProgressInPercentage = 0;
        $this->chunkFileExtension = null;
    }

    public function receiveChunks(Request $request) : self
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

    public function isUploadComplete() : bool
    {
        return $this->chunkFileUploadCompleteStatus;
    }

    public function getFile() : UploadedFile
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

    public function getLastUploadedChunkIndex() : ?int
    {
        return $this->lastUploadedChunkIndex;
    }
    
    public function getUploadProgressInPercentage() : int
    {
        return $this->uploadProgressInPercentage;
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

    private function checkChunkSetupStatus()
    {
        return $this->chunkSize !== null && $this->totalChunkCount !== null && $this->chunkFileName !== null;
    }

    private function calculateUploadProgress()
    {
        $this->uploadProgressInPercentage = (($this->lastUploadedChunkIndex + 1) / $this->totalChunkCount)  * 100;
    }
}