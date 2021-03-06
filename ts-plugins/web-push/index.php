<?php 
/**
 * Отправка Push сообщений в браузер
 *
 * @link https://web-push-codelab.glitch.me/
 * @link https://developers.google.com/web/fundamentals/codelabs/push-notifications/
 * @link https://github.com/web-push-libs/web-push-php
 * @link https://github.com/GoogleChromeLabs/web-push-codelab
 *
 * - Необходимо добавить в крон выполнение sheduler, т.к. очередь зависит от крона
 * - 
 */

namespace tsframe;

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use tsframe\App;
use tsframe\Config;
use tsframe\Http;
use tsframe\module\menu\MenuItem;
use tsframe\module\push\WebPushQueue;
use tsframe\module\scheduler\Scheduler;
use tsframe\module\scheduler\Task;
use tsframe\module\user\UserAccess;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('geodata', 'sheduler', 'user', 'dashboard');
	
	return [
		PluginInstaller::withKey('push.publicKey')
					->setType('text')
					->setDescription("<u>Публичный</u> ключ для Web Push (<a href='https://web-push-codelab.glitch.me/' target='_blank'>отсюда</a>)"),		

		PluginInstaller::withKey('push.privateKey')
					->setType('text')
					->setDescription("<u>Приватный</u> ключ для Web Push"),

		PluginInstaller::withKey('access.webpush')
					->setType('select')
					->setDescription("Права доступа: доступ к базе данных web-push клиентов")
					->setDefaultValue(UserAccess::Admin)
					->setValues(array_flip(UserAccess::getArray())),
	];
});

/**
 * Загрузка плагина
 */
Hook::registerOnce('plugin.load', function(){
	TemplateRoot::add('index', __DIR__ . DS . 'template' . DS . 'index');
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

/**
 * Добавляем пункт меню
 */
Hook::registerOnce('menu.render.dashboard-admin-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem('Web-Push клиенты', ['url' => Http::makeURI('/dashboard/web-push-clients'), 'fa' => 'commenting', 'access' => UserAccess::getAccess('webpush')]));
});

/**
 * Очередь для рассылки пушей
 */
Hook::registerOnce('app.install', function() {
	Scheduler::addTask('web-push-send', '*/5 * * * *');
});


/**
 * Разбор очереди пушей
 */
Hook::register('scheduler.task.web-push-send', function(Task $task) {
	$queues = WebPushQueue::getList();
	
	foreach ($queues as $queue) {
		$queue->send();
		break;
	}

	return true;
});