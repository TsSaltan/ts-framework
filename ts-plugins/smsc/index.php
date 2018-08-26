<?
/**
 * Система отправки смс через https://smsc.ru/
 */
namespace tsframe;

use tsframe\App;
use tsframe\Config;
use tsframe\Hook;
use tsframe\Plugins;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\UserAccess;
use tsframe\module\user\User;
use tsframe\view\TemplateRoot;
use tsframe\view\Template;

Hook::register('app.install', function(){
	if(is_null(Config::get('smsc'))){
		Config::set('smsc.login', "INPUT_YOUR_LOGIN");
		Config::set('smsc.password', "INPUT_YOUR_PASSWORD");
		Config::set('smsc.translit', "1");
		Config::set('access.smsc.log', UserAccess::Moderator);
	}
});

Hook::registerOnce('plugin.load', function(){
	Plugins::required('dashboard', 'user', 'database', 'logger');
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

/*Hook::register('menu.render.dashboard-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem('SMS-сообщения', ['url' => Http::makeURI('/dashboard/sms'), 'fa' => 'envelope-o', 'access' => UserAccess::getAccess('smsc.log')]));
});*/

Hook::register('template.dashboard.user.edit', function(Template $tpl, array &$configTabs, int &$activeTab){
	if(is_null($tpl->selectUser)) return;
	$selectUser = $tpl->selectUser;

	if($tpl->self || UserAccess::checkCurrentUser('user.edit')){
		if(isset($_GET['phone'])){
			$activeTab = sizeof($configTabs);
		}

		$configTabs['Телефон'] = function() use ($tpl, $selectUser){
			$tpl->inc('user_phone');
		};

	}
});

Hook::register('template.render', function(Template $tpl){
	$tpl->var('userPhone', User::current()->getMeta()->get('phone'));
});