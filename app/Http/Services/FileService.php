<?php

namespace App\Http\Services;

use App\Contracts\File;
use LINE\LINEBot\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use GreatTree\Base\Services\FileService as GtFileService;

class FileService extends GtFileService implements File
{
    public function saveFile(Response $response, $fileName, $model, $attribute, $option = [])
    {
        $result  = true;
        $fileData = [
            'size' => $response->getHeaders()['content-length'],
            'name' => $fileName . '.' . Str::after($response->getHeaders()['content-type'], '/'),
            'mime_type' => $response->getHeaders()['content-type'],
            'extension' => Str::after($response->getHeaders()['content-type'], '/'),
        ];
        $fileData['basename'] = $fileData['name'];
        $fileData['name'] = pathinfo($fileData['name'], \PATHINFO_FILENAME);
        $options = [];
        $disk = $disk ?? $this->disk;

        $fileData['created_user'] = 0;

        $fileData['path'] = $this->getPath($model, $fileData['basename']);

        $fullPath = $fileData['path'] . '/' . $fileData['basename'];
        try {
            Log::info($fileData['path']);
            $fileResult = Storage::disk($disk)->put($fileData['path'] . '/' . $fileData['basename'], $response->getRawBody(), $options);
            $result = $this->fileRepository->create($fileData);
            if ($result) {
                $fileModel = $this->fileRepository->getModel();
                $fileModel->toTarget($model)->attach($model->id, ['item_column' => $attribute]);
                $result = $fileModel;
            } else {
                $this->destroyFile($fullPath, $disk);
            }
            Log::info('save');
        } catch (\Exception $e) {
            //確保DB 寫入失敗 不會上傳檔案，變成檔案孤兒
            $this->destroyFile($fullPath, $disk);
            throw $e;
            Log::info('delete');
        }
        Log::info('finish');
        return $result;
    }
}
