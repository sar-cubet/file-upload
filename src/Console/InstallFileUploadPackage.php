<?php
namespace SarCubet\FileUpload\Console;

use Illuminate\Console\Command;

class InstallFileUploadPackage extends Command
{
    protected $signature = 'fileupload:install';
    protected $description = 'Install the File Upload Package';

    public function handle()
    {
        $this->info('Installing File Upload Package...');
        $this->info('Publishing resources...');

        $this->publishConfiguration();
        $this->info('Resources published');
    }

    private function publishConfiguration()
    {
        $params = [
            '--provider' => "SarCubet\FileUpload\FileUploadServiceProvider"
        ];
        $this->call('vendor:publish', $params);
    }
}