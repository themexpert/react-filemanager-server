<?php

namespace FileManager\Plugins;

use FileManager\FileManager;
use FileManager\Request;
use FileManager\Response;
use FileManager\Utils;

class General extends Plugin
{
    private $request;
    private $working_dir;
    private $payload;
    private $path;

    /**
     * General constructor.
     */
    public function __construct()
    {
        $this->request     = Request::getInstance();
        $this->working_dir = $this->request->getWorkingDir();
        $this->payload     = $this->request->getPayload();
        $this->path        = FileManager::$ROOT;
        Utils::secureDir($this->path);
    }

    /**
     * Send the list of items in current folder
     *
     * @return mixed
     */
    public function fetch_list()
    {
        $list = glob($this->path.'*');

        $currentPage = $this->request->get('page');
        $perPage     = $this->request->get('per_page');
        if ( ! $perPage) {
            $perPage = 30;
        }
        $resultSet = array_chunk($list, $perPage);

        if ( ! $currentPage) {
            $currentPage = 1;
        }
        if (count($resultSet) >= $currentPage) {
            $results = $resultSet[intval($currentPage) - 1];
        } else {
            $results = [];
        }

        $this->prepareList($results);

        $result = ['total' => count($list), 'items' => $results];

        return Response::JSON($result);
    }

    /**
     * Populates the items data
     *
     * @param $list
     */
    private function prepareList(&$list)
    {
        $list = array_values(array_filter($list, function ($item) {
            return ! in_array(basename($item), ['.', '..']);
        }));
        array_walk($list, function (&$item, $key) {
            $info = pathinfo($item);
            unset($info['dirname']);
            $info['is_dir']                 = is_dir($item);
            $info['last_modification_time'] = $this->fileLastMod($item);
            $info['size']                   = Utils::human_filesize(filesize($item));
            $info['permission']             = $this->filePerms($item);
            $item                           = $info;
        });
    }

    /**
     * Retrieves the file information
     *
     * @return mixed
     */
    public function file_info()
    {
        $file = $this->path.$this->payload['file'];
        Utils::secureDir($file);
        if ( ! file_exists($file)) {
            return Response::JSON(['message' => 'The request file does not exist'], 404);
        }
        $fileInfo                           = pathinfo($file);
        $fileInfo['last_modification_time'] = $this->fileLastMod($file);
        $fileInfo['size']                   = Utils::human_filesize(filesize($file));
        $fileInfo['permission']             = $this->filePerms($file);
        unset($fileInfo['dirname']);

        return Response::JSON($fileInfo);
    }

    /**
     * Retrieves the file permission
     *
     * @param $file
     *
     * @return string
     */
    private function filePerms($file)
    {
        $perms = fileperms($file);

        switch ($perms & 0xF000) {
            case 0xC000: // socket
                $info = 's';
                break;
            case 0xA000: // symbolic link
                $info = 'l';
                break;
            case 0x8000: // regular
                $info = 'r';
                break;
            case 0x6000: // block special
                $info = 'b';
                break;
            case 0x4000: // directory
                $info = 'd';
                break;
            case 0x2000: // character special
                $info = 'c';
                break;
            case 0x1000: // FIFO pipe
                $info = 'p';
                break;
            default: // unknown
                $info = 'u';
        }

// Owner
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ?
            (($perms & 0x0800) ? 's' : 'x') :
            (($perms & 0x0800) ? 'S' : '-'));

// Group
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ?
            (($perms & 0x0400) ? 's' : 'x') :
            (($perms & 0x0400) ? 'S' : '-'));

// World
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ?
            (($perms & 0x0200) ? 't' : 'x') :
            (($perms & 0x0200) ? 'T' : '-'));

        return $info;
    }

    /**
     * Retrieves the last modification time
     *
     * @param $file
     *
     * @return false|string
     */
    private function fileLastMod($file)
    {
        return date("F d Y H:i:s.", filemtime($file));
    }

    /**
     * Creates new directory
     *
     * @return mixed
     */
    public function new_dir()
    {
        $path = $this->path;
        if ( ! is_writable($path)) {
            return Response::JSON(['message' => 'The directory is not writable'], 500);
        }

        $dir  = $this->payload['dir'];
        $path .= '/'.$dir;
        if (file_exists($path)) {
            return Response::JSON(['message' => 'The directory already exists'], 403);
        }

        Utils::secureDir($path);

        if (mkdir($path)) {
            return Response::JSON(['message' => 'The directory has been successfully created']);
        }

        return Response::JSON(['message' => 'Could not complete the operation'], 503);
    }

    /**
     * Creates new file
     *
     * @return mixed
     */
    public function new_file()
    {
        $path = $this->path.$this->payload['filename'];
        if ( ! is_writable($this->path)) {
            return Response::JSON(['message' => 'The path is not writable'], 503);
        }
        if (file_exists($path)) {
            return Response::JSON(['message' => 'File with this name already exists'], 403);
        }
        Utils::secureDir($path);
        $file = fopen($path, 'w+');
        if ($file) {
            fwrite($file, $this->payload['content']);
            fclose($file);

            return Response::JSON(['message' => 'New File Added']);
        }

        return Response::JSON(['message' => 'Failed to add new file'], 503);
    }

    /**
     * Catches uploaded file
     *
     * @return mixed
     */
    public function upload()
    {
        if ( ! $this->request->hasKey('file')) {
            return Response::JSON(['message' => 'Invalid Request'], 403);
        }
        $file = $this->request->file('file');

        $mime = \mime_content_type($file['tmp_name']);
        if ( ! in_array($mime, FileManager::$UPLOAD['allowed_types'])) {
            return Response::JSON(['message' => $mime.' type of files are not allowed to be uploaded'], 403);
        }

        if (move_uploaded_file($file['tmp_name'], $this->getSafeFilename($this->path, $file['name']))) {
            return Response::JSON(['message' => 'File uploaded']);
        }

        return Response::JSON(['message' => 'Could not upload file'], 503);
    }

    /**
     * Get a safe file path to save a newly uploaded file
     *
     * @param $path
     * @param $name
     * @param  null  $i
     *
     * @return string
     */
    private function getSafeFilename($path, $name, $i = null)
    {
        $ext   = pathinfo($name, PATHINFO_EXTENSION);
        $_name = pathinfo($name, PATHINFO_FILENAME);

        $pathname = $path.$_name.($i ? '-'.$i : null).'.'.$ext;

        if (file_exists($pathname)) {
            $i = $i ? $i + 1 : 1;

            return $this->getSafeFilename($path, $name, $i);
        }

        return $pathname;
    }

    /*
     * Download a file
     *
     */
    public function download()
    {

    }


    /**
     * Download a remote file by URL
     *
     * @return mixed
     */
    public function remote_download()
    {
        $image    = $this->payload['url'];
        $filename = crc32($image);
        $filepath = FileManager::$ROOT.$filename;
        if (copy($image, $filepath)) {
            $ext = Utils::get_file_ext(\mime_content_type($filepath));
            rename($filepath, $filepath.'.'.$ext);

            return Response::JSON(['message' => 'File has been downloaded']);
        }

        return Response::JSON(['message' => 'Could not download file'], 503);
    }

    /**
     * Sends all item from current directory
     *
     * @return mixed
     */
    public function scan_dir()
    {
        $query = $this->payload['query'];
        $query = Utils::cleanDir($query);
        $dir   = $this->path.$query.'*';

        Utils::secureDir($dir);

        $all = glob($dir);
        $this->prepareList($all);
        $up  = implode('/', (function () use ($query) {
                $parts = explode('/', $query);
                unset($parts[count($parts) - 1]);

                return $parts;
            })()).'/';
        $all = array_chunk($all, 10);
        if (count($all)) {
            $all = $all[0];
        }
        $all = array_map(function ($item) use ($up) {
            $item['full_path'] = $up.$item['basename'];

            return $item;
        }, $all);

        return Response::JSON($all);
    }

    /**
     * Renames an item
     *
     * @return mixed
     */
    public function rename()
    {
        $oldName = $this->payload['old'];
        $newName = $this->payload['new'];
        if ($oldName === $newName) {
            return Response::JSON(['message' => 'Name is not changed'], 403);
        }
        Utils::secureDir($newName);
        if (strpos($newName, '/') !== false) {
            return Response::JSON(['message' => 'The file name can not contain directory separator'], 403);
        }
        if (rename($this->path.$oldName, $this->path.$newName)) {
            $this->remThumb($this->path.$oldName);
            return Response::JSON(['message' => 'Rename successful']);
        }

        return Response::JSON(['message' => 'Could not rename'], 503);
    }

    /**
     * Copies item or items
     *
     * @return mixed
     */
    public function copy()
    {
        $sources          = $this->payload['sources'];
        $destination      = $this->payload['destination'];
        $full_destination = Utils::cleanDir(FileManager::$JAIL_ROOT.$destination);
        if ( ! file_exists($full_destination)) {
            return Response::JSON(['message' => 'The destination does not exist'], 404);
        }
        if ( ! is_writable($full_destination)) {
            return Response::JSON(['message' => 'The destination is not writable'], 403);
        }
        $message_bag = [];
        foreach ($sources as $source) {
            $sourcePath  = $this->path.$source;
            $dirInfo     = pathinfo($sourcePath);
            $safe_target = Utils::cleanDir($full_destination.'/'.$source);
            $i           = 1;

            Utils::secureDir($sourcePath);
            Utils::secureDir($safe_target);

            while (file_exists($safe_target)) {
                $safe_target = Utils::cleanDir($full_destination.'/'.$dirInfo['filename']." ({$i})");
                if ( ! is_dir($sourcePath)) {
                    $safe_target .= ".".$dirInfo['extension'];
                }
                $i++;
            }

            if ((is_file($sourcePath) && copy($sourcePath, $safe_target)) || $this->recursiveCopy($sourcePath,
                    $safe_target)) {
                $message_bag[] = "Copied {$source}";
            } else {
                $message_bag[] = "Could not copy {$source}";
            }
        }

        return Response::JSON(['message' => 'Selected files and folders has been copied', 'bag' => $message_bag]);
    }

    /**
     * Copies recursively
     *
     * @param $src
     * @param $dst
     *
     * @return bool
     */
    private function recursiveCopy($src, $dst)
    {
        $dir = opendir($src);
        mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src.'/'.$file)) {
                    $this->recursiveCopy($src.'/'.$file, $dst.'/'.$file);
                } else {
                    copy($src.'/'.$file, $dst.'/'.$file);
                }
            }
        }
        closedir($dir);

        return file_exists($dst);
    }

    /**
     * Moves item or items
     *
     * @return mixed
     */
    public function move()
    {
        $sources          = $this->payload['sources'];
        $destination      = $this->payload['destination'];
        $full_destination = Utils::cleanDir(FileManager::$JAIL_ROOT.$destination);
        if ( ! file_exists($full_destination)) {
            return Response::JSON(['message' => 'The destination does not exist'], 404);
        }
        if ( ! is_writable($full_destination)) {
            return Response::JSON(['message' => 'The destination is not writable'], 403);
        }
        $message_bag = [];
        foreach ($sources as $source) {
            $sourcePath  = $this->path.$source;
            $dirInfo     = pathinfo($sourcePath);
            $safe_target = Utils::cleanDir($full_destination.'/'.$source);
            $i           = 1;

            Utils::secureDir($sourcePath);
            Utils::secureDir($safe_target);

            while (file_exists($safe_target)) {
                $safe_target = Utils::cleanDir($full_destination.'/'.$dirInfo['filename']." ({$i})");
                if ( ! is_dir($sourcePath)) {
                    $safe_target .= ".".$dirInfo['extension'];
                }
                $i++;
            }

            if (( ! is_dir($sourcePath) && rename($sourcePath, $safe_target)) || $this->recursiveMove($sourcePath,
                    $safe_target)) {
                if(is_file($sourcePath)) {
                    $this->remThumb($sourcePath);
                }
                $message_bag[] = "Moved {$source}";
            } else {
                $message_bag[] = "Could not move {$source}";
            }
        }

        return Response::JSON(['message' => 'Selected files and folders has been moved', 'bag' => $message_bag]);
    }

    /**
     * Moves items recursively
     *
     * @param $src
     * @param $dst
     *
     * @return bool
     */
    private function recursiveMove($src, $dst)
    {
        $dir = opendir($src);
        mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src.'/'.$file)) {
                    $this->recursiveMove($src.'/'.$file, $dst.'/'.$file);
                } else {
                    rename($src.'/'.$file, $dst.'/'.$file);
                    $this->remThumb($src.'/'.$file);
                }
            }
        }
        closedir($dir);

        rmdir($src);

        return file_exists($dst);
    }

    /**
     * Deletes an item
     *
     * @return mixed
     */
    public function delete()
    {
        $items = $this->payload['items'];
        if ( ! is_writable($this->path)) {
            return Response::JSON(['message' => 'The path is not writable']);
        }
        foreach ($items as $item) {
            $path = $this->path.$item;
            Utils::secureDir($path);

            if ( ! file_exists($path)) {
                return Response::JSON(['message' => 'The dir/file '.$item.' does not exist'], 404);
            }
            if (is_file($path)) {
                if (unlink($path)) {
                    $this->remThumb($path);
                } else {
                    return Response::JSON(['message' => 'Could not delete file '.$item], 503);
                }
            } elseif ( ! $this->rrmdir($path)) {
                return Response::JSON(['message' => 'Could not delete dir '.$item], 503);
            }
        }

        return Response::JSON(['message' => 'Selected Files are Deleted']);
    }

    /**
     * Removes thumb when an image is deleted
     *
     * @param $path
     */
    private function remThumb($path)
    {
        $info = pathinfo($path);
        if ( ! in_array($info['extension'], ['png', 'jpg', 'jpeg', 'webp', 'gif'])) {
            return;
        }
        $thumb = dirname($path).'/_thumbs/'.basename($path);

        if (file_exists($thumb)) {
            unlink($thumb);
        }
    }

    /**
     * Delete directory recursively
     *
     * @param $dir
     *
     * @return bool
     */
    private function rrmdir($dir)
    {
        if ( ! is_dir($dir)) {
            return unlink($dir);
        }

        $handle = opendir($dir);

        while (false !== ($file = readdir($handle))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($dir.'/'.$file)) {
                    $this->rrmdir($dir.'/'.$file);
                } else {
                    unlink($dir.'/'.$file);
                    $this->remThumb($dir.'/'.$file);
                }
            }
        }
        closedir($handle);

        return rmdir($dir);
    }

    public static function methods()
    {
        return [
            "fetch_list",
            "file_info",
            "new_dir",
            "new_file",
            "upload",
            "download",
            "remote_download",
            "scan_dir",
            "rename",
            "copy",
            "move",
            "delete"
        ];
    }
}