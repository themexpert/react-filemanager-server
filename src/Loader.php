<?php

namespace FileManager;

class Loader
{
    /**
     * generates and sends thumb for an image
     *
     * @param $path
     *
     * @return bool
     */
    public static function thumb($path)
    {
        self::validate($path);

        $thumb = self::genThumb($path);

        return Response::RAW(\mime_content_type($thumb), file_get_contents($thumb));
    }

    /**
     * Sends related icon to the file type
     *
     * @param $type
     *
     * @return bool
     */
    public static function icon($type)
    {
        $path = __DIR__.'/images/'.$type.'.png';
        if ( ! file_exists($path)) {
            $path = __DIR__.'/images/file.png';
        }

        return Response::RAW(\mime_content_type($path), file_get_contents($path));
    }

    /**
     * Sends raw file with mime-type
     *
     * @param $path
     *
     * @return bool
     */
    public static function raw($path)
    {
        self::validate($path);
        $content_type = \mime_content_type($path);
        if ($content_type === 'image/svg') {
            $content_type .= '+xml';
        }

        return Response::RAW($content_type, file_get_contents($path));
    }

    /**
     * Validates the request
     *
     * @param $path
     */
    private static function validate(&$path)
    {
        $path = Utils::cleanDir(FileManager::$ROOT.$path);
        Utils::secureDir($path);

        if ( ! file_exists($path)) {
            http_response_code(404);
            die;
        }
    }

    /**
     * Generates thumb
     *
     * @param $path
     *
     * @return string
     */
    private static function genThumb($path)
    {
        if (basename(dirname($path)) == '_thumbs') {
            return $path;
        }

        $thumb_dir = dirname($path).'/_thumbs/';
        if ( ! file_exists($thumb_dir)) {
            mkdir($thumb_dir);
        }
        $fileInfo   = pathinfo($path);
        $thumb_file = $thumb_dir.$fileInfo['basename'];
        if (file_exists($thumb_dir.$fileInfo['basename'])) {
            return $thumb_file;
        }

        //thumb does not exist
        $ext = strtolower($fileInfo['extension']);

        if ( ! in_array($ext, ['gif', 'jpg', 'png', 'jpeg', 'webp'])) {
            http_response_code(403);
            die;
        }

        if ($ext == 'gif') {
            $resource = imagecreatefromgif($path);
        } elseif ($ext == 'png') {
            $resource = imagecreatefrompng($path);
        } elseif ($ext == 'jpg' || $ext == 'jpeg') {
            $resource = imagecreatefromjpeg($path);
        } elseif ($ext == 'webp') {
            $resource = imagecreatefromwebp($path);
        }
        $width          = imagesx($resource);
        $height         = imagesy($resource);
        $desired_height = 100;
        $desired_width  = floor($width * ($desired_height / $height));
        $virtual_image  = imagecreatetruecolor($desired_width, $desired_height);
        imagesavealpha($virtual_image, true);
        $trans_colour = imagecolorallocatealpha($virtual_image, 0, 0, 0, 127);
        imagefill($virtual_image, 0, 0, $trans_colour);
        imagecopyresized($virtual_image, $resource, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
        imagepng($virtual_image, $thumb_file, 1);

        return $thumb_file;
    }
}