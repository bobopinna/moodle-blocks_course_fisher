<?php

class block_course_fisher_backend_csv extends block_course_fisher_backend
{
  public function __construct()
  {
     parent::__construct();
  }

  public function description()
  {
    return("CSV backend");
  }

  public function __destruct()
  {
     parent::__destruct();
  }

  private function getRecord($CSVstring)
  {
   global $CFG;
   $ray=array();
   $P=$this->getParser();
   $FLD=array_flip($P->getFields());
   $t=preg_split("/".$CFG->block_course_fisher_separator."/",$CSVstring);

   while(list($tk,$tv)=each($t))
   {
     if(isset($FLD[$tk]))
     {
       $ray[$FLD[$tk]]=$tv;
     }
   }
   return($ray);
  }



  public function HTTPfetch($useTestVals=false)
  {
    global $CFG;

    $P=$this->getParser();
    $c=0;
    $lines=array();
    $context = stream_context_create(array('http'=>array('timeout'=>1)));

    $override=false;
    if($useTestVals)
    {
      $override=$P->parseFieldAssign($CFG->block_course_fisher_fieldtest);
    }

    if($fd = fopen ($CFG->block_course_fisher_locator, "r", false, $context))
    {
      while (!feof ($fd) && $c<500000) 
      { 
        $buffer = fgets($fd, 4096); 
        if(!($CFG->block_course_fisher_firstrow && $c==0))
        {
          $ray=$this->getRecord(rtrim($buffer));
          if($P->evalRecord($P->substituteObjects($CFG->block_course_fisher_parameters,$override),$ray) )
          {
            $lines[] = $ray;
          }
        }
        $c++;
      } 
      fclose ($fd);
    }
    return($lines);
  }

  
