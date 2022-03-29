<?php

namespace App\Http\Services;

use App\Models\ShortUrl;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;

class ShortUrlService
{
    public function saveShortUrl($url)
    {
        $shortUrl = ShortUrl::where('origin_url', $url)->first();
        if (!$shortUrl) {
            $shortUrl = ShortUrl::create(['origin_url' => $url]);
        }
        return $shortUrl;
    }

    public function getShortUrl($id)
    {
        $shortUrl = ShortUrl::find($id);
        return env('APP_URL').'/api/re/'.Hashids::encode($shortUrl->id);
    }
}
