<?php

namespace FileManager;

class Response {
	public static function JSON($data, $code=200)
	{
		header('Content-Type: application/json');
		http_response_code($code);
		return print_r(json_encode($data, 128));
	}

	public static function RAW($mime, $content, $response=200)
	{
		header('Content-Type: ' . $mime);
		http_response_code($response);
		print $content;
		return true;
	}
}