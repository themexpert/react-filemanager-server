<?php

namespace FileManager;

class Pluggable
{
	/**
	 * @var Pluggable
	 */
	private static $instance;

	private $plugins = [];

	private function __construct()
	{
		$this->load(General::class);
	}

	public static function getInstance()
	{
		if (self::$instance)
		{
			return self::$instance;
		}
		self::$instance = new self();

		return self::$instance;
	}

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

	public function isValidAlias($category, $alias)
	{
		return array_key_exists($category, $this->plugins) && in_array($alias, $this->plugins[$category]['methods']);
	}

	public function getCategory($category)
	{
		return $this->plugins[$category];
	}

	public function executeRequest()
	{
		$request = Request::getInstance();
		$category = $this->getCategory($request->getCategory());
		return call_user_func([new $category['class'], $request->getAlias()]);
	}

	public function log()
	{
		print_r($this->plugins);
	}
}