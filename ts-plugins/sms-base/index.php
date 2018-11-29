<?
/**
 * Система отправки смс
 * Основа для провайдеров
 * Номер телефона в аккаунте пользователя
 */
namespace tsframe;

use tsframe\App;
use tsframe\Config;
use tsframe\Hook;
use tsframe\Plugins;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\UserAccess;
use tsframe\module\user\User;
use tsframe\module\io\Input;
use tsframe\view\TemplateRoot;
use tsframe\view\Template;
use tsframe\view\HtmlTemplate;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('dashboard', 'user', 'database', 'logger');
});

Hook::registerOnce('plugin.load', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
	TemplateRoot::addDefault(__DIR__ . DS . 'template');
	TemplateRoot::addDefault(CD . 'vendor' . DS . 'andr-04' . DS . 'jquery.inputmask-multi');

	Input::addFilter('phone', function(Input $input){
		$phone = $input->getCurrentData();
		$phone = str_replace(['+', ' ', '-', '(', ')', '.', "\t", '_'], '', $phone);
		
		if(is_numeric($phone)){
			$input->varProcess(function() use ($phone){
				return '+' . $phone;
			});

			return true;
		}

		return false;
	});
});

Hook::register('template.dashboard.user.edit', function(Template $tpl, array &$configTabs, &$activeTab){
	if(is_null($tpl->selectUser)) return;
	$selectUser = $tpl->selectUser;

	if($tpl->self || UserAccess::checkCurrentUser('user.edit')){
		if(isset($_GET['phone'])){
			$activeTab = 'phone';
		}

		$configTabs['phone']['title'] = 'Телефон';
		$configTabs['phone']['content'] = function() use ($tpl, $selectUser){
			$tpl->inc('user_phone');
		};
	}
});

Hook::register('template.render', function(Template $tpl){
	$tpl->var('userPhone', User::current()->getMeta()->get('phone'));
});

Hook::register('template.dashboard.header', function(HtmlTemplate $tpl){
	$tpl->js('js/jquery.inputmask.bundle.min.js');
	$tpl->js('js/jquery.inputmask-multi.js');
});

Hook::register('template.dashboard.config', function(HtmlTemplate $tpl){
	$tpl->inc('sms_config');
});