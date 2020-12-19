<?php
namespace tsframe\module;

use tsframe\Config;
use tsframe\module\database\Database;

/**
 * Used for old-compatibility
 * @deprecated
 */
class Log {
	protected static $logs = [];

	public static function add(string $message, string $type = 'default', array $meta = []): bool {
		$meta['message'] = $message;
		self::$logs[$type][] = $meta;

		try {
			return Database::prepare('INSERT INTO `log` (`id`, `type`, `data`) VALUES (UUID(), :type, :data)')
				->bind('type', $type)
				->bind('data', json_encode($meta))
				->exec()
				->lastInsertId() > 0;
		} catch (\Exception $e){
			return false;
		}
	}

	public static function getCurrentLogs(): array {
		return self::$logs;
	}

	public static function getLogsCount(string $type): int {
		$logs = Database::prepare('SELECT COUNT(*) c FROM `log` WHERE `type` = :type')
					->bind('type', $type)
					->exec()
					->fetch();

		return $logs[0]['c'] ?? -1;
	}

	public static function getLogs(string $type, int $offset = 0, int $count = 0): array {
		$return = [];
		$limits = (($count > 0) ? 'LIMIT ' . $count : '') . ' ' . (($offset > 0) ? 'OFFSET ' . $offset : '');
		$logs = Database::prepare('SELECT * FROM `log` WHERE `type` = :type ORDER BY `date` DESC ' . $limits)
					->bind('type', $type)
					->exec()
					->fetch();

		foreach ($logs as $log) {
			$return[] = ['date' => $log['date'], 'data' => json_decode($log['data'], true)];
		}

		return $return;
	}

	public static function getTypes(): array {
		$return = [];
		$types = Database::exec('SELECT DISTINCT `type` FROM `log`')
						->fetch();

		foreach ($types as $key => $value) {
			$return[] = $value['type'];
		}

		return $return;		
	}

	/**
	 * Add log with type=funcName
	 * @param  string $message
	 * @param  array  $meta
	 */
	public static function __callStatic(string $name , array $args){
		return self::add($args[0], $name, $args[1] ?? []);
	}

	/**
	 * Очистить логи
	 * @param  string      $type      Тип логов ('*' = все)
	 * @param  int|integer $timestamp Метка времени, ДО которой логи будут очищены
	 * @return bool
	 */
	public static function clear(string $type = '*', int $timestamp = 0): bool {
		$query = ($type == '*') 
					? Database::prepare('DELETE FROM `log` WHERE 1 = 1')
					: Database::prepare('DELETE FROM `log` WHERE `type` = :type AND `date` <= from_unixtime(:ts)');
		
		$query->bind('ts', $timestamp);
		if($type != '*') $query->bind('type', $type);

		return $query->exec()->affectedRows() > 0;
	}

	/**
	 * Получить размер логов в базе данных
	 * @return int
	 */
	public static function getSize(): int {
		return Database::getSize('log');
	}
}