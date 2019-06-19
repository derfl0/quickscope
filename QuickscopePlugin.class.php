<?php

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
        PageLayout::addScript($this->getPluginURL().'/assets/quickscope.min.js');
        PageLayout::addScript('studip-enrolment.js');
        self::addStylesheet('/assets/quickscope.less');
    }

    public function initialize () {    
    }

    public function perform($unconsumed_path)
    {
        StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, array(), null), '/'),
            'show'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }

}
