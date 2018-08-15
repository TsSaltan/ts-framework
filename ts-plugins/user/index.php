<?
/**
 * Система пользователей + авторизация
 */
namespace tsframe;

use tsframe\Config;
use tsframe\module\menu\Menu;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\User;
use tsframe\module\user\SingleUser;
use tsframe\module\user\UserAccess;
use tsframe\module\user\SocialLogin;
use tsframe\view\Template;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.load', function(){
	Plugins::required('database');

	TemplateRoot::addDefault(__DIR__ . DS . 'template');	
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

/**
 * Менюшка вверху
 */
Hook::register('menu.render.dashboard-top', function(MenuItem $menu){
	$menu->add(new MenuItem('Мой профиль', ['url' => '/dashboard/user/me', 'fa' => 'user', 'access' => UserAccess::getAccess('user.self')]), 0);
	$menu->add(new MenuItem('Настройки профиля', ['url' => '/dashboard/user/me/edit', 'fa' => 'gear', 'access' => UserAccess::getAccess('user.self')]), 1);
});

/**
 * Меню сбоку
 */
Hook::register('menu.render.dashboard-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem('Список пользователей', ['url' => '/dashboard/user/list', 'fa' => 'users', 'access' => UserAccess::getAccess('user.list')]));
});

/**
 * В шаблон будут добавлены переменные с инфо о пользователе
 */
Hook::register('template.render', function($tpl){
	$user = User::current();
	$tpl->vars([
		'user' => $user,
		'login' => $user->get('login'),
		'email' => $user->get('email'),
		'access' => $user->get('access'),
		'socialLogin' => SocialLogin::getWidgetCode(),
		'accessList' => UserAccess::getArray(),
	]);
});

/**
 * После установки приложения создадим учётку администратора
 */
Hook::registerOnce('app.install', function(){
	if(!User::exists(['access' => UserAccess::Admin])){
		$login = 'admin';
		$mail = 'change@admin.mail';
		$password = uniqid('pwd');
		User::register($login, $mail, $password, UserAccess::Admin);
		Log::add('New admin profile:', [
			'login' => $login,
			'mail' => $mail,
			'password' => $password,
		]);
	}

	if(Config::get('access') == null){
		Config::set('access.user.onRegister', 1); 	// Права доступа при регистрации
		Config::set('access.user.self', 1); 		// Изменение собственного профиля
		Config::set('access.user.view', 1); 		// Просмотр профиля пользователей
		Config::set('access.user.list', 2); 		// Просмотр списка пользователей
		Config::set('access.user.edit', 2); 		// Редактирование пользователей
		Config::set('access.user.delete', 4); 		// Редактирование пользователей
		Config::set('access.user.editAccess', 4);	// Редактирование уровня доступа
	}

	if(Config::get('appId') == null){
		Config::set('appId', generateRandomString(32));
	}
});

function generateRandomString(int $length){
	if (function_exists("random_bytes")) {
	    $bytes = random_bytes(ceil($length / 2));
	    $string = bin2hex($bytes);
	} else if (function_exists("openssl_random_pseudo_bytes")) {
		$bytes = openssl_random_pseudo_bytes($length);
		$string = base64_encode($bytes);
	} else {
		$string = sha1(uniqid(time()));
	}

	return substr($string, 0, $length);
}

Hook::register('template.dashboard.user.profile', function(Template $tpl, SingleUser $user){
	?>
	User ID: <b><?=$user->get('id')?></b>
	<?
	//ID: 
});