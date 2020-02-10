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
		// Clear json_last_error()
		json_encode(null);
		
		$data = self::convert_from_latin1_to_utf8_recursively($data);

		$json = json_encode($data, 128);
		if (JSON_ERROR_NONE !== json_last_error()) {
			$data = new \Exception(sprintf(
				'Unable to encode data to JSON in %s: %s',
				__CLASS__,
				json_last_error_msg()
			));
			http_response_code('503');
			$json = json_encode(['message' => $data->getMessage()]);
		}else{
			http_response_code($code);
		}

		header('Content-Type: application/json');		
		return print_r($json);
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
		if(!$mime){
			if(substr($content, 0, 4) === "<svg"){
				$mime = 'image/svg+xml';
			}
		}
		header('Content-Type: ' . $mime);
		http_response_code($response);
		print $content;
		return true;
	}
	
	/**
	* Encode array from latin1 to utf8 recursively
	* @param $dat
	* @return array|string
	*/
	public static function convert_from_latin1_to_utf8_recursively($dat)
	{
		if (is_string($dat)) {
			return utf8_encode($dat);
		} elseif (is_array($dat)) {
			$ret = [];
			foreach ($dat as $i => $d) $ret[ $i ] = self::convert_from_latin1_to_utf8_recursively($d);
			return $ret;
		} elseif (is_object($dat)) {
			foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);
			return $dat;
		} else {
			return $dat;
		}
	}
}
