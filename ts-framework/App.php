<?php
namespace tsframe;

use tsframe\Config;
use tsframe\controller\ErrorController;
use tsframe\controller\InstallController;
use tsframe\exception\BaseException;
use tsframe\exception\PluginException;
use tsframe\module\Crypto;
use tsframe\view\Template;

class App {

	/**
	 * Версия приложения
	 * major.minor.release
	 */
	const VERSION = "3.0-dev";

	/**
	 * Путь к директории на сервере, откуда будет брать начало роутер, ссылки и т.д.
	 * @var string
	 */
	protected static $basePath = '/';

	/**
	 * Возможность установить базовую директорию для работы скрипта
	 * Влияет на роутер (для роутера необходим путь вида %path%/ - слеш в конце, но не в начале!)
	 * Влияет на генерацию ссылок (метод Http::makeURI), к абсолютным ссылкам в начало добавляет часть пути
	 * @param string $path
	 */
	public static function setBasePath(string $path){
		$path = str_replace(['\\', '|'], '/', $path);

		// Cлэш в начале
		if(substr($path, 0, 1) != '/') $path = '/' . $path;

		// Cлэш в конце
		if(substr($path, -1, 1) != '/') $path .= '/';

		self::$basePath = $path;
	}


	public static function getBasePath(): string {
		return self::$basePath;
	}

	/**
	 * Проверяет, установлена ли необходимая версия фреймворка
	 * @param  string $version Требуемая версия
	 * @param  string $rule    Правило для сравнения, по умлочанию >=
	 * @throws BaseException
	 */
	public static function requiredVersion(string $version, string $rule = ">=", bool $throwException = true): bool {
		if(!version_compare(self::VERSION, $version, $rule)){
			if($throwException) throw new BaseException("Required framework version " . $rule . " " . $version . ". Current version: " . self::VERSION);
			return false;
		}

		return true;
	}

	/**
	 * Загрузка приложения
	 */
	public static function load(){
		Plugins::load();
	}
	
	/**
	 * Запуск приложения
	 * Поиск подходящего контроллера
	 */
	public static function start(){
		self::load();
		Hook::call('app.start');
		try{
			$controller = Router::findController();
			$controller->send();
		} catch(BaseException $e) {
			$controller = new ErrorController($e);
			$controller->send();
		} 
		Hook::call('app.finish');
	}

	/**
	 * Включен ли режим разработчика
	 */
	public static function isDev() : bool {
		return Config::get('dev_mode') === true;
	}

	/**
	 * Установка компонентов приложения
	 */
	public static function install(): bool {
		$controller = new InstallController;
		$controller->checkPost();

		$install = Plugins::install();
		$controller->setErrors($install['errors']);
		$controller->setRequiredFields($install['params']);
		$controller->send();

		if(!$controller->isInstalled()){
			return false;
		}

		if(strlen(Config::get('appId')) < 64){
			Config::set('appId', Crypto::generateString(64));
		}
		
		Hook::call('app.install');
		return true;
	}
}