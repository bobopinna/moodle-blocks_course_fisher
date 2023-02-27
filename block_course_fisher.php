<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Course fisher
 *
 * @package    blocks
 * @subpackage course_fisher
 * @copyright  2014 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
        return array(
            'site-index' => true,
            'my' => true);
    }

    public function instance_allow_multiple() {
         return false;
    }

    public function instance_can_be_collapsed() {
        return (parent::instance_can_be_collapsed() && (empty($this->config->enablecollaps) || $this->config->enablecollaps=='yes'));
    }

    public function instance_can_be_docked() {
        return (parent::instance_can_be_docked() && (empty($this->config->enabledock) || $this->config->enabledock=='yes'));
    }

    function user_can_addto($page) {
        // Don't allow people to add the block if they can't even use it
        if (!has_capability('block/course_fisher:addallcourses', $page->context)) {
            return false;
        }
        return parent::user_can_addto($page);
    }

    function user_can_edit() {
        // Don't allow people to edit the block if they can't even use it
        if (!has_capability('block/course_fisher:addallcourses', $this->context)) {
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

        if (!isloggedin() || isguestuser()) {
           return $this->content;
        }

        if (has_capability('block/course_fisher:addallcourses', $this->context)) {

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

        if ($this->enabled_user()) {
            $icon = $OUTPUT->pix_icon('t/add', 'icon');
            $url = new moodle_url('/blocks/course_fisher/addcourse.php', array());
            $this->content->items[] = html_writer::tag('a', $icon.get_string('addmoodlecourse', 'block_course_fisher'), array('href' => $url));
            if (isset($CFG->block_course_fisher_course_helplink) && !empty($CFG->block_course_fisher_course_helplink)) {
                $icon = $OUTPUT->pix_icon('help', 'icon');
                $url = new moodle_url($CFG->block_course_fisher_course_helplink, array());
                $this->content->items[] = html_writer::tag('a', $icon.get_string('help'), array('href' => $url));
            }
        }

        return $this->content;
    }

    private function enabled_user() {
        global $USER, $DB;

        $enabled = false;
        $filterconfigured = (isset($this->config->userfield) && !empty($this->config->userfield));
        if ($filterconfigured && !has_capability('block/course_fisher:addallcourses', $this->context)) {

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
                        if (mb_ereg($this->config->matchvalue.'$', $userfieldvalue) !== false) {
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
}
