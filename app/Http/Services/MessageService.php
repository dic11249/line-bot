<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;

class MessageService
{

    public function weatherTemplate(array $weather)
    {
        $postBackAction = new PostbackTemplateActionBuilder(
            '詳細資料',
            '天氣 '. $weather['縣市'],
            'https://www.cwb.gov.tw/V8/C/',
        );

        $carouselColumn = new CarouselColumnTemplateBuilder(
            $weather['縣市'],
            ('天氣狀況 '.$weather['天氣現象'])  . "\n" . ('溫度 '.$weather['氣溫']) . "\n" . ('降雨機率 '.$weather['降雨機率']),
            'https://picsum.photos/400/300',
            [$postBackAction]
        );

        $carouselTemplateBuilder = new CarouselTemplateBuilder([
            $carouselColumn
        ]);

        $templateMessageBuilder = new TemplateMessageBuilder(
            $weather['縣市'].' 天氣預報。',
            $carouselTemplateBuilder
        );

        return $templateMessageBuilder;
    }

    public function getWeatherImg(string $weather)
    {

    }

}
