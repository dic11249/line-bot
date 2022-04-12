<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WeatherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'city' => $this['city'],
            'time' => $this['time'],
            'Wx' => $this['Wx'],
            'url' => $this['url'],
            'PoP' => $this['PoP'],
            'Tem' => $this['Tem']
        ];
    }
}
