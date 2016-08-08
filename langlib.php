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

    function block_course_fisher_backend_lang($lang, $blockstrings) {
        global $CFG;

        $backends = scandir($CFG->dirroot.'/blocks/course_fisher/backend');
        foreach ($backends as $backend) {
            if (file_exists($CFG->dirroot.'/blocks/course_fisher/backend/'.$backend.'/lang/'.$lang.'/coursefisherbackend_'.$backend.'.php')) {
                $string = array();
                require_once($CFG->dirroot.'/blocks/course_fisher/backend/'.$backend.'/lang/'.$lang.'/coursefisherbackend_'.$backend.'.php');
                foreach ($string as $name => $translation) {
                    $blockstrings['backend_'.$backend.':'.$name] = $translation;
                }
            }
        }
        return $blockstrings;
    }

