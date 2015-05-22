<?php

class ShowController extends StudipController {

    public function __construct($dispatcher) {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
    }

    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);
        $this->set_layout(null);
        $this->set_content_type('text/html;Charset=windows-1252');
        //$this->set_layout($GLOBALS['template_factory']->open('layouts/base_without_infobox'));
    }

    public function index_action() {
        $this->answer = 'Yes';
    }

    public function course_action($course_id) {
        $this->course = Course::find($course_id);
        $this->sem = new Seminar($this->course);

        // check if we are in that course otherwise query collisions
        if (!CourseMember::exists($course_id, User::findCurrent()->id)) {
            $collisionStmt = DBManager::get()->prepare("SELECT seminare.*, count(*) as collisions FROM seminare JOIN seminar_user USING (seminar_id) JOIN termine ON (termine.range_id = seminare.seminar_id) 
JOIN termine compare ON ((termine.date + 1 BETWEEN compare.date AND compare.end_time) OR (termine.end_time - 1 BETWEEN compare.date AND compare.end_time)) 
WHERE user_id = ? 
AND compare.range_id = ? 
GROUP BY seminar_id");
            $collisionStmt->execute(array(User::findCurrent()->id, $course_id));
            while ($data = $collisionStmt->fetch(PDO::FETCH_ASSOC)) {
                $course = Course::import($data);
                $this->collisions[$course->getFullname()] = $data['collisions'];
            }
        }
    }

    public function user_action($username) {
        $this->user = User::findByUsername($username);
    }

    // customized #url_for for plugins
    function url_for($to) {
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
