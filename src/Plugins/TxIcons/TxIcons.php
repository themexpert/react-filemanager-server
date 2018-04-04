<?php

namespace FileManager\Plugins\TxIcons;

use FileManager\Response;

class TxIcons
{
    public function icons()
    {
        return Response::RAW('application/json', file_get_contents(__DIR__ . '/icons.json'));
    }
}