<?php
namespace tsframe\controller;

use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\user\Referrer;

/**
 * @route GET /dashboard/[referrer:action]
 */ 
class ReferrerDashboard extends UserDashboard {
	public function __construct(){
		$this->setActionPrefix(null);
	}

	public function getReferrer(){
		UserAccess::assertCurrentUser('referrer.self');
		$refer = new Referrer($this->currentUser);

		$this->vars['title'] = 'Партнёрская программа';
		$this->vars['userReferrer'] = $refer->getReferrer();
		$this->vars['userReferrals'] = $refer->getReferrals();
		$this->vars['userRefLink'] = $refer->getReferalURI();
		$this->vars['userRefStatLink'] = $refer->getReferalStatisticURI();
	}

}