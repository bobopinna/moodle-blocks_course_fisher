<?php
defined('MOODLE_INTERNAL') || die;

class block_course_fisher_backend {

    public $name;
    private $error="";

    public function __construct() {
        $this->name="Course fisher backend class";
    }

    public function init() {
        global $CFG;
        if (!is_subclass_of($this, 'block_course_fisher_backend')) {
            $this->error="Woops, wrong class initialized";
            return(false);
        }

        if (!$CFG->block_course_fisher_backend==__CLASS__) {
            $this->error="The name of the configured backend doesn't match the called class";
            return(false);
        }


        $this->error="";
        return(true);
    } 
 
    public function checkCFG() {
        global $CFG;
/*
block_course_fisher_locator
block_course_fisher_parameters
block_course_fisher_fieldlist
block_course_fisher_separator
block_course_fisher_firstrow
block_course_fisher_fieldlevel
block_course_fisher_coursename
block_course_fisher_fieldtest

    if(isset($CFG->)) {
      $this->=$CFG->;
    }
    else {
      $this->error="";
      return(false);
    }
*/

    }

    public function getError() {
      return($this->error);
    }

    public function getConf() {
      global $CFG;

   // $out=__CLASS__;

      if (is_subclass_of($this, 'block_course_fisher_backend')) {
          return("Yes it is the right subclass");
      }
    
      return("Ops, it seems no to be the right subclass");
    } 
 
    public function description() {
      return(false);
    } 

    public function get_data() {
       return null;
    }

    public function get_user_field($matches) {
        global $USER;

        if (isset($matches[1])) {
            if (isset($USER->$matches[1])) {
                return $USER->$matches[1];
            }
        }
        return null;
    }

    public function __destruct() {
    }

}

?>
