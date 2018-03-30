<?php

namespace FileManager\Plugins\FontAwesome;

use FileManager\Response;

class FontAwesome
{
    public function icons()
    {
        return Response::RAW('application/json', file_get_contents(__DIR__ . '/icons.json'));
    }
}