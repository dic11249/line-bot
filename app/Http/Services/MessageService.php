<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;

class MessageService
{
    public function weatherTemplate(array $data)
    {
        $cityName = $data[0]['city'];
        $postBackAction = new PostbackTemplateActionBuilder(
            '詳細資料',
            '天氣 ' . $cityName,
            'https://www.cwb.gov.tw/V8/C/',
        );

        $carouselColumns = collect($data)->map(function ($weather) use ($postBackAction) {
            $carouselColumn = new CarouselColumnTemplateBuilder(
                ($weather['time'] . ' ' . $weather['city']),
                ('天氣狀況 ' . $weather['Wx'])  . "\n" . ('溫度 ' . $weather['Tem']) . "\n" . ('降雨機率 ' . $weather['PoP']),
                $this->getWeatherImg($weather['time'], $weather['WxImg']),
                [$postBackAction]
            );
            return $carouselColumn;
        })->all();

        $carouselTemplateBuilder = new CarouselTemplateBuilder($carouselColumns);

        $templateMessageBuilder = new TemplateMessageBuilder(
            $cityName . ' 天氣預報。',
            $carouselTemplateBuilder
        );

        return $templateMessageBuilder;
    }

    public function getWeatherImg($time, $weatherValue)
    {
        if (Str::endsWith($time, '18:00:00')) {
            $url = 'https://www.cwb.gov.tw/V8/assets/img/weather_icons/weathers/svg_icon/night/' . str_pad($weatherValue, 2, '0', STR_PAD_LEFT) . '.svg';
        } else {
            $url = 'https://www.cwb.gov.tw/V8/assets/img/weather_icons/weathers/svg_icon/day/' . str_pad($weatherValue, 2, '0', STR_PAD_LEFT) . '.svg';
        }
        return $url;
    }
}
