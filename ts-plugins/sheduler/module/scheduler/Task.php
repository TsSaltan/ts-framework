<?php 
namespace tsframe\module\scheduler;

use Cron\CronExpression;
use tsframe\module\database\Database;

class Task {
	protected $name;
	protected $period;
	protected $lastExec;

    public function __construct(string $taskName, string $period, int $lastExec = 0){
        $this->name = $taskName;
        $this->period = $period;
        $this->lastExec = ($lastExec == 0) ? time() : $lastExec;
    }

    /**
     * Обновить параметры из базы данных
     */
    public function refreshParams(): bool {
        $query = Database::exec("SELECT *, UNIX_TIMESTAMP(`last-exec`) ts FROM `tasks` WHERE `name` = :name")
                        ->bind('name', $this->name)
                        ->fetch();

        foreach ($query as $task) {
            $this->period = $task['period'];
            $this->lastExec = ($task['ts'] == 0) ? time() : $task['ts'];
            return true;
        }

        return false;
    }

    /**
     * Получить следующее время запуска
     * @return [type] [description]
     */
    public function getRunDate(): int {
    	$cron = CronExpression::factory($this->period);
		return $cron->getNextRunDate(date('Y-m-d H:i:s', $this->lastExec))->format('U');
    }

    /**
     * Необходим ли запуск текущей задачи
     * @return bool
     */
    public function runRequired(): bool {
    	return $this->getRunDate() <= time();
    }

    /**
     * Обновить время запуска
     */
    public function update(){
    	Database::exec('UPDATE `tasks` SET `last-exec` = CURRENT_TIMESTAMP() WHERE `name` = :name', ['name' => $this->name]);
    	$this->lastExec = time();
    }

    /**
     * Удалить задачу
     */
    public function delete(){
    	Database::exec('DELETE FROM `tasks` WHERE `name` = :name', ['name' => $this->name]);
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getLastExec(): int {
        return $this->lastExec;
    }

    /**
     * @return string
     */
    public function getPeriod(): string {
        return $this->period;
    }
}