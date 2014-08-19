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
$courseid = optional_param('courseid', '', PARAM_TEXT);

if (! $user = $DB->get_record('user', array('id' => $userid)) ) {
    error("No such user in this course");
}

$url = new moodle_url('/blocks/course_fisher/addcourse.php', array('id'=>$userid));

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

        $teachercourses = $backend->get_data();

        if (!empty($teachercourses)) {
            if (empty($courseid)) {
                echo $OUTPUT->header();
                echo html_writer::start_tag('div', array('class' => 'teachercourses'));
                $availablecourses = ''; 
                $existentcourses = ''; 
                foreach($teachercourses as $teachercourse) {
                    $courseshortname = block_course_fisher_format_fields($CFG->block_course_fisher_course_shortname, $teachercourse);
                    if (! $course = $DB->get_record('course', array('shortname' => $courseshortname))) {
                        $categories = array_filter(explode("\n", block_course_fisher_format_fields($CFG->block_course_fisher_fieldlevel, $teachercourse)));
                        $coursepath = implode(' / ', $categories);
                        $coursefullname = block_course_fisher_format_fields($CFG->block_course_fisher_course_fullname, $teachercourse);
                        $coursehash = md5($coursepath.' / '.$coursefullname);

                        $addcourseurl = new moodle_url('/blocks/course_fisher/addcourse.php', array('id' => $userid, 'courseid' => $coursehash));
                        $link = html_writer::tag('a', $coursefullname, array('href' => $addcourseurl));
                        $availablecourses .= html_writer::tag('li', $coursepath.' / '.$link, array());
                    } else {
                        $categorieslist = coursecat::make_categories_list();
                        if (is_enrolled(context_course::instance($course->id), $user, 'moodle/course:update', true)) {
                            $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
                        } else {
                            $coursehash = md5($categorieslist[$course->category].' / '.$course->fullname);
                            $courseurl = new moodle_url('/blocks/course_fisher/addcourse.php', array('id' => $userid, 'courseid' => $coursehash));
                        }
                        $link = html_writer::tag('a', $course->fullname, array('href' => $courseurl));
                        $existentcourses .= html_writer::tag('li', $categorieslist[$course->category].' / '.$link, array());
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
                    $courseshortname = block_course_fisher_format_fields($CFG->block_course_fisher_course_shortname, $teachercourse);
                    $categories = array_filter(explode("\n", block_course_fisher_format_fields($CFG->block_course_fisher_fieldlevel, $teachercourse)));
                    $coursepath = implode(' / ', $categories);
                    $coursefullname = block_course_fisher_format_fields($CFG->block_course_fisher_course_fullname, $teachercourse);
                    $coursehash = md5($coursepath.' / '.$coursefullname);

                    if ($coursehash == $courseid) {
                        if ($newcourse = block_course_fisher_create_course($coursefullname, $courseshortname, $userid, $categories)) {
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
        }

    }
}

?>
