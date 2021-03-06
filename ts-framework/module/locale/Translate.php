<?php
use tsframe\Config;

namespace tsframe\module\locale {
	/**
	 * Работа с языковым пакетом
	 * Языковой пакет иммет структуру как у JSON-файла конфигурации
	 */
	class Translate extends Config {
		protected static $separator = '/';

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

		/**
		 * Загрузить языковой файл
		 * @param  string $lang 
		 */
		public static function loadLangFile(string $lang){
			static::load(Lang::LANG_DIR . $lang . '.json');
		}

		/**
		 * Импорт ключей в текущий языковой файл из другого файла
		 * @param  string $path 
		 */
		public static function importFile(string $path){
			$data = json_decode(file_get_contents($path), true);
			static::$cache = array_merge(static::$cache, $data);
			static::save();
		}

		/**
		 * Ищет ключ в языковом пакете и формирует его перевод.
		 * Можно использовать переменные %s, %d и т.д. как у функции sprintf
		 * Возможно создание нескольких вариантов склонений в зависимости от цифровой переменной, 
		 * например: %d[помидор|помидора|помидоров] будет преобразован в 5 помидоров
		 * @param  string $key  Имя ключа
		 * @param  $args Все последующие аргументы - ключи, которые будут заменять переменные
		 * @return string
		 */
		public static function text(string $key, ...$args): string {
			$item = static::get($key);
			if(is_null($item) || !$item){
				$item = $key;
			}

			// Ищем ключи для sprintf
			return preg_replace_callback('#(%(?:\d+\$)?[+-]?(?:[ 0]|\'.{1})?-?\d*(?:\.\d+)?[bcdeEufFgGosxX])(\[([^\]]+)\])?+#Ui', 
				function(array $matches) use (&$args){
					$current = current($args);
					next($args);

					if(isset($matches[3])){
						$params = explode('|', $matches[3]);
						array_unshift($params, intval($current));
						$params[] = false;
						return sprintf($matches[1], $current) . ' ' . call_user_func_array([Translate::class, 'numCase'], $params);
					} else {
						return sprintf($matches[0], $current);
					}
				}, $item
			);

			return $item;
		}

		/**
		 * Выбор падежа в зависимости от численного значения
		 * @param  int          $n         Число
		 * @param  string       $n1        Единственное число (1 помидор/tomato)
		 * @param  string       $n2        Множенственное число (2 помидора/tomatoes)
		 * @param  string|null  $n5        [optional] Множенственное число (5 помидоров/tomatoes)
		 * @param  bool|boolean $addNumber [optional] Если true, добавит число перед склонением, false вернёт только склонение
		 * @return string
		 */
		public static function numCase(int $n, string $n1, string $n2, ?string $n5 = null, bool $addNumber = false): string {
			$ns = $n1;
			if(strlen($n5) == 0 && $n > 1){
				$ns = $n2;
			} 
			else if(strlen($n5) > 0){
				if($n == 0 || $n % 10 == 9 || $n % 10 == 8 || $n % 10 == 7 || $n % 10 == 6 || $n % 10 == 5 || $n % 10 == 0 || $n % 100 == 11 || $n % 100 == 12 || $n % 100 == 13){
					$ns = $n5;
				}

				if($n % 2 == 0 || $n % 10 == 3){
					$ns = $n2;
				}
			}

			return ($addNumber) ? $n . ' ' . $ns : $ns;
		}
	}

}

namespace {
	/**
	 * Alias Translate::text (return)
	 */
	function __(string $key, ...$args): string {
		return call_user_func_array([tsframe\Translate::class, 'text'], func_get_args());
	}

	/**
	 * Alias Translate::text (echo)
	 */
	function _e(string $key, ...$args){
		echo call_user_func_array('__', func_get_args());
	}

	/**
	 * Alias Translate::numCase (return)
	 */
	function _n(int $n, string $n1, string $n2, ?string $n5 = null){
		return call_user_func_array([tsframe\Translate::class, 'numCase'], func_get_args());
	}

	/**
	 * Alias Translate::numCase (echo)
	 */
	function _ne(int $n, string $n1, string $n2, ?string $n5 = null){
		echo call_user_func_array('_e', func_get_args());
	}
}