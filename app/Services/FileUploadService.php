<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService implements FileUploadServiceInterface
{
    /**
     * Store the uploaded file in the specified directory with the given name.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string $storedName
     * @return string
     */
    public function upload(UploadedFile $file, string $directory, string $storedName): string
    {
        return $file->storeAs($directory, $storedName, 'public');
    }

    /**
     * Get the URL for the stored file path.
     *
     * @param string $filePath
     * @return string
     */
    public function getUrl(string $filePath): string
    {
        return Storage::url($filePath);
    }

    /**
     * Process the uploaded file by storing it and generating its URL.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return array
     */
    public function processFile(UploadedFile $file, string $directory): array
    {
        $hash = Str::uuid();

        $extension = $file->getClientOriginalExtension();
        $storedName = $hash . '.' . $extension;
        $filePath = $this->upload($file, $directory, $storedName);

        return [
            'filePath' => $filePath,
            'fileUrl' => $this->getUrl($filePath),
        ];
    }

    /**
     * Update the image associated with the given model.
     *
     * @param $model
     * @param $newImage
     * @param string $directory
     * @return mixed
     */
    public function updateImage($model, $newImage, string $directory): mixed
    {
        if ($model->photo_url) {
            $oldImagePath = str_replace('/storage/', 'public/', $model->photo_url);
            Storage::delete($oldImagePath);
        }

        $fileData = $this->processFile($newImage, $directory);
        $model->photo_url = $fileData['fileUrl'];
        $model->save();

        return $model;
    }

    /**
     * Delete the image associated with the given model.
     *
     * @param $model
     * @return mixed
     */
    public function deleteImage($model): mixed
    {
        $imagePath = str_replace('/storage/', 'public/', $model->photo_url);
        $directoryPath = dirname($imagePath);
        Storage::deleteDirectory($directoryPath);

        $model->photo_url = null;
        $model->save();

        return $model;
    }

}
