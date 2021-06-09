<?php
/**
 * База данных стран, регионов и городов
 * + GeoIP запросы
 */
namespace tsframe;

use tsframe\module\Geo\GeoIP;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.load', function(){
    TemplateRoot::add('geodata', __DIR__ . DS . 'template');   
});