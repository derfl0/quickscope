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
        $course = Course::find($course_id);
        $sem = new Seminar($course);

        $result['avatar'] = CourseAvatar::getAvatar($course->id)->getURL(Avatar::MEDIUM);
        $result['header'] = $course->getFullname();
        $result['text'][] = join(', ', array_map(function($obj) {
                    return $obj->user->getFullname();
                }, $course->getMembersWithStatus('dozent')));
        $result['text'][] = $sem->getDatesHTML();

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
                $result['error'][] = sprintf(_('%s Überschneidungen mit %s'), $data['collisions'], $course->getFullname());
            }

            // Check if you are member
            if ($semuser = $course->members->findOneBy('user_id', User::findCurrent()->id)) {
                $result['important'][] = sprintf(_('Sie sind %s in dieser Veranstaltung'), get_title_for_status($semuser->status, 1));
            } else {
                $result['action'][] = array(
                    'label' => _('In Veranstaltung eintragen'),
                    'icon' => Assets::image_path('/images/icons/16/blue/door-enter.png'),
                    'url' => URLHelper::getURL('dispatch.php/course/enrolment/apply/' . $course_id),
                    'type' => 'dialog'
                );

                $result['action'][] = array(
                    'label' => _('Im Stundenplan vormerken'),
                    'icon' => Assets::image_path('/images/icons/16/blue/info.png'),
                    'url' => URLHelper::getURL('dispatch.php/calendar/schedule/addvirtual/' . $course_id)
                );
            }
        }
        
        $this->render_json($result);
    }

    public function user_action($username) {
        $user = User::findByUsername($username);
        $result['avatar'] = Avatar::getAvatar($user->id)->getURL(Avatar::MEDIUM);
        $result['header'] = $user->getFullName();
        $result['text'][] = $user->motto;
        $result['text'][] = $user->Email;
        $result['action'][] = array(
            'label' => _('Kontakt hinzufügen'),
            'icon' => Assets::image_path('/images/icons/16/blue/person.png'),
            'url' => URLHelper::getURL('dispatch.php/profile/add_buddy', array('username' => $username))
        );

        $result['action'][] = array(
            'label' => _('Studip Nachricht'),
            'icon' => Assets::image_path('/images/icons/16/blue/mail.png'),
            'url' => URLHelper::getURL('dispatch.php/messages/write', array('username' => $username, 'rec_uname' => $username)),
            'type' => 'dialog'
        );

        $this->render_json($result);
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
