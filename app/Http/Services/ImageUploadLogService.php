<?php

namespace App\Http\Services;

use LINE\LINEBot\Response;
use App\Models\ImageUploadLog;
use Illuminate\Support\Facades\Log;
use Libern\QRCodeReader\QRCodeReader;
use Illuminate\Support\Facades\Storage;

class ImageUploadLogService
{
    public function qrCodeUpload($event, Response $response)
    {
        $path =  $event->getUserId() . '/' . 'qrcode/' . $event->getMessageId() . '.png';
        Storage::disk('public')->put($path, $response->getRawBody());

        $qr_reader = new QRCodeReader();
        $qr_text = $qr_reader->decode('storage/'.$path);
        if(!$qr_text) {
            Log::info('非QR Code 刪除檔案');
            Storage::disk('public')->delete($path);
        }
        return $qr_text;
    }

    public function uploadLogCreate($user_id, $qr_text)
    {
        ImageUploadLog::create([
            'user_id' => $user_id,
            'uuid' => $qr_text,
            'start_time' => now(),
            'end_time' => now()->addMinute()
        ]);
    }
}
