<?php

namespace FileManager;

class FileManager
{
	public static $ROOT;
	public static $JAIL_ROOT;
	public static $UPLOAD;

	public function __construct($config_file)
	{
	    if(!file_exists($config_file)) {
	         Response::JSON(['message' => 'Config file could not be found'], 503);
	         die;
        }
		$config     = include_once $config_file;
	    if(!is_array($config)) {
	        Response::JSON(['message' => 'Invalid config file'], 503);
	        die;
        }
        self::$JAIL_ROOT = Utils::cleanDir($_SERVER['DOCUMENT_ROOT'] . $config['root']);
	    if(!file_exists(self::$JAIL_ROOT)) {
		    Response::JSON(['message' => 'The root directory for file manager does not exist'], 503);
		    die;
	    }
        self::$ROOT = Utils::cleanDir(self::$JAIL_ROOT . rtrim(Request::getInstance()->getWorkingDir(), '/') . '/');
		self::$UPLOAD = $config['upload'];

		return $this->execute();
	}

	public function execute()
	{
		$pluggable = Pluggable::getInstance();
		$request   = Request::getInstance();

		if($request->hasKey('thumb')) {
			return Loader::thumb($request->get('thumb'));
		}
		elseif($request->hasKey('icon')) {
			return Loader::icon($request->get('icon'));
		}
		elseif ($request->hasKey('raw')) {
			return Loader::raw($request->get('raw'));
		}

		$category  = $request->getCategory();
		$alias     = $request->getAlias();
		if (!$category || !$alias || !$pluggable->isValidAlias($category, $alias))
		{
			return Response::JSON(['message' => 'Invalid Request'], 406);
		}

		return $pluggable->executeRequest();
	}
}