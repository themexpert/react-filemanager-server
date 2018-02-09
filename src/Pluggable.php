<?php

namespace FileManager;

class Pluggable
{
	/**
	 * @var Pluggable
	 */
	private static $instance;

	private $plugins = [];

	/**
	 * Pluggable constructor.
	 * Loads all the plugins
	 */
	private function __construct()
	{
		$this->load(General::class);
	}

	/**
	 * Apply singleton
	 *
	 * @return Pluggable
	 */
	public static function getInstance()
	{
		if (self::$instance)
		{
			return self::$instance;
		}
		self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Loads a plugin
	 *
	 * @param $class
	 */
	private function load($class)
	{
		$class                                             = new \ReflectionClass($class);
		$this->plugins[strtolower($class->getShortName())] = [
			'class'   => $class->getName(),
			'methods' => array_map(function ($method) {
				return $method->name;
			}, $class->getMethods(\ReflectionMethod::IS_PUBLIC))
		];
	}

	/**
	 * Checks if an alias is valid
	 *
	 * @param $category
	 * @param $alias
	 *
	 * @return bool
	 */
	public function isValidAlias($category, $alias)
	{
		return array_key_exists($category, $this->plugins) && in_array($alias, $this->plugins[$category]['methods']);
	}

	/**
	 * Retrieve category
	 *
	 * @param $category
	 *
	 * @return mixed
	 */
	public function getCategory($category)
	{
		return $this->plugins[$category];
	}

	/**
	 * Execute a request
	 *
	 * @return mixed
	 */
	public function executeRequest()
	{
		$request = Request::getInstance();
		$category = $this->getCategory($request->getCategory());
		return call_user_func([new $category['class'], $request->getAlias()]);
	}

	/**
	 * Print plugins information
	 */
	public function log()
	{
		print_r($this->plugins);
	}
}