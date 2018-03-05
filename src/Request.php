<?php

namespace FileManager;

class Request {

	/**
	 * @var Request
	 */
	private static $instance;
	private $requests = [];
	private $files = [];

	/**
	 * Request constructor.
	 */
	private function __construct()
	{
		$requests = !empty($_FILES) ? $_POST : json_decode(trim(file_get_contents('php://input')), true);
		$requests = is_array($requests) ? $requests : [];
		$this->requests = array_merge($requests, $_GET, $_FILES);
		$this->files = $_FILES;
	}

	/**
	 * Applies singleton
	 *
	 * @return Request
	 */
	public static function getInstance() {
		if(self::$instance)
			return self::$instance;
		self::$instance = new self();
		return self::$instance;
	}

	/**
	 * Determine if the requested key exists in the request
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function hasKey($key)
	{
		return array_key_exists($key, $this->requests);
	}

	/**
	 * Get request value by key
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public function get($key) {
		if(!$this->hasKey($key))
			return null;
		return $this->requests[$key];
	}

	/**
	 * Determine if file exists
	 *
	 * @param $file
	 *
	 * @return bool
	 */
	public function hasFile($file)
	{
		if(empty($this->files))
			return false;
		return array_key_exists($file, $this->files);
	}

	/**
	 * Get the file
	 *
	 * @param $file
	 *
	 * @return mixed|null
	 */
	public function file($file)
	{
		if(!$this->hasFile($file))
			return null;
		return $this->files[$file];
	}

	/**
	 * Get the currently working dir
	 *
	 * @return mixed|null
	 */
	public function getWorkingDir()
	{
		if(!$this->hasKey('working_dir'))
			return null;
		return $this->get('working_dir');
	}

	/**
	 * Get the current plugin
	 *
	 * @return mixed|null
	 */
	public function getPlugin()
	{
		if(!$this->hasKey('plugin'))
			return null;
		return $this->get('plugin');
	}

	/**
	 * Get the current alias
	 *
	 * @return mixed|null
	 */
	public function getAlias()
	{
		if(!$this->hasKey('alias'))
			return null;
		return $this->get('alias');
	}

	/**
	 * Get the current payload
	 *
	 * @return mixed|null
	 */
	public function getPayload()
	{
		if(!$this->hasKey('payload'))
			return null;
		return $this->get('payload');
	}

	/**
	 * Handle static calls
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 */
	public static function __callStatic($name, $arguments)
	{
		if(!self::$instance)
			self::$instance = new self();
		return call_user_func_array(self::$instance->{$name}, $arguments);
	}

}