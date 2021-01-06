<?php
/**
 * Интеграция с платежной системой Payssion
 * @link https://www.payssion.com/ru/index.html
 * @link https://github.com/payssion/payssion-php/
 */
namespace tsframe;

use tsframe\Http;
use tsframe\PluginInstaller;
use tsframe\module\PayssionModule;
use tsframe\module\user\Cash;
use tsframe\module\user\SingleUser;
use tsframe\view\Template;
use tsframe\view\TemplateRoot;


Hook::registerOnce('plugin.load', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::registerOnce('plugin.install', function(){
	return [
		PluginInstaller::withKey('payssion.api_key')
						->setType('text')
						->setDescription("Payssion API key")
						->setRequired(true),

		PluginInstaller::withKey('payssion.secret_key')
						->setType('text')
						->setDescription("Payssion secrey key")
						->setRequired(true),

		PluginInstaller::withKey('payssion.production_mode')
						->setType('select')
						->setDescription("Production mode")
						->setValues([0 => "Disabled / Test mode", 1 => "Enabled / Production mode"])
						->setRequired(true),

		PluginInstaller::withKey('payssion.payment_types')
						->setType('text')
						->setDescription("Payment methods (comma separated words) from <a href='https://payssion.com/en/docs/#api-reference-pm-id' target='_blank'>here (pm_id values)</a>.")
						->setRequired(true),

		PluginInstaller::withKey('payssion.set-notify-helper')
						->setType('helper-text')
						->setDescription("Set application <b>Notify URL</b> as: ")
						->setDefaultValue(Http::makeURI('/payssion/notify')),

		PluginInstaller::withKey('payssion.set-return-helper')
						->setType('helper-text')
						->setDescription("Set application <b>Return URL</b> as: ")
						->setDefaultValue(Http::makeURI('/payssion/return')),
	];
});


Hook::register('template.dashboard.user.edit.balance', function(Template $tpl, SingleUser $selectUser){
	$tpl->var('currency', Cash::getCurrency());
	$tpl->var('payssionTypes', PayssionModule::getPaymentTypes());
	$tpl->var('payssionDescription', PayssionModule::$payDescription);
	$tpl->inc('payssion-form');
});