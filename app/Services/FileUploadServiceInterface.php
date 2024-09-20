<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

interface FileUploadServiceInterface
{
    public function upload(UploadedFile $file, string $directory, string $storedName): string;

    public function getUrl(string $filePath): string;

    public function processFile(UploadedFile $file, string $directory): array;
}
