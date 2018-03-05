<?php

namespace FileManager\Plugins;

use FileManager\FileManager;
use FileManager\Response;

class Pixabay extends Plugin {

    public function pixabay()
    {
        return Response::JSON(['message' =>' Nothing here']);
    }

    public static function methods()
    {
        return [
            'pixabay' => [
                'component' => 'Pixabay'
            ]
        ];
    }

    public static function tabs()
    {
        return ['pixabay' => 'Pixabay'];
    }

}