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
 * Course fishe course generator.
 *
 * @package    blocks
 * @subpackage course_fisher
 * @copyright  2014 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__) . '/../../config.php');
require_once('locallib.php');
require_once('backendlib.php');

$userid = required_param('id', PARAM_INT);
$courseid = optional_param('courseid', '', PARAM_ALPHANUM);

if (! $user = $DB->get_record('user', array('id' => $userid)) ) {
    error("No such user in this course");
}

$url = new moodle_url('/blocks/course_fisher/addcourse.php', array('id' => $userid, 'courseid' => $courseid));

$PAGE->set_url($url);

if ($USER->id != $userid) {
    print_error('invalidteacherid','block_course_fisher');
}

require_login();
$systemcontext = context_system::instance();

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

        $teachercourses = $backend->get_data(has_capability('block/course_fisher:addallcourses', $systemcontext));

        if (!empty($teachercourses)) {
            if (empty($courseid)) {
                echo $OUTPUT->header();
                echo html_writer::start_tag('div', array('class' => 'teachercourses'));
                $availablecourses = ''; 
                $existentcourses = ''; 
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
                        $fieldlist = block_course_fisher_format_fields($CFG->block_course_fisher_fieldlevel, $teachercourse);
                        $categories = block_course_fisher_get_fields_description(array_filter(explode("\n", $fieldlist)));
                        $coursepath = implode(' / ', $categories);
                        $coursefullname = block_course_fisher_format_fields($CFG->block_course_fisher_course_fullname, $teachercourse);
                        $coursehash = md5($coursepath.' / '.$coursefullname);

                        $addcourseurl = new moodle_url('/blocks/course_fisher/addcourse.php', array('id' => $userid, 'courseid' => $coursehash));
                        $link = html_writer::tag('a', get_string('addcourse', 'block_course_fisher'), array('href' => $addcourseurl, 'class' => 'addcourselink'));
                        $coursecategories = html_writer::tag('span', $coursepath, array('class' => 'addcoursecategory'));
                        $coursename = html_writer::tag('span', $coursefullname, array('class' => 'addcoursename'));
                        if (has_capability('block/course_fisher:addallcourses', $systemcontext)) {
                           $coursecodes = html_writer::tag('span', $coursecode.$courseshortname, array('class' => 'addcoursecode'));
                           $availablecourses .= html_writer::tag('li', $link.$coursename.$coursecategories.$coursecodes, array('class' => 'addcourseitem'));
                        } else {
                           $availablecourses .= html_writer::tag('li', $link.$coursename.$coursecategories, array('class' => 'addcourseitem'));
                        }
                    } else {
                        $categorieslist = coursecat::make_categories_list();
                        $link = '';

                        $isalreadyteacher = is_enrolled(context_course::instance($course->id), $user, 'moodle/course:update', true);
                        $canaddall = has_capability('block/course_fisher:addallcourses', $systemcontext);
                        if ($isalreadyteacher || $canaddall) {
                            $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
                            $link = html_writer::tag('a', get_string('entercourse', 'block_course_fisher'), array('href' => $courseurl, 'class' => 'entercourselink'));
                        } else {
                            $coursehash = md5($categorieslist[$course->category].' / '.$course->fullname);
                            $courseurl = new moodle_url('/blocks/course_fisher/addcourse.php', array('id' => $userid, 'courseid' => $coursehash));
                            $link = html_writer::tag('a', get_string('enroltocourse', 'block_course_fisher'), array('href' => $courseurl, 'class' => 'enroltocourselink'));
                        }
                        $cousecategories = html_writer::tag('span', $categorieslist[$course->category], array('class' => 'entercoursecategory'));
                        $coursename = html_writer::tag('span', $course->fullname, array('class' => 'entercoursename'));
                        if (has_capability('block/course_fisher:addallcourses', $systemcontext)) {
                            $coursecode = isset($course->idnumber) && !empty($course->idnumber)?$course->idnumber:$course->shortname;
                            $coursecodes = html_writer::tag('span', $coursecode, array('class' => 'entercoursecode'));
                            $existentcourses .= html_writer::tag('li', $link.$coursename.$coursecategories.$coursecodes, array('class' => 'entercourseitem'));
                        } else {
                            $existentcourses .= html_writer::tag('li', $link.$coursename.$coursecategories, array('class' => 'entercourseitem'));
                        }
                    }
                }
                if (!empty($availablecourses)) {
                    echo html_writer::tag('h1', get_string('availablecourses', 'block_course_fisher'), array());
                    echo html_writer::start_tag('ul', array('class' => 'availablecourses'));
                    echo $availablecourses;
                    echo html_writer::end_tag('ul');
                }
                if (!empty($existentcourses)) {
                    echo html_writer::tag('h1', get_string('existentcourses', 'block_course_fisher'), array());
                    echo html_writer::start_tag('ul', array('class' => 'existentcourses'));
                    echo $existentcourses;
                    echo html_writer::end_tag('ul');
                }
                echo html_writer::end_tag('div');
                echo $OUTPUT->footer();
            } else {
                foreach($teachercourses as $teachercourse) {
                    $course = null;
                    $coursecode = '';
                    $courseshortname = '';
                    $coursehash = '';
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
                        $coursehash = md5($coursepath.' / '.$coursefullname);
                    } else {
                        $categorieslist = coursecat::make_categories_list();
                        $coursehash = md5($categorieslist[$course->category].' / '.$course->fullname);
                    }

                    if ($coursehash == $courseid) {
                        $coursecode = block_course_fisher_format_fields($CFG->block_course_fisher_course_code, $teachercourse);
                        if (has_capability('block/course_fisher:addallcourses', $systemcontext)) {
                            $userid = null;
                        }
                        if ($newcourse = block_course_fisher_create_course($coursefullname, $courseshortname, $coursecode, $userid, block_course_fisher_get_fields_items($categories))) {
                             if ($CFG->block_course_fisher_redirect == COURSE_EDIT) {
                                 redirect(new moodle_url('/course/edit.php', array('id' => $newcourse->id)));
                             } else {
                                 redirect(new moodle_url('/course/view.php', array('id' => $newcourse->id)));
                             }
                        } else {
                             notice(get_string('coursecreationerror', 'block_course_fisher'));
                        }
                    }
                }
                print_error('Course hash does not match');
            }
        } else {
            notice(get_string('nocourseavailable', 'block_course_fisher'), new moodle_url('/index.php'));
        }
    }
}

?>
