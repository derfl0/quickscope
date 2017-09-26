<?php

class ShowController extends StudipController
{

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        $this->set_layout(null);
    }

    public function index_action()
    {
        $this->answer = 'Yes';
    }

    public function course_action($course_id)
    {
        $course = Course::find($course_id);
        $sem = new Seminar($course);

        $result['avatar'] = CourseAvatar::getAvatar($course->id)->getURL(Avatar::MEDIUM);
        $result['header'] = $course->getFullname();
        $result['text'][] = join(', ', array_map(function ($obj) {
            return $obj->user->getFullname();
        }, $course->getMembersWithStatus('dozent')));
        $result['text'][] = $sem->getDatesHTML();

        // check if we are in that course otherwise query collisions
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $courseMember = CourseMember::findOneBySQL('seminar_id = ? AND user_id = ?', array($course_id, User::findCurrent()->id));
            $deputy = isDeputy(User::findCurrent()->id, $course_id);
            if (!$courseMember && !$deputy) {
                $collisionStmt = DBManager::get()->prepare("SELECT seminare.*, count(seminare.`Seminar_id`) as collisions FROM seminare JOIN seminar_user USING (seminar_id) JOIN termine ON (termine.range_id = seminare.seminar_id)
    JOIN termine compare ON ((termine.date + 1 BETWEEN compare.date AND compare.end_time) OR (termine.end_time - 1 BETWEEN compare.date AND compare.end_time))
    WHERE user_id = ?
    AND compare.range_id = ?
    GROUP BY seminar_id");
                $collisionStmt->execute(array(User::findCurrent()->id, $course_id));
                while ($data = $collisionStmt->fetch(PDO::FETCH_ASSOC)) {
                    $course = Course::import($data);
                    $result['error'][] = sprintf(_('%s Überschneidungen mit %s'), $data['collisions'], $course->getFullname());
                }

                $result['action'][] = array(
                    'label' => _('In Veranstaltung eintragen'),
                    'icon' => Icon::create('door-enter', 'clickable')->asImagePath(),
                    'url' => URLHelper::getURL('dispatch.php/course/enrolment/apply/' . $course_id),
                    'type' => 'dialog'
                );

                $result['action'][] = array(
                    'label' => _('Im Stundenplan vormerken'),
                    'icon' => Icon::create('info', 'clickable')->asImagePath(),
                    'url' => URLHelper::getURL('dispatch.php/calendar/schedule/addvirtual/' . $course_id)
                );
            } else if ($deputy) {
                $result['important'][] = sprintf(_('Sie sind Vertretung in dieser Veranstaltung.'));
            } else {
                $result['important'][] = sprintf(_('Sie sind %s in dieser Veranstaltung.'), get_title_for_status($courseMember->status, 1));
            }
        }

        $this->render_json($result);
    }

    public function user_action($username)
    {
        $user = User::findByUsername($username);
        $result['avatar'] = Avatar::getAvatar($user->id)->getURL(Avatar::MEDIUM);
        $result['header'] = $user->getFullName();
        $result['text'][] = $user->motto;
        $result['text'][] = get_visible_email($user->id);
        if (get_visibility_by_id($user->id) || $GLOBALS['perm']->have_perm('root')) {
            $result['action'][] = array(
                'label' => _('Kontakt hinzufügen'),
                'icon' => Icon::create('person', 'clickable')->asImagePath(),
                'url' => URLHelper::getURL('dispatch.php/profile/add_buddy', array('username' => $username))
            );
        }
        $result['action'][] = array(
            'label' => _('Stud.IP-Nachricht schicken'),
            'icon' => Icon::create('mail', 'clickable')->asImagePath(),
            'url' => URLHelper::getURL('dispatch.php/messages/write', array('username' => $username, 'rec_uname' => $username)),
            'type' => 'dialog'
        );

        $this->render_json($result);
    }

    public function file_action($file_id)
    {
        if (class_exists('imagick')) {
            $file = StudipDocument::find($file_id);
            $file->checkAccess($GLOBALS['user']->id);

            // check cache
            $cache = StudipCacheFactory::getCache();
            $cachedIm = $cache->read('quickscope' . $file->id);

            if (!$cachedIm) {
                $filename = $GLOBALS['UPLOAD_PATH'] . '/' . substr($file->dokument_id, 0, 2) . '/' . $file->dokument_id;
                try {
                    $im = new imagick();
                    $im->readImage($filename . '[0]');
                    $im->setImageBackgroundColor('white');
                    $im = $im->flattenImages();
                    /* create the thumbnail */
                    $im->setImageFormat('jpg');
                    $im->setImageCompressionQuality(90);
                    $im->scaleImage(250, 250, true);
                    $imageTag = "<img src='data:image/jpg;base64," . base64_encode($im->getImageBlob()) . "' />";
                    $cache->write('quickscope' . $file->id, $imageTag);
                    $result['text'][] = $imageTag;

                } catch (ImagickException $e) {

                }
            } else {
                $result['text'][] = $cachedIm;
            }
        }
        $this->render_json($result);
    }

    // customized #url_for for plugins
    function url_for($to = '')
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
