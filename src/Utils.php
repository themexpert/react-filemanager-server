<?php

namespace FileManager;

class Utils
{

	public static function cleanDir($dir)
	{
		return preg_replace('!\/+!', '/', $dir);
	}

	public static function secureDir($dir)
	{
		if (strpos($dir, '..') !== false)
		{
			Response::JSON(['message' => 'You can not use `..` in directory'], 403);
			die();
		}
	}

	public static function human_filesize($bytes, $decimals = 2)
	{
		$sz     = 'BKMGTP';
		$factor = floor((strlen($bytes) - 1) / 3);

		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}
}