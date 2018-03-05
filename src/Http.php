<?php

namespace FileManager;

class Http
{
	/**
	 * GET request via cURL
	 *
	 * @param $url
	 *
	 * @return string|bool
	 */
	public static function get($url)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . './cookie.txt');
		curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . './cookie.txt');
		$result = curl_exec($ch);
		$err    = curl_error($ch);
		$errn   = curl_errno($ch);
		curl_close($ch);
		if ($errn > 0)
		{
			return false;
		}

		return $result;
	}

	/**
	 * POST request via cURL
	 *
	 * @param $url
	 * @param $data
	 *
	 * @return string
	 */
	public static function post($url, $data)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . './cookie.txt');
		curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . './cookie.txt');
		$result = curl_exec($ch);
		$err    = curl_error($ch);
		$errn   = curl_errno($ch);
		curl_close($ch);
		if ($errn > 0)
		{
			return false;
		}

		return $result;
	}
}