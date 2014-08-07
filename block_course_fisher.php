<?php

defined('MOODLE_INTERNAL') || die();

require_once('locallib.php');
require_once('backendlib.php');

class block_course_fisher extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_course_fisher');
    }

    function get_content() {
        global $CFG, $OUTPUT, $USER;

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $this->config = new stdClass();
        $this->config->teacherid = 'idnumber';

        $this->config->backend = 'db';

        if (file_exists($CFG->dirroot.'/blocks/course_fisher/backend/'.$this->config->backend.'/lib.php')) {
            include_once($CFG->dirroot.'/blocks/course_fisher/backend/'.$this->config->backend.'/lib.php');

            $backendclassname = 'block_course_fisher_backend_'.$this->config->backend;
            if (class_exists($backendclassname)) {

                $backend = new $backendclassname();

                $teachercourses = $backend->get_teacher_courses($USER->{$this->config->teacherid});

                if (!empty($teachercourses)) {
                    $icon = $OUTPUT->pix_icon('i/course', 'icon');
                    $this->content->items[] = '<a href="'.$CFG->wwwroot.'/blocks/course_fisher/guide.php?id='.$teachercourse->id.'">'.$icon.get_string('courseguides', 'block_course_fisher').'</a>';
                    $icon = $OUTPUT->pix_icon('book', 'icon');
                    $this->content->items[] = '<a href="'.$CFG->wwwroot.'/blocks/course_fisher/register.php?id='.$teachercourse->id.'">'.$icon.get_string('courseregisters', 'block_course_fisher').'</a>';
                    $icon = $OUTPUT->pix_icon('t/add', 'icon');
                    $this->content->items[] = '<a href="'.$CFG->wwwroot.'/blocks/course_fisher/addcourse.php?id='.$teachercourse->id.'">'.$icon.get_string('addmoodlecourse', 'block_course_fisher').'</a>';
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

    function has_config() {
        return true;
    }


}


