<?php
require_once(dirname(__FILE__) . '/../../config.php');

require_once($CFG->dirroot."/blocks/course_fisher/locallib.php");
require_once($CFG->dirroot."/blocks/course_fisher/backendlib.php");

global $CFG;
//global $USER;
/*

    [block_course_fisher_backend] => csv
    [block_course_fisher_locator] => https://spweb.units.it/front/tabellaCSV.php?id=v_ie_di_coper&pino=[%!USER:idnumber!%]
    [block_course_fisher_parameters] => [%uidnumber%]
    [block_course_fisher_fieldlist] => DIP_COD
DIP_DES
TIPO_CORSO_COD
TIPO_CORSO_DES
CDS_COD
NOME_CDS
CDSORD_COD
AA_ORD_ID
REGDID_COD
AA_REGDID_ID
PDS_COD
PDS_DES
AF_GEN_COD
AF_GEN_DES
AA_OFF_ID
ANNO_CORSO
MATRICOLA_RESP_DID
MATRICOLA_TITOLARE
MATRICOLA
    [block_course_fisher_separator] => |
    [block_course_fisher_firstrow] => 1
    [block_course_fisher_fieldlevel] => [%DIP_COD%]:[%DIP_DES%]
[%CDS_COD%]:[%CDS_COD%] - [%NOME_CDS%]
[%AA_OFF_ID%]:A.A. [%AA_OFF_ID%]
    [block_course_fisher_coursename] => [%AF_GEN_COD%][%AF_GEN_COD%]: [%AF_GEN_DES%]
    [block_course_fisher_fieldtest] => [%uidnumber%]:5772
block_course_fisher_fieldlist
*/


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

//echo '<div class="actionbuttons">' . $OUTPUT->single_button($baseurl, 'Test the backend' 'get') . '</div>';

$BKEfile=$CFG->dirroot."/blocks/course_fisher/backend/".$CFG->block_course_fisher_backend."/lib.php";
$BKEname="block_course_fisher_backend_".$CFG->block_course_fisher_backend;


$zztop="";

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
//        print_r($BC->getConf());
//       print "Un due tre";

      } // else Class is initializable


    } // else Class exists
  } // else backend file exists
} // else config exists

$P=$BC->getParser();
$zztop=$P->getObjects();
//print_r($P);
$z=$P->setFields($CFG->block_course_fisher_fieldlist);
if($z)
{
 print "Ha inserito $z campi<hr><pre>";
 print_r($P->getFields());
 print "</pre>";

 print "<br>Checking fieldlevel<hr><pre>";
 $Muniq=$P->parseFields($CFG->block_course_fisher_fieldlevel);
 print "Result: ".$P->getResult(); if(strlen($P->getResultString())) { print $P->getResultString(); }; print "\r\n";
 if( $Muniq )
 {
  print_r($Muniq);
 }
 print "</pre>";
 

 print "<br>Checking fullname: ".$CFG->block_course_fisher_course_fullname."<hr><pre>";
 $Muniq=$P->parseFields($CFG->block_course_fisher_course_fullname);
 print "Result: ".$P->getResult(); if(strlen($P->getResultString())) { print $P->getResultString(); }; print "\r\n";
 if( $Muniq )
 {
  print_r($Muniq);
 }
 print "</pre>";
 

 print "<br>Checking shortname: ".$CFG->block_course_fisher_course_shortname."<hr><pre>";
 $Muniq=$P->parseFields($CFG->block_course_fisher_course_shortname);
 print "Result: ".$P->getResult(); if(strlen($P->getResultString())) { print $P->getResultString(); }; print "\r\n";
 if( $Muniq )
 {
  print_r($Muniq);
 }
 print "</pre>";
 
 

 print "<br>Checking parameters: <hr><pre>".$CFG->block_course_fisher_parameters."\r\n----------------------\r\n";
 $Muniq=$P->parseFields($CFG->block_course_fisher_parameters,1);
 print "Result: ".$P->getResult(); if(strlen($P->getResultString())) { print $P->getResultString(); }; print "\r\n";
 if( $Muniq )
 {
  print_r($Muniq);
 }
 
print "\r\n ObjValues: \r\n";
  print_r($P->getObjValues());

print "</pre>";
 


 print "<br>Checking TestValues: <hr><pre>".$CFG->block_course_fisher_fieldtest."\r\n----------------------\r\n";
 $Muniq=$P->parseFields($CFG->block_course_fisher_fieldtest,1);
 print "Result: ".$P->getResult(); if(strlen($P->getResultString())) { print $P->getResultString(); }; print "\r\n";
 if( $Muniq )
 {
  print_r($Muniq);
 }


print "<br>fieldtest Eval:".$eval."<br>\r\n";

$Muniq=$P->parseFieldAssign($CFG->block_course_fisher_fieldtest);
 if( $Muniq )
 {
  print_r($Muniq);
 }


print "<br>Assigning<br>\r\n";


 $Muniq=$P->parseFields($CFG->block_course_fisher_fieldtest,$Muniq);
 print "Result: ".$P->getResult(); if(strlen($P->getResultString())) { print $P->getResultString(); }; print "\r\n";
 if( $Muniq )
 {
  print_r($Muniq);
 }



 print "<br>Checking LOCATOR: ".$CFG->block_course_fisher_locator."<hr><pre>";
 $Muniq=$P->parseFields($CFG->block_course_fisher_locator,1);
 print "Result: ".$P->getResult(); if(strlen($P->getResultString())) { print $P->getResultString(); }; print "\r\n";
 if( $Muniq )
 {
  print_r($Muniq);
 }
 print "</pre>";

/*
 print "<br>ASSIGN LOCATOR: ".$CFG->block_course_fisher_locator."<hr><pre>";
$Muniq=$P->parseFieldAssign($CFG->block_course_fisher_locator);
 if( $Muniq )
 {
  print_r($Muniq);
 }
*/
}
//print_r($BC);


print "<br>Check SubstituteObjects<hr><pre>";
print_r($P->substituteObjects($CFG->block_course_fisher_parameters));
 print "</pre>";


print "<br>EVALRECORD<hr><pre>";

$ray=array();
$v="014467|Dipartimento di Fisica|LM|Laurea Magistrale|SM23|FISICA|SM23-12|2012|SM23-12-13|2013|SM23+3+|astrofisica e cosmologia, fisica terrestre e dell'ambiente|753SM|ATMOSFERE STELLARI|2013|1|015220||015220|";


   $FLD=array_flip($P->getFields());
   $t=preg_split("/".$CFG->block_course_fisher_separator."/",$v);

   while(list($tk,$tv)=each($t))
   {
     if(isset($FLD[$tk]))
     {
       $ray[$FLD[$tk]]=$tv;
     }
   }

print_r($ray);

print "\r\n---------------------------\r\n";

print_r($P->evalRecord($P->substituteObjects($CFG->block_course_fisher_parameters),$ray));
 print "</pre>";



print "\r\n\r\n<br>Fine.<br>";














// ------------------------------+
// Footer
// ------------------------------+

echo '<div class="backlink">' . html_writer::link($confurl, get_string('back')) . '</div>';
echo $OUTPUT->footer();

?>
