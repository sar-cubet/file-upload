<?php
namespace SarCubet\FileUpload\Features;

use SarCubet\FileUpload\Upload;
use Illuminate\Http\UploadedFile;

class VirusScan extends Upload
{
    private $isFileInfected;
    private $malwareName;

    public function __construct()
    {
        $this->isFileInfected = false;
        $this->malwareName = '';
    }

    public function scanFile(UploadedFile $file) : self
    {
        $command = "clamscan --infected {$file->getPathname()}";
        $output = shell_exec($command);
        
        $infected = false;
        $malwareName = '';

        if (preg_match('/^(.*): (.*) FOUND$/m', $output, $matches)) {
            $infected = true;
            $malwareName = $matches[2];
        }

        $this->isFileInfected = $infected;
        $this->malwareName = $malwareName;
        
        return $this;
    }

    public function isFileInfected() : bool
    {
        return $this->isFileInfected;
    }

    public function getMalwareName() : string
    {
        return $this->malwareName;
    }
}