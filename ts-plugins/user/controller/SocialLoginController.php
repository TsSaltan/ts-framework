<?php
namespace tsframe\controller;

use tsframe\exception\AccessException;
use tsframe\exception\UserException;
use tsframe\Http;
use tsframe\module\Meta;
use tsframe\module\user\User;
use tsframe\module\user\UserConfig;
use tsframe\module\user\SingleUser;
use tsframe\module\user\UserAccess;
use tsframe\module\user\SocialLogin;
use tsframe\module\io\Input;

/**
 * @route GET|POST /dashboard/social-login
 */
class SocialLoginController extends AbstractController{
	public function response(){
		if(!UserConfig::isSocialEnabled()) throw new AccessException('Social login is disabled');

		$currentUser = User::current();
		$data = Input::post(false)
						->name('token')
						  ->required()
					  	  ->minLength(1)
						->assert();

		$login = new SocialLogin($data['token']);
		
		// Если не авторизован, пытаемся авторизовать
		if(!$currentUser->isAuthorized()){
			try {	
				$user = $login->getUser();
				$user->createSession();
				Http::redirect(Http::makeURI('/dashboard/'));
			} catch (UserException $e){
				Http::redirect(Http::makeURI('/dashboard/auth', ['error' => 'social']));
			}
		} 
		// Если авторизован, привязываем аккаунт
		else {
			try{
				$login->saveUserMeta($currentUser);
				Http::redirect(Http::makeURI('/dashboard/user/me/edit', ['social' => 'success'], 'social'));
			} catch(AccessException $e){
				Http::redirect(Http::makeURI('/dashboard/user/me/edit', ['social' => 'fail'], 'social'));
			}
		}
	}
}