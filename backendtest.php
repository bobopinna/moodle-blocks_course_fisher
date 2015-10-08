<?php
require_once(dirname(__FILE__) . '/../../config.php');

require_once($CFG->dirroot."/blocks/course_fisher/locallib.php");
require_once($CFG->dirroot."/blocks/course_fisher/backendlib.php");

global $CFG;


$urlparams = array();
$confurl = new moodle_url('http://mooshib.units.it/admin/settings.php?section=blocksettingcourse_fisher', $urlparams);
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
