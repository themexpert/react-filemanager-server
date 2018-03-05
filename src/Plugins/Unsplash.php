<?php

namespace FileManager\Plugins;

use FileManager\FileManager;
use FileManager\Response;

class Unsplash extends Plugin {

    public function unsplash()
    {
        return Response::JSON(['message' =>' Nothing here']);
    }

    public static function methods()
    {
        return [
            'unsplash' => [
                'component' => 'Unsplash'
            ]
        ];
    }

    public static function tabs()
    {
        return ['unsplash' => 'Unsplash'];
    }

}