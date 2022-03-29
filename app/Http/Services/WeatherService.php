<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class WeatherService
{

    public function getWeather(string $city, int $time = 0)
    {
        $city = Str::replace('台','臺',$city);

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
                'PoP' => $PoP[$i]['parameter']['parameterName'] . '%',
                'Tem' => $MinT[$i]['parameter']['parameterName'] . '℃-' . $MaxT[$i]['parameter']['parameterName'] . '℃'
            ];
        }

        return $data;
    }
}
