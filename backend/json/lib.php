<?php

class block_course_fisher_backend_json extends block_course_fisher_backend
{
  public function __construct()
  {
     parent::__construct();
  }

  public function description()
  {
    return("JSON backend");
  }

 
  
  public function get_data()
  {
    global $CFG;

    if($this->init())
    {
      $P=$this->getParser();
      $override=$P->parseFieldAssign($CFG->block_course_fisher_fieldtest);

      $Fld=array("block_course_fisher_fieldlevel",
                 "block_course_fisher_course_code",
                 "block_course_fisher_course_fullname",
                 "block_course_fisher_course_shortname",
                 "block_course_fisher_locator",
                 "block_course_fisher_parameters", 
                 "block_course_fisher_fieldtest");

      if(!(false===($this->checkCFG("block_course_fisher_fieldlist",$Fld,$override))))
      {
               
        
        $D=array();
        $j=file_get_contents($CFG->block_course_fisher_locator);
        $d=json_decode($j,true);
        while(list($k,$v)=each($d))
         {
        	// if($P->evalRecord($P->substituteObjects($CFG->block_course_fisher_parameters,$override),$v))
        	          {
        	           $D[] = (object)$v;
        	          }   
        
         }
        return($D);
        
      }

    } // init

    return(false);

  }

 
}
