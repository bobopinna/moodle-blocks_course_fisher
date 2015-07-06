<?php

defined('MOODLE_INTERNAL') || die();

require_once('locallib.php');
require_once('backendlib.php');

class block_course_fisher extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_course_fisher');
    }

    function has_config() {
        return true;
    }

    function applicable_formats() {
        return array('site' => true, 'mod' => false, 'my' => false, 'admin' => false,
                     'tag' => false);
    }

    public function instance_allow_multiple() {
      return false;
    }
    
    function specialization() {
        $this->title = isset($this->config->title) ? format_string($this->config->title) : $this->title;
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

        if (!isloggedin()) {
           return $this->content;
        }

        if (file_exists($CFG->dirroot.'/blocks/course_fisher/backend/'.$CFG->block_course_fisher_backend.'/lib.php')) {
            require_once($CFG->dirroot.'/blocks/course_fisher/backend/'.$CFG->block_course_fisher_backend.'/lib.php');

            $backendclassname = 'block_course_fisher_backend_'.$CFG->block_course_fisher_backend;
            if (class_exists($backendclassname)) {

                $backend = new $backendclassname();

                $teachercourses = $backend->get_data(is_siteadmin());

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
    

    public function cron() {
        global $CFG;
 
        if (file_exists($CFG->dirroot.'/blocks/course_fisher/backend/'.$CFG->block_course_fisher_backend.'/lib.php')) {
            require_once($CFG->dirroot.'/blocks/course_fisher/backend/'.$CFG->block_course_fisher_backend.'/lib.php');
            $backendclassname = 'block_course_fisher_backend_'.$CFG->block_course_fisher_backend;
            if (class_exists($backendclassname)) {
                $backend = new $backendclassname();
	        if (method_exists($backend,'cron')) {
                    mtrace('Processing backend '.$CFG->block_course_fisher_backend.' cron...');
	            $backend->cron();
                    mtrace('done.');
                }

                if (isset($CFG->block_course_fisher_autocreation) && !empty($CFG->block_course_fisher_autocreation)) {
                    mtrace('Processing course autocreation...');
                    $teachercourses = $backend->get_data(true);

                    if (!empty($teachercourses)) {
                        foreach($teachercourses as $teachercourse) {
                            $course = null;
                            $coursecode = '';
                            $courseshortname = '';
                            if (isset($CFG->block_course_fisher_course_code) && !empty($CFG->block_course_fisher_course_code)) {
                                $coursecode = block_course_fisher_format_fields($CFG->block_course_fisher_course_code, $teachercourse);
                                $course = $DB->get_record('course', array('idnumber' => $coursecode));
                            } else {
                                $courseshortname = block_course_fisher_format_fields($CFG->block_course_fisher_course_shortname, $teachercourse);
                                $course = $DB->get_record('course', array('shortname' => $courseshortname));
                            }
                            if (! $course) {
                                $courseshortname = block_course_fisher_format_fields($CFG->block_course_fisher_course_shortname, $teachercourse);
                                $categories = array_filter(explode("\n", block_course_fisher_format_fields($CFG->block_course_fisher_fieldlevel, $teachercourse)));
                                $categoriesdescriptions = block_course_fisher_get_fields_description($categories);
                                $coursepath = implode(' / ', $categoriesdescriptions);
                                $coursefullname = block_course_fisher_format_fields($CFG->block_course_fisher_course_fullname, $teachercourse);
        
                                $coursecode = block_course_fisher_format_fields($CFG->block_course_fisher_course_code, $teachercourse);
                                if (! $newcourse = block_course_fisher_create_course($coursefullname, $courseshortname, $coursecode, 0, block_course_fisher_get_fields_items($categories))) {
                                     notice(get_string('coursecreationerror', 'block_course_fisher'));
                                } else {
                                     mtrace('... added course'.$coursefullname.' - '.$courseshortname.' - '.$coursecode);
                                }
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

}
