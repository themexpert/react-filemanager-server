<?php

namespace FileManager;

class Utils
{
	/**
	 * Clean up directory
	 *
	 * @param $dir
	 *
	 * @return null|string|string[]
	 */
	public static function cleanDir($dir)
	{
		return preg_replace('!\/+!', '/', $dir);
	}

	/**
	 * Checks for security
	 *
	 * @param $dir
	 */
	public static function secureDir($dir)
	{
		if (strpos($dir, '/..') !== false || strpos($dir, '../') !== false)
		{
			Response::JSON(['message' => 'You can not use `/..` and `../` in directory'], 403);
			die();
		}
	}

	/**
	 * Gives a human-readable file-size
	 * @param     $bytes
	 * @param int $decimals
	 *
	 * @return string
	 */
	public static function human_filesize($bytes, $decimals = 2)
	{
		$sz     = 'BKMGTP';
		$factor = floor((strlen($bytes) - 1) / 3);

		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}
}