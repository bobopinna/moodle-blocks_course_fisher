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
 * Strings for component 'block_course_fisher', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   block_course_fisher
 * @copyright Roberto Pinna <roberto.pinna@unipmn.it
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['course_fisher:addinstance'] = 'Add a new Course Fisher block';
$string['pluginname'] = 'Course Fisher';
$string['configtitle'] = 'Title';
$string['courseguides'] = 'Course guides';
$string['courseregisters'] = 'Course registers';
$string['addmoodlecourse'] = 'Add moodle course';
$string['availablecourses'] = 'Addable courses';
$string['availableregisters'] = 'Available registers';
$string['existentcourses'] = 'Existent courses';
$string['backendfailure'] = 'Can not connect to course backend';
$string['editcourse'] = 'Edit course';
$string['viewcourse'] = 'View course';
$string['coursenotfound'] = 'Course not found';
$string['filter'] = 'User filter';
$string['shown'] = 'Shown';
$string['hidden'] = 'Hidden';
$string['nouserfilterset'] = 'No user filter set';
$string['ifuserprofilefield'] = 'if user profile field';


// Append backends string as backend_<backend name>:string so you must call get_string('backend_db:pluginname', 'block_course_fisher') for example
require_once($CFG->dirroot.'/blocks/course_fisher/langlib.php');
$string = block_course_fisher_backend_lang('en', $string);
