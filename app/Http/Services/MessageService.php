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

    public function weatherTemplate(array $data)
    {
        $postBackAction = new PostbackTemplateActionBuilder(
            '詳細資料',
            '天氣 '. $data[0]['city'],
            'https://www.cwb.gov.tw/V8/C/',
        );

        $carouselColumns = collect($data)->map(function($weather) use ($postBackAction) {
            $carouselColumn = new CarouselColumnTemplateBuilder(
                ($weather['time'].' '.$weather['city']),
                ('天氣狀況 '.$weather['Wx'])  . "\n" . ('溫度 '.$weather['Tem']) . "\n" . ('降雨機率 '.$weather['PoP']),
                'https://picsum.photos/400/300',
                [$postBackAction]
            );
            return $carouselColumn;
        })->all();

        $carouselTemplateBuilder = new CarouselTemplateBuilder($carouselColumns);

        $templateMessageBuilder = new TemplateMessageBuilder(
            $data[0]['city'].' 天氣預報。',
            $carouselTemplateBuilder
        );

        return $templateMessageBuilder;
    }
}
