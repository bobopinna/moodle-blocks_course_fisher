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
 * @copyright  2014 Diego Fantoma
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

require_once($CFG->dirroot."/blocks/course_fisher/locallib.php");
require_once($CFG->dirroot."/blocks/course_fisher/backendlib.php");

global $CFG;


$urlparams = array();
$confurl = new moodle_url('/admin/settings.php?section=blocksettingcourse_fisher', $urlparams);
$baseurl = new moodle_url('/blocks/course_fisher/backendtest.php', $urlparams);
$PAGE->set_url($baseurl);

$PAGE->set_pagelayout('standard');
$PAGE->set_title('Course Fisher Backend Test page');
$PAGE->set_heading('Course Fisher Backend Test page');
$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname', 'block_course_fisher'));
$PAGE->navbar->add('Course Fisher Backend Test Page', $baseurl);
echo $OUTPUT->header();

// ------------------------------+
// Body code
// ------------------------------+


$BKEfile=$CFG->dirroot."/blocks/course_fisher/backend/".$CFG->block_course_fisher_backend."/lib.php";
$BKEname="block_course_fisher_backend_".$CFG->block_course_fisher_backend;


if(!strlen($CFG->block_course_fisher_backend))
{
  //ERROR no config
}
else
{
  if(!file_exists($BKEfile))
  {
    // ERROR no backend file
  }
  else
  {
    @include_once($BKEfile);
    if(!class_exists($BKEname))
    {
      print "Error: Class not existant";
      // ERROR class not defined
    }
    else
    {
      $BC=new $BKEname();
      if(!$BC->init())
      {
        // ERROR Class not initializable
        print "Error: ".$BC->getError();
      }
      else
      {
$Fld=array("block_course_fisher_fieldlevel", "block_course_fisher_course_fullname", "block_course_fisher_course_shortname",  "block_course_fisher_locator", "block_course_fisher_parameters",
"block_course_fisher_fieldtest");


         if(false===($BC->checkCFG("block_course_fisher_fieldlist",$Fld)))
         {
           print "Error: ".$BC->getError()."!!!";
         }
         else
         {

print "\r\n\r\n<br>Backend ready<br>\r\n\r\n";
print "<pre>";
print_r($BC->HTTPfetch(true));
print "</pre>";

         }

      } // else Class is initializable


    } // else Class exists
  } // else backend file exists
} // else config exists





print "\r\n\r\n<br>Fine.<br>";

// ------------------------------+
// Footer
// ------------------------------+

echo '<div class="backlink">' . html_writer::link($confurl, get_string('back')) . '</div>';
echo $OUTPUT->footer();

?>
