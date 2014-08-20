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
 * Strings for component 'block_course_fisher', language 'it', branch 'MOODLE_20_STABLE'
 *
 * @package   block_course_fisher
 * @copyright Roberto Pinna <roberto.pinna@unipmn.it
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['course_fisher:addinstance'] = 'Aggiungere un nuovo blocco Course Fisher';
$string['pluginname'] = 'Course Fisher';
$string['courseguides'] = 'Guide corsi';
$string['courseregisters'] = 'Registri corsi';
$string['addmoodlecourse'] = 'Aggiungi corso moodle';
$string['availablecourses'] = 'Corsi creabili';
$string['availableregisters'] = 'Registri disponibili';
$string['existentcourses'] = 'Corsi esistenti';
$string['backendfailure'] = 'Non &egrave; possibile collegarsi al backend per il recupero dei corsi';
$string['editcourse'] = 'Impostazioni corso';
$string['viewcourse'] = 'Accedi al corso';

// Appende le traduzioni dei backend come backend_<nome backend>:stringa in questo modo bisogna utilizzare per esempio get_string('backend_db:pluginname', 'block_course_fisher')
require_once($CFG->dirroot.'/blocks/course_fisher/langlib.php');
$string = block_course_fisher_backend_lang('it', $string);
