<?php
namespace tsframe;

/**
 * Класс для хранения конфигов в формате json
 * Config::get('param');
 * Config::get('param.path');
 * Config::set('param', 'value');
 * Config::set('param,path', 'value');
 */
class Config {

	/**
	 * Файл с настройками
	 * @var string
	 */
	protected static $file;
	
	/**
	 * Закешированные данные
	 * @var array
	 */
	protected static $cache = [];

	public static function load(string $file){
		self::$file = $file;

		if(file_exists(self::$file)){
			self::$cache =  json_decode(file_get_contents(self::$file), true);
		}
	}

	protected static function save(){
		$data = json_encode(self::$cache, JSON_PRETTY_PRINT);
		file_put_contents(self::$file, $data);
	}

	/**
	 * Получить ссылку на раздел настроек
	 */
	protected static function &getPath(string $path){
		$path = explode('.', $path);
		$data = &self::$cache;

		foreach ($path as $p) {
			$data = &$data[$p];
		}

		return $data;
	}

	public static function get(string $path = '*'){
		if($path == '*') return self::$cache;

		$data = self::getPath($path);
		return $data;
	}	

	public static function set(string $path = '*', $value){
		if($path == '*') $data =& self::$cache;
		else $data =& self::getPath($path);
		$data = $value;
		self::save();
	}

	public static function isset(string $path) {
		$path = explode('.', $path);
		$data = &self::$cache;

		foreach ($path as $p) {
			if(!isset($data[$p])) return false;
			$data = &$data[$p];
		}

		return true;
	}

	public static function unset(string $path): bool {
		$path = explode('.', $path);
		$data = &self::$cache;
		$len = sizeof($path);

		foreach ($path as $k => $p) {
			if(!isset($data[$p])) return false;

			if($k == $len-1){
				unset($data[$p]);
			} else {
				$data = &$data[$p];
			}
		}

		self::save();
		return true;
	}
}