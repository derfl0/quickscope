<?php
require 'bootstrap.php';

/**
 * QuickscopePlugin.class.php
 *
 * ...
 *
 * @author  Florian Bieringer <florian.bieringer@uni-passau.de>
 * @version 1.0
 */

class QuickscopePlugin extends StudIPPlugin implements SystemPlugin {

    public function __construct() {
        parent::__construct();
        PageLayout::addScript($this->getPluginURL().'/assets/quickscope.js');
        PageLayout::addScript(Assets::javascript_path('enrolment.js'));
        self::addStylesheet('/assets/quickscope.less');
    }

    public function initialize () {    
    }

    public function perform($unconsumed_path)
    {
        $this->setupAutoload();
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, array(), null), '/'),
            'show'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }

    private function setupAutoload()
    {
        if (class_exists('StudipAutoloader')) {
            StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
        } else {
            spl_autoload_register(function ($class) {
                include_once __DIR__ . $class . '.php';
            });
        }
    }
}
