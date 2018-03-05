<?php

namespace FileManager\Plugins;

abstract class Plugin {
	public static function methods() { return []; }
	public static function actions() { return []; }
	public static function tabs() { return []; }
}