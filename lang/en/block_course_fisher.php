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
 * @copyright Roberto Pinna Angelo Calò
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['course_fisher:addinstance'] = 'Add a new Course Fisher block';
$string['course_fisher:myaddinstance'] = 'Add a new Course Fisher block to Dashboard';
$string['course_fisher:addallcourses'] = 'Add all courses got from Course Fisher';
$string['course_fisher:addcourses'] = 'Add own courses got from Course Fisher';
$string['pluginname'] = 'Course Fisher';
$string['configtitle'] = 'Title';
$string['courseguides'] = 'Course guides';
$string['courseregisters'] = 'Course registers';
$string['addmoodlecourse'] = 'Add moodle course';
$string['addcourse'] = 'Add course';
$string['nocourseavailable'] = 'No courses available';
$string['coursegroup'] = 'Course group';
$string['addcoursegroup'] = 'Add course group';
$string['addsinglecourse'] = 'Add single course';
$string['entercourse'] = 'Enter into course';
$string['enroltocourse'] = 'Enrol as teacher into course';
$string['availablecourses'] = 'Addable courses';
$string['existentcourses'] = 'Existent courses';
$string['backendfailure'] = 'Can not connect to course backend';
$string['edit'] = 'Edit course settings';
$string['view'] = 'View course';
$string['import'] = 'Import data from an other course';
$string['coursenotfound'] = 'Course not found';
$string['filter'] = 'User filter';
$string['shown'] = 'Shown';
$string['hidden'] = 'Hidden';
$string['nouserfilterset'] = 'No user filter set';
$string['ifuserprofilefield'] = 'if user profile field';
$string['nocourseavailable'] = 'Sorry no available courses';
$string['courselink'] = 'Linked course';
$string['courselinkmessage'] = 'This course is linked to {$a}. Please click the link below';
$string['choosewhatadd'] = 'Choose what would you add:';
$string['choosenextaction'] = 'What would you do after course creation:';
$string['execute'] = 'Execute';




$string['linkhelppage'] = 'Help Page URL';
$string['insertlink'] = 'Insert link';
$string['configurationtest'] = 'Configuration Test';
$string['configurationbackend'] = 'Backend Configuration';
$string['backend'] = 'Backend';
$string['backendtype'] = 'Backend Type';
$string['locatorurl'] = 'Locator (URL)';
$string['sourceformat'] = 'eg. file://path or mysql:username:password@host:port/database/table use multiple rows if want to use multiple sources in a first match order';
$string['parameters'] = 'parameters';
$string['parametersformat'] = 'es. query or filter get. Use [%fiel%] for userfield, eg. [%uidnumber%]';
$string['testvalue'] = 'Values for test';
$string['testvalueformat'] = 'one for row:  [field]:value';
$string['separator'] = 'separator';
$string['separatoruse'] = 'fields separator, used only where necessary (es. csv)';
$string['firstrow'] = 'Jump first row';
$string['firstrowcontent'] = 'if is the list of CSV fields';
$string['fieldlist'] = 'Fields list';
$string['fieldlistformat'] = 'one for row in a first match order';



$string['chooseexistsaction'] = 'Some courses in course group already exists. What you would to do with those courses?';
$string['join'] = 'Join them to course group';
$string['separated'] = 'Keep them separated from course group';
$string['educationaloffer'] = 'Educational Offer Page';
$string['educationaloffermessage'] = 'Here you find all information about this course edudcational offer';
$string['coursenotifysubject'] = 'Course Fisher - A new course requires your attention!';
$string['coursenotifytext'] = 'Dear Admin,
You need to check a Course Fisher new course
{$a->coursefullname}

Course URL: {$a->courseurl}';
$string['coursenotifytextcomplete'] = 'Dear Admin,
You need to check a Course Fisher new course
{$a->coursefullname}

Course URL: {$a->courseurl}

Educational Offer Page URL: {$a->educationalofferurl}';
$string['coursenotifyhtml'] = 'Dear Admin,<br />
You need to check a Course Fisher new course<br />
<b>{$a->coursefullname}</b><br /><br />
Course URL: <a href="{$a->courseurl}">{$a->courseurl}</a>';
$string['coursenotifyhtmlcomplete'] = 'Dear Admin,<br />
You need to check a Course Fisher new course<br />
<b>{$a->coursefullname}</b><br /><br />
Course URL: <a href="{$a->courseurl}">{$a->courseurl}</a><br />
Educational Offer Page URL: <a href="{$a->educationalofferurl}">{$a->educationalofferurl}</a>';
$string['meta'] = 'Connected with Meta Link enrolment in father course';
$string['guest'] = 'Connected with Guest enrolment in sons courses';
$string['existentcourse'] = 'This course was already created';
$string['notifycoursecreation'] = 'Email course creation based on rule to';
$string['confignotifycoursecreation'] = 'Send course creation notification messages to these selected users. Notification will be sent upon the previous rule verification.';

// Append backends string as backend_<backend name>:string so you must call get_string('backend_db:pluginname', 'block_course_fisher') for example
require_once($CFG->dirroot.'/blocks/course_fisher/langlib.php');
$string = block_course_fisher_backend_lang('en', $string);
