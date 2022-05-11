<?php

namespace App\Contracts;

use LINE\LINEBot\Response;
use GreatTree\Base\Models\Entity\Storage\File as StorageFile;

interface File
{
    public function saveFile(Response $response, $fileName, $model, $attribute, $option = []);
}
