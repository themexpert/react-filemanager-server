<?php

namespace FileManager;

class Response {
	/**
	 * Sends JSON response
	 *
	 * @param     $data
	 * @param int $code
	 *
	 * @return mixed
	 */
	public static function JSON($data, $code=200)
	{
		header('Content-Type: application/json');
		http_response_code($code);
		return print_r(json_encode($data, 128));
	}

	/**
	 * Sends raw response
	 *
	 * @param     $mime
	 * @param     $content
	 * @param int $response
	 *
	 * @return bool
	 */
	public static function RAW($mime, $content, $response=200)
	{
		header('Content-Type: ' . $mime);
		http_response_code($response);
		print $content;
		return true;
	}
}