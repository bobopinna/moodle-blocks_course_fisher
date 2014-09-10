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

  public function fetchToCache()
  {
    global $CFG;

    $P=$this->getParser();
    $c=0;
    $lines=array();
    $context = stream_context_create(array('http'=>array('timeout'=>1)));

//Opens cache files for writing
    if(!($fp1 = @fopen($CFG->dataroot.'/temp/block_course_fisher_cache1.tmp', 'w')))
    { return(false); }
    if(!($fp2 = @fopen($CFG->dataroot.'/temp/block_course_fisher_cache2.tmp', 'w')))
    { return(false); }

    if($fd = @fopen ($CFG->block_course_fisher_locator, "r", false, $context))
    {
      while (!feof ($fd) && $c<5000000) 
      { 
        $buffer = fgets($fd, 4096); 
        if(!($CFG->block_course_fisher_firstrow && $c==0))
        {
          $ray=$this->getRecord(rtrim($buffer));
          $strecords[$c]=$P->prepareRecord($CFG->block_course_fisher_parameters,$ray);
          $fullrecords[$c] = serialize($ray);

          fwrite($fp1,$strecords[$c]."\r\n");
          fwrite($fp2,$fullrecords[$c]."\r\n");
        }
        $c++;
      } 
      fclose ($fd);
      fclose ($fp1);
      fclose ($fp2);
      return($c);
    }
    return(false);
  }

  public function fetchFromCache($override=false)
  {
    global $CFG;

    $P=$this->getParser();
    $lines=array();

    if(false===($strecords=@file($CFG->dataroot.'/temp/block_course_fisher_cache1.tmp')))
    { return(false); }
 
    if(false===($fullrecords=@file($CFG->dataroot.'/temp/block_course_fisher_cache2.tmp')))
    { return(false); }

    $found=0;
    while( (list($Xk,$Xv)=each($strecords)) && $found==0)
    {
      if(eval($P->substituteObjects($Xv,$override)))
      {
        $lines[] = (object)unserialize($fullrecords[$Xk]);
      }
    }

    return($lines);
    
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
            $lines[] = (object)$ray;
          }
        }
        $c++;
      } 
      fclose ($fd);
    }
    return($lines);
  }

  
  public function get_data()
  {
    if($this->init())
    {
      $Fld=array("block_course_fisher_fieldlevel",
                 "block_course_fisher_course_code",
                 "block_course_fisher_course_fullname",
                 "block_course_fisher_course_shortname",
                 "block_course_fisher_locator",
                 "block_course_fisher_parameters", 
                 "block_course_fisher_fieldtest");

      if(!(false===($this->checkCFG("block_course_fisher_fieldlist",$Fld))))
      {
        if(isset($_SESSION['MoodleBlockCourseFisherCSV'.$_COOKIE['MoodleSession'.$CFG->sessioncookie]]))
        {
          $C=unserialize($_SESSION['MoodleBlockCourseFisherCSV'.$_COOKIE['MoodleSession'.$CFG->sessioncookie]]);
          if(is_array($C))
          {
           return($C);
          }
          return(array());
        }
        else
        {
          $C=$this->fetchFromCache();
          $_SESSION['MoodleBlockCourseFisherCSV'.$_COOKIE['MoodleSession'.$CFG->sessioncookie]]=serialize($this->fetchFromCache());
          return($C);
        }
        return(array());
      } // checkCFG

    } // init

    return(false);

  }

  public function cron()
  {
    global $CFG;


    if($this->init())
    {
      $P=$this->getParser();
      $override=$P->parseFieldAssign($CFG->block_course_fisher_fieldtest);

      $Fld=array("block_course_fisher_fieldlevel",
                 "block_course_fisher_course_fullname",
                 "block_course_fisher_course_shortname",
                 "block_course_fisher_locator",
                 "block_course_fisher_parameters", 
                 "block_course_fisher_fieldtest");
      if(!(false===($this->checkCFG("block_course_fisher_fieldlist",$Fld,$override))))
      {
        $this->fetchToCache();
      } // checkCFG
      else
      {
        print "CSVBACK ERROR:: ".$this->getError()."\r\n";
      }
    } // init

    return(true);
  }

}
