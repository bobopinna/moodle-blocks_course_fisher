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

$courseid = optional_param('courseid', '', PARAM_ALPHANUM);
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

        $teachercourses = $backend->get_data(has_capability('block/course_fisher:addallcourses', $systemcontext));

        if (!empty($teachercourses)) {
            $categorieslist = coursecat::make_categories_list();
            if (empty($courseid)) {
                echo $OUTPUT->header();
                echo html_writer::start_tag('div', array('class' => 'teachercourses'));

                $availablecourses = array();
                $existentcourses = '';
                foreach($teachercourses as $teachercourse) {

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
                    if (isset($CFG->block_course_fisher_course_group) && !empty($CFG->block_course_fisher_course_group)) {
                        $coursegroup = block_course_fisher_format_fields($CFG->block_course_fisher_course_group, $teachercourse);
						$primary = false;						
						if(empty($coursegroup)){
							$coursegroup = $courseidnumber;
							$primary = true;
						}
                    }

                    if (! $course) {
                        $coursecode = !empty($courseidnumber)?$courseidnumber:$courseshortname;

                        $fieldlist = block_course_fisher_format_fields($CFG->block_course_fisher_fieldlevel, $teachercourse);
                        $categories = block_course_fisher_get_fields_description(array_filter(explode("\n", $fieldlist)));
                        $coursepath = implode(' / ', $categories);
                        $coursefullname = block_course_fisher_format_fields($CFG->block_course_fisher_course_fullname, $teachercourse);
                        $coursehash = md5($coursepath.' / '.$coursecode);

                        $addcourseurl = new moodle_url('/blocks/course_fisher/addcourse.php', array('courseid' => $coursehash));
                        $link = html_writer::tag('a', get_string('addcourse', 'block_course_fisher'), array('href' => $addcourseurl, 'class' => 'addcourselink'));
                        $coursecategories = html_writer::tag('span', $coursepath, array('class' => 'addcoursecategory'));
                        $coursename = html_writer::tag('span', $coursefullname, array('class' => 'addcoursename'));
                       
						if (has_capability('block/course_fisher:addallcourses', $systemcontext)) {
                           $coursecodes = html_writer::tag('span', $coursecode." ".$courseshortname, array('class' => 'addcoursecode'));
			   
						   if($primary && isset($availablecourses[$coursegroup]))
								$availablecourses[$coursegroup] = array_merge(array($coursehash => html_writer::tag('li', $link.$coursename.$coursecategories.$coursecodes, array('class' => 'addcourseitem'))), $availablecourses[$coursegroup]);
						   else
						   		$availablecourses[$coursegroup][$coursehash] = html_writer::tag('li', $link.$coursename.$coursecategories.$coursecodes, array('class' => 'addcourseitem'));
                        } else {
						   if($primary && isset($availablecourses[$coursegroup]))
						   		$availablecourses[$coursegroup] = array_merge(array($coursehash => html_writer::tag('li', $link.$coursename.$coursecategories, array('class' => 'addcourseitem'))), $availablecourses[$coursegroup]);
						   else
                           		$availablecourses[$coursegroup][$coursehash] = html_writer::tag('li', $link.$coursename.$coursecategories, array('class' => 'addcourseitem'));
                        }
                    } else {
                        $coursecode = isset($course->idnumber) && !empty($course->idnumber)?$course->idnumber:$course->shortname;
                        $link = '';

                        $isalreadyteacher = is_enrolled(context_course::instance($course->id), $user, 'moodle/course:update', true);
                        $canaddall = has_capability('block/course_fisher:addallcourses', $systemcontext);
                        if (!$isalreadyteacher && !$canaddall && $primary) {
                            $coursehash = md5($categorieslist[$course->category].' / '.$coursecode);
                            $courseurl = new moodle_url('/blocks/course_fisher/addcourse.php', array('courseid' => $coursehash));
                            $link = html_writer::tag('a', get_string('enroltocourse', 'block_course_fisher'), array('href' => $courseurl, 'class' => 'enroltocourselink'));
                            $coursecategories = html_writer::tag('span', $categorieslist[$course->category], array('class' => 'enroltocoursecategory'));
                            $coursename = html_writer::tag('span', $course->fullname, array('class' => 'enroltocoursename'));
                            $existentcourses[$coursegroup][$coursehash] = html_writer::tag('li', $link.$coursename.$coursecategories, array('class' => 'enroltocourseitem'));
                        }
                    }
                }
                if (!empty($availablecourses)) {
                    echo html_writer::tag('h1', get_string('availablecourses', 'block_course_fisher'), array());
                    foreach ($availablecourses as $coursegroup => $availablegroupelements) {
                        echo html_writer::start_tag('ul', array('class' => 'availablecourses'.$coursegroup));
                        if (count($availablegroupelements) > 1) {
                            if (!empty($coursegroup)) {
                                echo html_writer::start_tag('li', array('class' => 'availablecourses'.$coursegroup));
                                $grouphash = implode('', array_keys($availablegroupelements));
                                $groupurl = new moodle_url('/blocks/course_fisher/addcourse.php', array('courseid' => $grouphash));
                                echo html_writer::tag('a', get_string('addcoursegroup', 'block_course_fisher'), array('href' => $groupurl, 'class' => 'addcoursegrouplink'));
                                echo html_writer::start_tag('ul', array('class' => 'availablecourses'.$coursegroup));
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
                    foreach ($existentcourses as $coursegroup => $existentgroupelements) {
                        echo html_writer::start_tag('ul', array('class' => 'existentcourses'.$coursegroup));
                        if (count($existentgroupelements) > 1) {
                            if (!empty($coursegroup)) {
                                echo html_writer::start_tag('li', array('class' => 'existentcourses'.$coursegroup));
                                echo html_writer::start_tag('ul', array('class' => 'existentcourses'.$coursegroup));
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
                echo html_writer::end_tag('div');
                echo $OUTPUT->footer();
            } else {
                $coursehashes = str_split($courseid, strlen(md5('coursehash')));
                $metacourseids = array();
                $firstcourse = null;
				foreach($coursehashes as $hash){
		            foreach($teachercourses as $teachercourse) {
		                $course = null;
		                $coursehash = '';
		                $courseidnumber = '';
		                $courseshortname = block_course_fisher_format_fields($CFG->block_course_fisher_course_shortname, $teachercourse);
		                if (isset($CFG->block_course_fisher_course_code) && !empty($CFG->block_course_fisher_course_code)) {
		                    $courseidnumber = block_course_fisher_format_fields($CFG->block_course_fisher_course_code, $teachercourse);
		                    $course = $DB->get_record('course', array('idnumber' => $courseidnumber));
		                } else {
		                    $course = $DB->get_record('course', array('shortname' => $courseshortname));
		                }

		                if (! $course) {
		                    $coursecode = !empty($courseidnumber)?$courseidnumber:$courseshortname;
		                    $categories = array_filter(explode("\n", block_course_fisher_format_fields($CFG->block_course_fisher_fieldlevel, $teachercourse)));
		                    $categoriesdescriptions = block_course_fisher_get_fields_description($categories);
		                    $coursepath = implode(' / ', $categoriesdescriptions);
		                    $coursefullname = block_course_fisher_format_fields($CFG->block_course_fisher_course_fullname, $teachercourse);
		                    $coursehash = md5($coursepath.' / '.$coursecode);
		                } else {
		                    $coursecode = isset($course->idnumber) && !empty($course->idnumber)?$course->idnumber:$course->shortname;
		                    $coursehash = md5($categorieslist[$course->category].' / '.$coursecode);
		                }


						//if (in_array($coursehash, $coursehashes)) {
						if ($coursehash === $hash) {
		                    $userid = $USER->id;
		                    if (has_capability('block/course_fisher:addallcourses', $systemcontext)) {
		                        $userid = null;
		                    }
		                    
		                    $summary = '';
		                    if (isset($CFG->block_course_fisher_course_summary) && !empty($CFG->block_course_fisher_course_summary))
		                    	$summary = block_course_fisher_format_fields($CFG->block_course_fisher_course_summary, $teachercourse);
		                    
		                    $sectionzero = null;
		                    if (isset($CFG->block_course_fisher_sectionzero_name) && !empty($CFG->block_course_fisher_sectionzero_name))
		                    	$sectionzero = block_course_fisher_format_fields($CFG->block_course_fisher_sectionzero_name, $teachercourse);
		                    
		                    $educationaloffer_link = null;
		                    if (isset($CFG->block_course_fisher_educationaloffer_link) && !empty($CFG->block_course_fisher_educationaloffer_link))
		                    	$educationaloffer_link = block_course_fisher_format_fields($CFG->block_course_fisher_educationaloffer_link, $teachercourse);
		                    
		                    $template = null;
		                    if (isset($CFG->block_course_fisher_course_template) && !empty($CFG->block_course_fisher_course_template))
		                    	$template = block_course_fisher_format_fields($CFG->block_course_fisher_course_template, $teachercourse);
		                    
							$sendmail = false;
		                    if (isset($CFG->block_course_fisher_email_condition) && !empty($CFG->block_course_fisher_email_condition)){
		                    	$P = $backend->getParser();
		                    	$sendmail = eval($P->prepareRecord($P->substituteObjects($CFG->block_course_fisher_email_condition,false),(array)$teachercourse));
		                    }
		                    
							if($firstcourse !== null && isset($CFG->block_course_fisher_linked_course_category) && !empty($CFG->block_course_fisher_linked_course_category))
								$categories[] = block_course_fisher_format_fields($CFG->block_course_fisher_linked_course_category, $teachercourse);

		                    if ($newcourse = block_course_fisher_create_course($coursefullname, $courseshortname, $courseidnumber, $userid, block_course_fisher_get_fields_items($categories), $firstcourse, $summary, $sectionzero, $educationaloffer_link, $template, $sendmail)) {
		                        if ($firstcourse === null) {
		                            $firstcourse = clone($newcourse);
		                        } else {
		                            $metacourseids[] = $newcourse->id;
		                        }
		                    } else {
		                         notice(get_string('coursecreationerror', 'block_course_fisher'), new moodle_url('/index.php'));
		                    }
							break;
		                }
		            }
				}

                if ($firstcourse !== null) {
					/*if (!empty($metacourseids)) {
                         block_course_fisher_add_metacourses($firstcourse, $metacourseids);
                    }*/
                    if ($CFG->block_course_fisher_redirect == COURSE_EDIT) {
                        redirect(new moodle_url('/course/edit.php', array('id' => $firstcourse->id)));
                    } else {
                        redirect(new moodle_url('/course/view.php', array('id' => $firstcourse->id)));
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
