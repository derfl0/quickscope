<?php
class ShowController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        $this->set_layout(null);
        $this->set_content_type('text/html;Charset=windows-1252');
        //$this->set_layout($GLOBALS['template_factory']->open('layouts/base_without_infobox'));
    }

    public function index_action()
    {
        $this->answer = 'Yes';
    }
    
    public function course_action($course_id) {
        $this->course = Course::find($course_id);
    }

    // customized #url_for for plugins
    function url_for($to)
    {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->plugin, $params, join('/', $args));
    } 
}
