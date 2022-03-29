<?php

namespace App\Http\Controllers;

use LINE\LINEBot;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Services\MessageService;
use App\Http\Services\WeatherService;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;

class LineBotController extends Controller
{
    protected $weatherService;
    protected $messageService;

    public function __construct(WeatherService $weatherService, MessageService $messageService)
    {
        $this->weatherService = $weatherService;
        $this->messageService = $messageService;
        $this->accessToken = env('LINE_CHANNEL_ACCESS_TOKEN');
        $this->channelSecret = env('LINE_CHANNEL_SECRET');
    }

    public function chat(Request $request)
    {
        $httpClient = new CurlHTTPClient($this->accessToken);
        $bot = new LINEBot($httpClient, ['channelSecret' => $this->channelSecret]);

        // LINE BOT回傳資料
        $events = $request->events;
        Log::info($events[0]);
        // 使用者訊息類別
        $replyType = $events[0]['message']['type'] ?? null;
        Log::info('replyType = ' . $replyType);
        // 使用者訊息
        $text = $events[0]['message']['text'] ?? null;
        Log::info('text = ' . $text);
        // 使用者token
        $replyToken = $events['0']['replyToken'];
        Log::info('replyToken = ' . $replyToken);

        // 判斷訊息類別, 文字回傳相應資料
        switch ($replyType) {
            // 文字訊息
            case 'text':
                // help
                if (Str::contains(Str::lower($text), 'help')) {
                    $bot->replyText($replyToken, '輸入 天氣縣市 可查詢天氣' . "\n" . 'ex: 天氣 屏東縣');
                }

                // 天氣預報
                if (Str::startsWith($text, '天氣') || Str::endsWith($text, '天氣')) {
                    if (Str::startsWith($text, '天氣')) {
                        $city = trim(Str::after($text, '天氣'));
                        $data = $this->weatherService->getWeather($city);
                        $templateMessageBuilder = $this->messageService->weatherTemplate($data);
                        $bot->replyMessage($replyToken, $templateMessageBuilder);
                    } else {
                        $city = trim(Str::before($text, '天氣'));
                        $data = $this->weatherService->getWeather($city);
                        $templateMessageBuilder = $this->messageService->weatherTemplate($data);
                        $bot->replyMessage($replyToken, $templateMessageBuilder);
                    }
                }
            // 預設回聲蟲
            default:
                $bot->replyText($replyToken, $text);
        }
    }

    public function index(Request $request)
    {
        $result = $this->weatherService->getWeather($request->city, $request->time);
        return $result;
    }
}
