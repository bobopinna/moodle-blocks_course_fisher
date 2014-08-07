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
 * Teacher course generator.
 *
 * @package    blocks
 * @subpackage ugov
 * @copyright  2014 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__) . '/../../config.php');
require_once('locallib.php');

$id = required_param('id', PARAM_INT);

$url = new moodle_url('/blocks/ugov/index.php', array('id'=>$id));

$PAGE->set_url($url);

if ($USER->idnumber != $id) {
    print_error('invalidteacherid','block_ugov');
}

require_login();
$context = context_site::instance();

$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');

/// Print the page header
$straddcourse = get_string('addcours', 'block_ugov');

$PAGE->navbar->add($straddcourse);
$PAGE->set_heading($straddcourse);



$ugov = new ugov($id);
$ugov->setup();
$ugov->display();
$ugov->generate_data();
$ugov->complete();

?>
