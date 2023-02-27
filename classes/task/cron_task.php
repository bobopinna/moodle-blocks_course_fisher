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
 * Task for updating backend datas for Course Fisher block
 *
 * @package   block_course_fisher
 * @copyright 2023 Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_fisher\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Task for updating backend datas for Course Fisher block
 *
 * @package   block_course_fisher
 * @copyright 2023 Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cron_task extends \core\task\scheduled_task {

    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'block_course_fisher');
    }

    /**
     * This task execute backend cron.
     */
    public function execute() {
        global $CFG, $DB;
        
        require_once($CFG->dirroot . '/blocks/course_fisher/locallib.php');

        block_course_fisher_cron();

    }
}
