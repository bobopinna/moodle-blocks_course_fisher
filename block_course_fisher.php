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
        return array('site' => true,
                      'mod' => false);
    }

    public function instance_allow_multiple() {
      return false;
    }
   
    function user_can_addto($page) {
        // Don't allow people to add the block if they can't even use it
        if (!is_siteadmin()) {
            return false;
        }
        return parent::user_can_addto($page);
    }

    function user_can_edit() {
        // Don't allow people to edit the block if they can't even use it
        if (!is_siteadmin()) {
            return false;
        }
        return parent::user_can_edit();
    }
 
    function specialization() {
        $this->title = (isset($this->config->title) && !empty($this->config->title)) ? format_string($this->config->title) : $this->title;
    }

    function get_content() {
        global $CFG, $USER, $OUTPUT, $DB;

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        if (is_siteadmin()) {

            if (isset($this->config->userfield) && !empty($this->config->userfield)) {
                $userfieldname = '';
                $customfields = $DB->get_records('user_info_field');
                if (!empty($customfields)) {
                    foreach($customfields as $customfield) {
                        if ($customfield->shortname == $this->config->userfield) {
                            $userfieldname = $customfield->name;
                        }
                    }
                }
                if (empty($userfieldname)) {
                    if (isset($USER->{$this->config->userfield})) {
                        $userfieldname = get_string($this->config->userfield);
                    } else {
                        $userfieldname = $this->config->userfield;
                    }
                }

                $footers = array();
                $footers[] = get_string($this->config->display, 'block_course_fisher');
                $footers[] = get_string('ifuserprofilefield', 'block_course_fisher');
                $footers[] = $userfieldname;
                $footers[] = get_string($this->config->operator, 'filters');
                $footers[] = '"'.format_string($this->config->matchvalue).'"';
                $this->content->footer = implode(' ', $footers);
            } else {
                $this->content->footer = html_writer::tag('span', get_string('nouserfilterset', 'block_course_fisher'), array('class' => 'block_course_fisher_permission'));
            }
        }

        if (!isloggedin()) {
           return $this->content;
        }

        if (file_exists($CFG->dirroot.'/blocks/course_fisher/backend/'.$CFG->block_course_fisher_backend.'/lib.php')) {
            require_once($CFG->dirroot.'/blocks/course_fisher/backend/'.$CFG->block_course_fisher_backend.'/lib.php');

            $backendclassname = 'block_course_fisher_backend_'.$CFG->block_course_fisher_backend;
            if (class_exists($backendclassname)) {

                $backend = new $backendclassname();

                if (is_siteadmin() || $this->enabled_user()) {

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
        }

        return $this->content;
    }

    private function enabled_user() {
        global $USER, $DB;

        $enabled = false;
        if (isset($this->config->userfield) && !empty($this->config->userfield)) {
            if (isset($this->config->matchvalue) && !empty($this->config->matchvalue)) {
           
                $userfieldvalue = '';
                $customfields = $DB->get_records('user_info_field');
                if (!empty($customfields)) {
                    foreach($customfields as $customfield) {
                        if ($customfield->shortname == $this->config->userfield) {
                            if (isset($USER->profile[$customfield->shortname]) && !empty($USER->profile[$customfield->shortname])) {
                                $userfieldvalue = $USER->profile[$customfield->shortname];
                            }
                        }
                    }
                }
                if (empty($userfieldvalue)) {
                    if (isset($USER->{$this->config->userfield})) {
                        $userfieldvalue = $USER->{$this->config->userfield};
                    }
                }

                switch ($this->config->operator) {
                    case 'contains':
                        if (mb_strpos($userfieldvalue, $this->config->matchvalue) !== false) {
                            $enabled = true;
                        }
                    break;
                    case 'doesnotcontains':
                        if (mb_strpos($userfieldvalue, $this->config->matchvalue) === false) {
                            $enabled = true;
                        }
                    break;
                    case 'isequalto':
                        if ($this->config->matchvalue == $userfieldvalue) {
                            $enabled = true;
                        }
                    break;
                    case 'isnotequalto':
                        if ($this->config->matchvalue != $userfieldvalue) {
                            $enabled = true;
                        }
                    break;
                    case 'startswith':
                        if (mb_ereg_match('^'.$this->config->matchvalue, $userfieldvalue) !== false) {
                            $enabled = true;
                        }
                    break;
                    case 'endswith':
                        if (mb_ereg($this->config->matchvalue.'$', $userfield) !== false) {
                            $enabled = true;
                        }
                    break;
                }
                if (isset($this->config->display) && ($this->config->display == 'hidden')) {
                    $enabled = !$enabled;
                }
            }
        } else {
            $enabled = true;
        }

        return $enabled;
    }    

    public function cron() {
        global $CFG, $DB;
 
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
