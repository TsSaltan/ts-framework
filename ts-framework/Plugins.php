<?php
namespace tsframe;

use tsframe\exception\PluginException;

class Plugins{
	/**
	 * Loaded plugins name => path
	 * @var array
	 */
	protected static $loaded = [];

	/**
	 * Disabled plugins
	 * @var array
	 */
	protected static $disabled = [];

	public static function load(){
		$list = self::getList();
		foreach ($list as $plugin) {
			$parent = dirname($plugin);
			$pluginName = basename($parent);
			if(in_array($pluginName, self::$disabled)) continue;

			Autoload::addRoot($parent);
			require $plugin;
			self::$loaded[$pluginName] = $parent;
		}

		foreach (self::$loaded as $name => $path) {
			Hook::call('plugin.load', [$name, $path]);
		}
	}

	public static function install(){
		foreach (self::$loaded as $name => $path) {
			Hook::call('plugin.install', [$name, $path]);
		}
	}

	protected static function getList(){
		$files = glob(CD . 'ts-plugins' . DS . '*' . DS . 'index.php');
		return $files;
	}

	/**
	 * Позволяет указать на требуемые плагины
	 * @throws PluginException
	 * @param строки с названиями необходимых плагинов
	 */
	public static function required(){
		foreach(func_get_args() as $pluginName){
			if(!isset(self::$loaded[$pluginName])){
				throw new PluginException('Plugin "'. $pluginName .'" does not loaded', 500, [
					'pluginName' => $pluginName,
					'loaded' => self::$loaded,
					'disabled' => self::$disabled,
				]);
			}
		}
	}

	/**
	 * Позволяет указать на конфликтующие плагины
	 * @throws PluginException
	 * @param строки с названиями конфликтующих плагинов
	 */
	public static function conflict(){
		foreach(func_get_args() as $pluginName){
			if(isset(self::$loaded[$pluginName])){
				throw new PluginException('Plugin conflict with "'. $pluginName .'"', 500, [
					'pluginName' => $pluginName,
					'loaded' => self::$loaded
				]);
			}
		}
	}

	/**
	 * Позволяет отключить плагины
	 */
	public static function disable(){
		self::$disabled = array_unique(array_merge(self::$disabled, func_get_args()));
	}
}