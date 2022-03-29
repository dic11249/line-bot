<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class WeatherService
{

    public function getWeather(string $city, int $time = 0)
    {
        $response = Http::get(env('OPEN_DATA_BASE_URL') . '/v1/rest/datastore/F-C0032-001', [
            'Authorization' => env('API_TOKEN'),
            'format' => 'JSON',
            'locationName' => $city
        ]);
        // Wx 天氣現象, MaxT 最高溫度, MinT 最低溫度, CI 舒適度, PoP 降雨機率
        $data = Arr::get($response->json(), 'records.location.0.weatherElement');

        $cityName = Arr::get($response->json(), 'records.location.0.locationName');
        $startTime = Arr::get($data, '0.time.' . $time . '.startTime');
        $endTime = Arr::get($data, '0.time.' . $time . '.endTime');
        $Wx = Arr::get($data, '0.time.' . $time . '.parameter.parameterName');
        $PoP = Arr::get($data, '1.time.' . $time . '.parameter.parameterName');
        $MinT = Arr::get($data, '2.time.' . $time . '.parameter.parameterName');
        $CI = Arr::get($data, '3.time.' . $time . '.parameter.parameterName');
        $MaxT = Arr::get($data, '4.time.' . $time . '.parameter.parameterName');

        $result = [
            '時間: ' . $startTime . ' ~ ' . $endTime,
            '縣市: ' . $cityName,
            '天氣現象: ' . $Wx,
            '降雨機率: ' . $PoP . ' 百分比',
            '最高氣溫: ' . $MaxT . ' 度',
            '最低氣溫: ' . $MinT . ' 度',
            '舒適度: '   . $CI
        ];

        return $result;
    }

    public function getCityWeather(string $city, int $time = 0)
    {
        $response = Http::get(env('OPEN_DATA_BASE_URL') . '/v1/rest/datastore/F-C0032-001', [
            'Authorization' => env('API_TOKEN'),
            'format' => 'JSON',
            'locationName' => $city
        ]);
        // Wx 天氣現象, MaxT 最高溫度, MinT 最低溫度, CI 舒適度, PoP 降雨機率
        $data = Arr::get($response->json(), 'records.location.0.weatherElement');

        $cityName = Arr::get($response->json(), 'records.location.0.locationName');
        $startTime = Arr::get($data, '0.time.' . $time . '.startTime');
        $endTime = Arr::get($data, '0.time.' . $time . '.endTime');
        $Wx = Arr::get($data, '0.time.' . $time . '.parameter.parameterName');
        $PoP = Arr::get($data, '1.time.' . $time . '.parameter.parameterName');
        $MinT = Arr::get($data, '2.time.' . $time . '.parameter.parameterName');
        $CI = Arr::get($data, '3.time.' . $time . '.parameter.parameterName');
        $MaxT = Arr::get($data, '4.time.' . $time . '.parameter.parameterName');

        $result = [
            '縣市' => $cityName,
            '天氣現象' => $Wx,
            '降雨機率' => $PoP . '%',
            '氣溫' => $MinT . ' ~ ' . $MaxT . '℃',
            '舒適度'   => $CI
        ];

        return $result;
    }
}
