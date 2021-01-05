<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Hook;
use tsframe\Http;
use tsframe\Plugins;
use tsframe\module\Logger;
use tsframe\module\scheduler\Scheduler;

/**
 * Контроллер для планировщика задач
 * @route GET /scheduler-task/
 */
class SchedulerController extends AbstractController{
	public function response(){
		// Для браузера - никакого доступа
		if(Http::isBrowser()){
			$this->responseCode = Http::CODE_ACCESS_DENIED;
			$this->responseBody = "Access denied";
			return;
		}

		$tasks = Scheduler::getTasks();
		Hook::call('scheduler.start', [$tasks], null, function(){});

		foreach ($tasks as $task){
			$task->refreshParams();

			if($task->runRequired()){
				$logData = [
					'now' => time(), 
					'last-exec' => date('Y-m-d h:i:s', $task->getLastExec()),
					'period' => $task->getPeriod(), 
				];

				$task->update();
				Hook::call('scheduler.task.' . $task->getName(), [$task], function($return) use ($task, $logData){
					if($return !== false){
						$lresult = 'successfully';
					} else {
						$lresult = 'unsuccessfully';
					}

					Logger::scheduler()->debug('Task "'.$task->getName().'" started ' . $lresult, $logData);

				}, function($error) use ($task, $logData){
					$logData['error_message'] = $error->getMessage();
					$logData['error_code'] = $error->getCode();
					Logger::scheduler()->error('Task "'.$task->getName().'" throws exception ['.get_class($error).']', $logData);
				});
			}
		}

		$this->responseCode = Http::CODE_OK;
		$this->responseBody = "Tasks updated";
	}
}