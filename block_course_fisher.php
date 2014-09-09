<?php

defined('MOODLE_INTERNAL') || die();

require_once('locallib.php');
require_once('backendlib.php');

class block_course_fisher extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_course_fisher');
    }

    function get_content() {
        global $CFG, $USER, $OUTPUT;

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (file_exists($CFG->dirroot.'/blocks/course_fisher/backend/'.$CFG->block_course_fisher_backend.'/lib.php')) {
            require_once($CFG->dirroot.'/blocks/course_fisher/backend/'.$CFG->block_course_fisher_backend.'/lib.php');

            $backendclassname = 'block_course_fisher_backend_'.$CFG->block_course_fisher_backend;
            if (class_exists($backendclassname)) {

                $backend = new $backendclassname();

                $teachercourses = $backend->get_data();

                if (!empty($teachercourses)) {
                    if (file_exists($CFG->dirroot."/blocks/course_fisher/guide.php")) {
                        $icon = $OUTPUT->pix_icon('i/course', 'icon');
                        $url =  new moodle_url('/blocks/course_fisher/guide.php', array('id' => $USER->id));
                        $this->content->items[] = html_writer::tag('a', $icon.get_string('courseguides', 'block_course_fisher'), array('href' => $url));
                    }
                    if (file_exists($CFG->dirroot."/blocks/course_fisher/register.php")) {
                        $icon = $OUTPUT->pix_icon('i/grades', 'icon');
                        $url = new moodle_url('/blocks/course_fisher/register.php', array('id' => $USER->id));
                        $this->content->items[] = html_writer::tag('a', $icon.get_string('courseregisters', 'block_course_fisher'), array('href' => $url));
                    }
                    $icon = $OUTPUT->pix_icon('t/add', 'icon');
                    $url = new moodle_url('/blocks/course_fisher/addcourse.php', array('id' => $USER->id));
                    $this->content->items[] = html_writer::tag('a', $icon.get_string('addmoodlecourse', 'block_course_fisher'), array('href' => $url));
                }
                if (isset($CFG->block_course_fisher_course_helplink) && !empty($CFG->block_course_fisher_course_helplink)) {
                    $icon = $OUTPUT->pix_icon('help', 'icon');
                    $url = new moodle_url($CFG->block_course_fisher_course_helplink, array());
                    $this->content->items[] = html_writer::tag('a', $icon.get_string('help'), array('href' => $url));
                }
                if ($teachercourses === false) {
                    $this->content->footer .= get_string('backendfailure', 'block_course_fisher');
                }
            }
        }

        return $this->content;
    }

    /**
     * Returns the role that best describes this blocks contents.
     *
     * This returns 'navigation' as the blocks contents is a list of links to activities and resources.
     *
     * @return string 'navigation'
     */
    public function get_aria_role() {
        return 'navigation';
    }

    function applicable_formats() {
        return array('site' => true, 'mod' => false, 'my' => false, 'admin' => false,
                     'tag' => false);
    }

    public function instance_allow_multiple() 
    {
      return false;
    }
    
    
    function has_config() {
        return true;
    }


}
