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
 * @copyright 2014 and above Roberto Pinna, Diego Fantoma, Angelo Calò
 * @copyright 2016 and above Francesco Carbone
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once('locallib.php');
require_once('backendlib.php');
require_once('preferences_form.php');

$courseid = optional_param('courseid', '', PARAM_ALPHANUM);
$action = optional_param('action', '', PARAM_ALPHANUM);
$existent = optional_param('existent', '', PARAM_ALPHANUM);

if (isset($CFG->block_course_fisher_actions) && !empty($CFG->block_course_fisher_actions) && !in_array($action, explode(',', $CFG->block_course_fisher_actions))) {
    $action = '';
} elseif ((!isset($CFG->block_course_fisher_actions) || empty($CFG->block_course_fisher_actions)) && ($action != 'view')) {
    $action = '';
}

$urlquery = array();
if (!empty($courseid)) {
    $urlquery['courseid'] = $courseid;
}

$url = new moodle_url('/blocks/course_fisher/addcourse.php', $urlquery);

$PAGE->set_url($url);
require_login();

$systemcontext = context_system::instance();
require_capability('block/course_fisher:addcourses', $systemcontext);

if (! $user = $DB->get_record('user', array('id' => $USER->id)) ) {
    error("No such user");
}


$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('incourse');

$fullname = fullname($user, has_capability('moodle/site:viewfullnames', $systemcontext));

/// Print the page header
$straddcourse = get_string('addmoodlecourse', 'block_course_fisher');

$PAGE->set_title($straddcourse);
$PAGE->navbar->add($straddcourse);
$PAGE->set_heading($straddcourse);

if (file_exists($CFG->dirroot.'/blocks/course_fisher/backend/'.$CFG->block_course_fisher_backend.'/lib.php')) {
    require_once($CFG->dirroot.'/blocks/course_fisher/backend/'.$CFG->block_course_fisher_backend.'/lib.php');

    $backendclassname = 'block_course_fisher_backend_'.$CFG->block_course_fisher_backend;

    if (class_exists($backendclassname)) {

        $backend = new $backendclassname();

        $teachercourses = block_course_fisher_get_coursehashes($backend->get_data(has_capability('block/course_fisher:addallcourses', $systemcontext)));

        if (!empty($teachercourses)) {
            $categorieslist = coursecat::make_categories_list();
            if (empty($courseid)) {
                echo $OUTPUT->header();
                echo html_writer::start_tag('div', array('class' => 'teachercourses'));

                $availablecourses = array();
                $existentcourses = '';
                foreach ($teachercourses as $coursehash => $teachercourse) {

                    $course = null;
                    $courseidnumber = '';
                    $courseshortname = block_course_fisher_format_fields($CFG->block_course_fisher_course_shortname, $teachercourse);
                    if (isset($CFG->block_course_fisher_course_code) && !empty($CFG->block_course_fisher_course_code)) {
                        $courseidnumber = block_course_fisher_format_fields($CFG->block_course_fisher_course_code, $teachercourse);
                        $course = $DB->get_record('course', array('idnumber' => $courseidnumber));
                    } else {
                        $course = $DB->get_record('course', array('shortname' => $courseshortname));
                    }

                    $coursegroup = '';
                    $groupcourses = block_course_fisher_get_groupcourses($teachercourses, $coursehash, $teachercourse);
                    if (count($groupcourses) > 1) {
                        reset($groupcourses);
                        $coursegroup = key($groupcourses);
                    }

                    if (! $course) {
                        $coursecode = !empty($courseidnumber)?$courseidnumber:$courseshortname;

                        $fieldlist = block_course_fisher_format_fields($CFG->block_course_fisher_fieldlevel, $teachercourse);
                        $categories = block_course_fisher_get_fields_description(array_filter(explode("\n", $fieldlist)));
                        $coursepath = implode(' / ', $categories);
                        $coursefullname = block_course_fisher_format_fields($CFG->block_course_fisher_course_fullname, $teachercourse);

                        $addcourseurl = new moodle_url('/blocks/course_fisher/addcourse.php', array('courseid' => $coursehash));
                        $link = html_writer::tag('a', get_string('addcourse', 'block_course_fisher'), array('href' => $addcourseurl, 'class' => 'addcourselink'));
                        $coursecategories = html_writer::tag('span', $coursepath, array('class' => 'addcoursecategory'));
                        $coursename = html_writer::tag('span', $coursefullname, array('class' => 'addcoursename'));
                        if (has_capability('block/course_fisher:addallcourses', $systemcontext)) {
                           $coursecodes = html_writer::tag('span', $coursecode.$courseshortname, array('class' => 'addcoursecode'));
                           //$availablecourses[$coursehash] = html_writer::tag('li', $link.$coursename.$coursecategories.$coursecodes, array('class' => 'addcourseitem'));
                           $availablecourses[$coursegroup][$coursehash] = html_writer::tag('li', $link.$coursename.$coursecategories.$coursecodes, array('class' => 'addcourseitem'));
                        } else {
                           //$availablecourses[$coursehash] = html_writer::tag('li', $link.$coursename.$coursecategories, array('class' => 'addcourseitem'));
                           $availablecourses[$coursegroup][$coursehash] = html_writer::tag('li', $link.$coursename.$coursecategories, array('class' => 'addcourseitem'));
                        }
                    } else {
                        $coursecode = isset($course->idnumber) && !empty($course->idnumber)?$course->idnumber:$course->shortname;
                        $link = '';

                        $isalreadyteacher = is_enrolled(context_course::instance($course->id), $user, 'moodle/course:update', true);
                        $canaddall = has_capability('block/course_fisher:addallcourses', $systemcontext);
                        if (!$isalreadyteacher && !$canaddall) {
                            //$coursehash = md5($categorieslist[$course->category].' / '.$coursecode);
                            $courseurl = new moodle_url('/blocks/course_fisher/addcourse.php', array('courseid' => $coursehash, 'action' => 'view'));
                            $link = html_writer::tag('a', get_string('enroltocourse', 'block_course_fisher'), array('href' => $courseurl, 'class' => 'enroltocourselink'));
                            $coursecategories = html_writer::tag('span', $categorieslist[$course->category], array('class' => 'enroltocoursecategory'));
                            $coursename = html_writer::tag('span', $course->fullname, array('class' => 'enroltocoursename'));
                            //$existentcourses[$coursehash] = html_writer::tag('li', $link.$coursename.$coursecategories, array('class' => 'enroltocourseitem'));
                            $existentcourses[$coursegroup][$coursehash] = html_writer::tag('li', $link.$coursename.$coursecategories, array('class' => 'enroltocourseitem'));
                        }
                    }
                }
                if (!empty($availablecourses)) {
                    echo html_writer::tag('h1', get_string('availablecourses', 'block_course_fisher'), array());
                    //echo implode("\n", $availablecourses);
                    foreach ($availablecourses as $coursegroup => $availablegroupelements) {
                        echo html_writer::start_tag('ul', array('class' => 'availablecourses'));
                        if (count($availablegroupelements) > 1) {
                            if (!empty($coursegroup)) {
                                echo html_writer::start_tag('li', array('class' => 'availablecourses coursegroup'));
                                echo html_writer::tag('span', get_string('coursegroup', 'block_course_fisher'), array('class' => 'coursegrouptitle'));
                                echo html_writer::start_tag('ul', array('class' => 'availablecourses'));
                                echo implode("\n", $availablegroupelements);
                                echo html_writer::end_tag('ul');
                                echo html_writer::end_tag('li');
                            } else {
                                echo implode("\n", $availablegroupelements);
                            }
                        } else {
                            echo current($availablegroupelements);
                        }
                        echo html_writer::end_tag('ul');
                    }
                }
                if (!empty($existentcourses)) {
                    echo html_writer::tag('h1', get_string('existentcourses', 'block_course_fisher'), array());
                    //echo implode("\n", $existentcourses);
                    foreach ($existentcourses as $coursegroup => $existentgroupelements) {
                        echo html_writer::start_tag('ul', array('class' => 'existentcourses'));
                        if (count($existentgroupelements) > 1) {
                            if (!empty($coursegroup)) {
                                echo html_writer::start_tag('li', array('class' => 'existentcourses coursegroup'));
                                echo html_writer::tag('span', get_string('coursegroup', 'block_course_fisher'), array('class' => 'coursegrouptitle'));
                                echo html_writer::start_tag('ul', array('class' => 'existentcourses'));
                                echo implode("\n", $existentgroupelements);
                                echo html_writer::end_tag('ul');
                                echo html_writer::end_tag('li');
                            } else {
                                echo implode("\n", $existentgroupelements);
                            }
                        } else {
                            echo current($existentgroupelements);
                        }
                        echo html_writer::end_tag('ul');
                    }
                }
                if (empty($availablecourses) && empty($existentcourses)) {
                    notice(get_string('nocourseavailable', 'block_course_fisher'), new moodle_url('/index.php'));
                }

                echo html_writer::end_tag('div');
                echo $OUTPUT->footer();
            } else {
                $coursehashes = str_split($courseid, strlen(md5('coursehash')));
                $metacourseids = array();
                $firstcourse = null;
                $groupcourses = array();
                foreach ($coursehashes as $coursehash) {
                    if (isset($teachercourses[$coursehash])) {
                        $hashcourse = $teachercourses[$coursehash];

                        $coursedata = new stdClass();
                        $coursedata->idnumber = '';
                        $coursedata->shortname = block_course_fisher_format_fields($CFG->block_course_fisher_course_shortname, $hashcourse);
                        if (isset($CFG->block_course_fisher_course_code) && !empty($CFG->block_course_fisher_course_code)) {
                            $coursedata->idnumber = block_course_fisher_format_fields($CFG->block_course_fisher_course_code, $hashcourse);
                        }
                        $coursedata->code = !empty($coursedata->idnumber)?$coursedata->idnumber:$coursedata->shortname;
                        $categories = array_filter(explode("\n", block_course_fisher_format_fields($CFG->block_course_fisher_fieldlevel, $hashcourse)));
                        $categoriesdescriptions = block_course_fisher_get_fields_description($categories);
                        $coursedata->path = implode(' / ', $categoriesdescriptions);
                        $coursedata->fullname = block_course_fisher_format_fields($CFG->block_course_fisher_course_fullname, $hashcourse);

                        $userid = $USER->id;
                        if (has_capability('block/course_fisher:addallcourses', $systemcontext)) {
                            $userid = null;
                        }
                        if (!empty($action)) { 
                            /* Create course */
                            $coursedata->summary = '';
                            if (isset($CFG->block_course_fisher_course_summary) && !empty($CFG->block_course_fisher_course_summary)) {
                                $coursedata->summary = block_course_fisher_format_fields($CFG->block_course_fisher_course_summary, $hashcourse);
                            }
                            
                            $coursedata->sectionzero = '';
                            if (isset($CFG->block_course_fisher_sectionzero_name) && !empty($CFG->block_course_fisher_sectionzero_name)) {
                                $coursedata->sectionzero = block_course_fisher_format_fields($CFG->block_course_fisher_sectionzero_name, $hashcourse);
                            }
                            
                            $coursedata->educationalofferurl = '';
                            if (isset($CFG->block_course_fisher_educationaloffer_link) && !empty($CFG->block_course_fisher_educationaloffer_link)) {
                                $coursedata->educationalofferurl = block_course_fisher_format_fields($CFG->block_course_fisher_educationaloffer_link, $hashcourse);
                            }
                            
                            $coursedata->templateshortname = '';
                            if (isset($CFG->block_course_fisher_course_template) && !empty($CFG->block_course_fisher_course_template)) {
                                $coursedata->templateshortname = block_course_fisher_format_fields($CFG->block_course_fisher_course_template, $hashcourse);
                            }

                            $coursedata->notifycreation = false;
                            if (isset($CFG->block_course_fisher_email_condition) && !empty($CFG->block_course_fisher_email_condition)) {
                                $P = $backend->getParser();
                                $coursedata->notifycreation = eval($P->prepareRecord($P->substituteObjects($CFG->block_course_fisher_email_condition, false), (array)$hashcourse));
                            }
                            
                            if($firstcourse !== null && isset($CFG->block_course_fisher_linked_course_category) && !empty($CFG->block_course_fisher_linked_course_category)) {
                                $categories[] = block_course_fisher_format_fields($CFG->block_course_fisher_linked_course_category, $hashcourse);
                            }

                            if (!empty($coursedata->idnumber)) {
                                $oldcourse = $DB->get_record('course', array('idnumber' => $coursedata->idnumber));
                            } else {
                                $oldcourse = $DB->get_record('course', array('shortname' => $coursedata->shortname));
                            }

                            if ($newcourse = block_course_fisher_create_course($coursedata, $userid, block_course_fisher_get_fields_items($categories), $firstcourse, $existent)) {
                                if ($firstcourse === null) {
                                    $firstcourse = clone($newcourse);
                                } elseif (!isset($CFG->block_course_fisher_linktype) || ($CFG->block_course_fisher_linktype == 'meta')) {
                                    $metacourseids[] = $newcourse->id;
                                }
                            } else {
                                notice(get_string('coursecreationerror', 'block_course_fisher'), new moodle_url('/index.php'));
                            }

                            if ($oldcourse) {
                                if ($existent == 'join') {
                                    if ($firstcourse !== null) {
                                        block_course_fisher_add_linkedcourse_url($oldcourse, $firstcourse);
                                        if (!isset($CFG->block_course_fisher_linktype) || ($CFG->block_course_fisher_linktype == 'meta')) {
                                            $metacourseids[] = $oldcourse->id;
                                        }
                                    } else {
                                        $firstcourse = clone($oldcourse);
                                    }
                                }
                            }
                        } else if (count($groupcourses) == 0) { 
                            // Get teacher grouped courses
                            $groupcourses = block_course_fisher_get_groupcourses($teachercourses, $coursehash, $coursedata); 
                        }
                    }
                }

                if (!empty($action)) {
                    if ($firstcourse !== null) {
                        if (!empty($metacourseids) && (!isset($CFG->block_course_fisher_linktype) || ($CFG->block_course_fisher_linktype == 'meta'))) {
                             block_course_fisher_add_metacourses($firstcourse, $metacourseids);
                        }
                        switch ($action) {
                            case 'view':
                            case 'edit':
                                redirect(new moodle_url('/course/'.$action.'.php', array('id' => $firstcourse->id)));
                            break;
                            case 'import':
                                redirect(new moodle_url('/backup/'.$action.'.php', array('id' => $firstcourse->id)));
                            break;
                        }
                    } else {
                        print_error('Course hash does not match for course access');
                    }
                } else if (!empty($groupcourses)) {
                    $preferences = new preferences_form(null, array('coursehash' => $coursehash, 'groupcourses' => $groupcourses));
                    echo $OUTPUT->header();
                    echo html_writer::start_tag('div', array('class' => 'teachercourses'));
                    $preferences->display();
                    echo html_writer::end_tag('div');
                    echo $OUTPUT->footer();
                } else {
                    print_error('Course hash does not match for preferences page');
                }
            }
        } else {
             notice(get_string('nocourseavailable', 'block_course_fisher'), new moodle_url('/index.php'));
        }
    }
}

?>
