<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use GreatTree\Base\Database\Eloquent\Casts\HasOneFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GreatTree\Base\Database\Eloquent\Casts\HasManyFile;

class ImageUploadLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'uuid', 'start_time', 'end_time'];

    protected $casts = [
        'image' => HasManyFile::class,
    ];
}
