<?php
defined('MOODLE_INTERNAL') || die;

class block_course_fisher_parser {

  private $Objects=array();
  private $Fields=array();
  private $ObjValues=array();
  private $parseResult=false;
  private $parseResultString="";

  private $LeftSep="\[\%";
  private $RightSep="\%\]";
  private $LeftObjSep="!";
  private $RightObjSep="!";
  private $ObjSep=":";

  public function addObject($name, $Obj)
  {
    if(is_object($Obj) && strlen($name))
    {
      $this->Objects[$name]=$Obj;
    }
    else
    {
      return(false);
    }
  }

  public function getObjects()
  {
    return(count($this->Objects));
  }

  public function setFields($fld="")
  {
    if(strlen($fld))
    {
      $this->Fields=array_flip(preg_split("/\n|\s/",trim($fld),-1,PREG_SPLIT_NO_EMPTY));
      if(count($this->Fields))
      {
        return(count($this->Fields));
      }
    }
   return(false);
  }


  public function getLeftObjSep()
  {
    return($this->LeftObjSep);
  }

  public function getRightObjSep()
  {
    return($this->RightObjSep);
  }

  public function getLeftSep()
  {
    return($this->LeftSep);
  }

  public function getRightSep()
  {
    return($this->RightSep);
  }


  public function getFields()
  {
    return($this->Fields);
  }

  public function getResult()
  {
    return($this->parseResult);
  }

  public function getResultString()
  {
    return($this->parseResultString);
  }

  public function getObjValues()
  {
    return($this->ObjValues);
  }



  private function parseObjectVariable($Var,$override=false)
  {
     preg_match_all("/".$this->LeftObjSep."(\w+)".$this->ObjSep."(\w+)".$this->RightObjSep."/",$Var,$R,PREG_PATTERN_ORDER);

     if(is_array($R))
     {
       if(is_array($R[1]) && is_array($R[2]))
       {
         if(isset($R[1][0]) && isset($R[2][0]))
         {
            if(is_object($this->Objects[$R[1][0]]))
            {
              if(isset($this->Objects[$R[1][0]]->$R[2][0]))
              {

                  if(is_array($override))
                  {
                     if(isset($override[$this->LeftObjSep.$R[1][0].$this->ObjSep.$R[2][0].$this->RightObjSep]))
                     {
                       if(strlen(strval($override[$this->LeftObjSep.$R[1][0].$this->ObjSep.$R[2][0].$this->RightObjSep])))
                       {
                         return($override[$this->LeftObjSep.$R[1][0].$this->ObjSep.$R[2][0].$this->RightObjSep]);
                       }
                     }
                  }


                  if(strlen(strval($this->Objects[$R[1][0]]->$R[2][0])))
                  {
                    return($this->Objects[$R[1][0]]->$R[2][0]);
                  }
                  
              }
            }
         }
       }
     }
   return(false);
  }

  public function parseFieldAssign($string2check,$allowVars=false)
  {
    $M=array();
    $result=true;
    preg_match_all("/".$this->LeftSep."(".$this->LeftObjSep."\w+".$this->ObjSep."\w+".$this->RightObjSep.")".$this->RightSep."".$this->ObjSep."(\w+)"."/",$string2check,$M,PREG_PATTERN_ORDER);

    if(isset($M[1]) && isset($M[2]))
    {
      while(list($Mk,$Mv)=each($M[1]))
      {
        $Muniq[$Mv]=$M[2][$Mk];
      }
      return($Muniq);
    }

    return(false);
  }


  public function parseFields($string2check,$allowVars=false)
  {
    $M=array();
    $result=true;
    $this->ObjValues=array();
    $this->RecordValues=array();


    preg_match_all("/".$this->LeftSep."(\w+|".$this->LeftObjSep."\w+".$this->ObjSep."\w+".$this->RightObjSep.")".$this->RightSep."/",$string2check,$M,PREG_PATTERN_ORDER);
    if(isset($M[1]))
    {
     $Muniq=array();
     $F=@array_flip($M[1]);
     while(list($Mk,$Mv)=each($F))
     {
       $Muniq[$Mk]=false;
       if(isset($this->Fields[$Mk]))
       {
          $Muniq[$Mk]=true;
          $this->parseResultString="";
       }
       else
       {
         if($allowVars)
         {
           if($this->parseObjectVariable($Mk,$allowVars))
           {
             $Muniq[$Mk]=$this->parseObjectVariable($Mk,$allowVars);
             $this->ObjValues[$Mk]=$Muniq[$Mk];
             $this->parseResultString="";
           }
           else
           {
             $this->parseResultString="Not a valid object name";
           }
         }
       }
      
      }
     if(!$Muniq[$Mk]) {$result=false;}
    }
    $this->parseResult=$result;
    return($Muniq);
  }


  public function substituteObjects($string2check,$override=false)
  {
    $S=$string2check;
    if( is_array($Muniq=$this->parseFields($S,$this->Fields,1) ) )
    {

      while(list($Mk,$Mv)=each($Muniq))
      {
        if(!($Mv===false))
        {
          if(substr($Mk,0,1)==$this->LeftObjSep && substr($Mk,-1)==$this->RightObjSep)
          {
            $S=preg_replace('/\[\%'.$Mk.'\%\]/',"'".$Mv."'",$S);
          }
        }
      }

    }
    return($S);
  }


  public function evalRecord($string2check,$Record,$override=false)
  {
    $validation = false;
    $S=$string2check;

    if(is_array($Record))
    {
      while(list($Fk,$Fv)=each($Record))
      {
        $S=preg_replace('/'.$this->LeftSep.$Fk.$this->RightSep.'/',"'".$Fv."'",$S);
      }
      $validation = 'return (' . $S . ') ? true : false;';
    }

    return(eval($validation));

  }


} // class block_course_fisher_parser





class block_course_fisher_backend {

  public  $name;
  private $error="";
  private $FldSepStart="[%";
  private $FldSepEnd="%]";
  private $Parser;
  private $BackendFields=array();


  public function __construct() 
  {
    $this->name="Course fisher backend class";
    $this->Parser=new block_course_fisher_parser();
    $this->BackendFields=array(
                               "year" =>date('Y'),
                               "month"=>date('m'),
                               "day"  =>date('d')
                              );
  }



  public function init()
  {
    global $CFG,$USER,$COURSE;

    if(!is_subclass_of($this, 'block_course_fisher_backend'))
    {
      $this->error="Woops, wrong class initialized";
      return(false);
    }

    if(!$CFG->block_course_fisher_backend==__CLASS__)
    {
      $this->error="The name of the configured backend doesn't match the called class";
      return(false);
    }

    $this->Parser->addObject("USER",$USER);
    $this->Parser->addObject("COURSE",$COURSE);
    $this->Parser->addObject("BACKEND", (object) $this->BackendFields );
    $this->error="";
    return(true);
  } 
 


  public function checkCFG()
  {
    global $CFG;
    $result=true;
    $this->error="";

    if(isset($CFG->$CFlist))
    {

      if(strlen($CFG->$CFlist))
      {
        if($this->Parser->setFields($CFG->$CFlist))
        {

          if(is_array($Cparm))
          {
            foreach($Cparm as $C)
            {

              if(isset($CFG->$C))
              {
                if(strlen($CFG->$C))
                {
                   
                   $Muniq=$this->Parser->parseFields($CFG->$C,1);
                   if($this->Parser->getResult()===false)
                   {
                     $result=false;
                     $this->error=$C.": ".$this->Parser->getResultString();
                   }

                }
              }

            }
          }

        }
      }

    }

   return($result);

  }

  public function getParser()
  {
    return($this->Parser);
  }

  public function getError()
  {
    return($this->error);
  }



  public function getConf()
  {
    global $CFG;

   // $out=__CLASS__;

    if(is_subclass_of($this, 'block_course_fisher_backend'))
    {
      return("Yes it is the right subclass");
    }
    
    return("Ops, it seems no to be the right subclass");
  } 
 


  public function description()
  {
    return(false);
  } 



  public function get_data() 
  {
    return false;
  }



  public function get_user_field($matches) 
  {
    global $USER;
    if (isset($matches[1])) 
    {
      if (isset($USER->$matches[1])) 
      {
        return $USER->$matches[1];
      }
    }
    return null;
  }


  public function __destruct() 
  {
  }

}
