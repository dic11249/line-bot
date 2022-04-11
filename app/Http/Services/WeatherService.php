<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class WeatherService
{

    private $cityArr = [
        '臺北市', '新北市', '桃園市', '臺中市', '臺南市', '高雄市',
        '新竹縣', '彰化縣', '南投縣', '雲林縣', '嘉義縣', '屏東縣', '宜蘭縣', '花蓮縣', '臺東縣', '澎湖縣', '金門縣', '連江縣',
        '基隆市', '新竹市', '嘉義市'
    ];

    public function getWeather(string $city, int $time = 0)
    {
        $city = Str::replace('台', '臺', $city);

        if (!in_array($city, $this->cityArr)) {
            return $city;
        }

        $response = Http::get(env('OPEN_DATA_BASE_URL') . '/v1/rest/datastore/F-C0032-001', [
            'Authorization' => env('API_TOKEN'),
            'format' => 'JSON',
            'locationName' => $city
        ]);
        // Wx 天氣現象, MaxT 最高溫度, MinT 最低溫度, CI 舒適度, PoP 降雨機率
        $originData = $response->json();

        $resultData = Arr::get($originData, 'records');
        $city = Arr::get($resultData, 'location.0.locationName');
        $Wx = Arr::get($resultData, 'location.0.weatherElement.0.time');
        $PoP = Arr::get($resultData, 'location.0.weatherElement.1.time');
        $MinT = Arr::get($resultData, 'location.0.weatherElement.2.time');
        $CT = Arr::get($resultData, 'location.0.weatherElement.3.time');
        $MaxT = Arr::get($resultData, 'location.0.weatherElement.4.time');

        for ($i = 0; $i <= 2; $i++) {
            $data[$i] = [
                'city' => $city,
                'time' => $Wx[$i]['startTime'],
                'Wx' => $Wx[$i]['parameter']['parameterName'],
                'WxImg' => $Wx[$i]['parameter']['parameterValue'],
                'url' => 'https://www.cwb.gov.tw/V8/assets/img/weather_icons/weathers/svg_icon/day/' . str_pad($Wx[$i]['parameter']['parameterValue'], 2, '0', STR_PAD_LEFT) . '.svg',
                'PoP' => $PoP[$i]['parameter']['parameterName'] . '%',
                'Tem' => $MinT[$i]['parameter']['parameterName'] . '℃-' . $MaxT[$i]['parameter']['parameterName'] . '℃'
            ];
        }

        return $data;
    }
}
