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
		$this->loadPlugins();
//		$this->log();
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

	private function loadPlugins()
	{
		$plugins = glob(__DIR__ . '/Plugins/*');
		foreach ($plugins as $plugin)
		{
			if (is_dir($plugin))
			{
				$file = $plugin . '/' . basename($plugin) . '.php';
				if (!file_exists($file))
				{
					include_once $file;
				}
				$plugin = basename($plugin);
				$this->load('FileManager\\Plugins\\' . $plugin . '\\' . $plugin);
			}
			else
			{
				include_once $plugin;
				$this->load('FileManager\\Plugins\\' . basename($plugin, '.' . pathinfo($plugin)['extension']));
			}
		}
	}

    /**
     * Loads a plugin
     *
     * @param $class_path
     */
	private function load($class_path)
	{
        try {
            $reflection_class = new \ReflectionClass($class_path);
        } catch (\ReflectionException $e) {
            error_log($e->getMessage());
        }
        $class_short_name                 = $reflection_class->getShortName();
		if($class_short_name === 'Plugin')
			return;
		$class_name                       = $reflection_class->getName();
		$this->plugins[$class_short_name] = [
			'class'   => $class_name,
			'methods' => array_map(function(\ReflectionMethod $method) {
			    return $method->getName();
            }, $reflection_class->getMethods(\ReflectionMethod::IS_PUBLIC))
		];
	}

	/**
	 * Checks if an alias is valid
	 *
	 * @param $plugin
	 * @param $alias
	 *
	 * @return bool
	 */
	public function isValidAlias($plugin, $alias)
	{
		$methods = $this->plugins[$plugin]['methods'];
		if(empty($methods))
			return false;
		if(is_array(array_values($methods)[0]))
			$methods = array_keys($methods);
		return array_key_exists($plugin, $this->plugins) && in_array($alias, $methods);
	}

	/**
	 * Retrieve plugin
	 *
	 * @param $plugin
	 *
	 * @return mixed
	 */
	public function getPlugin($plugin)
	{
		return $this->plugins[$plugin];
	}

	/**
	 * Execute a request
	 *
	 * @return mixed
	 */
	public function executeRequest()
	{
		$request  = Request::getInstance();
		$plugin = $this->getPlugin($request->getPlugin());

		return call_user_func([new $plugin['class'], $request->getAlias()]);
	}

//	public function plugins()
//	{
//		return Response::JSON($this->plugins);
//	}

	/**
	 * Print plugins information
	 */
	public function log()
	{
		print_r($this->plugins);
	}
}