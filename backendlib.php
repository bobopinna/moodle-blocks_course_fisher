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

  public function getObjSep()
  {
    return($this->ObjSep);
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

//error_log(print_r($R, true));
     if(is_array($R))
     {
       if(is_array($R[1]) && is_array($R[2]))
       {
         if(isset($R[1][0]) && isset($R[2][0]))
         {
            $objectname = $R[1][0];
            $itemname = $R[2][0];
            if (is_object($this->Objects[$objectname]))
            {
              if (isset($this->Objects[$objectname]->$itemname))
              {

                  if(is_array($override))
                  {
                     if(isset($override[$this->LeftObjSep.$objectname.$this->ObjSep.$itemname.$this->RightObjSep]))
                     {
                       if(strlen(strval($override[$this->LeftObjSep.$objectname.$this->ObjSep.$itemname.$this->RightObjSep])))
                       {
                         return($override[$this->LeftObjSep.$objectname.$this->ObjSep.$itemname.$this->RightObjSep]);
                       }
                     }
                  } else {
                     if(strlen(strval($this->Objects[$objectname]->{$itemname})))
                     {
                        return($this->Objects[$objectname]->{$itemname});
                     }

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
      $Muniq=array();
      foreach ($M[1] as $Mk => $Mv)
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
    $errIdx=0;
    $this->ObjValues=array();
    $this->parseResult=true;
    $this->parseResultString="";

    preg_match_all("/".$this->LeftSep."(\w+|".$this->LeftObjSep."\w+".$this->ObjSep."\w+".$this->RightObjSep.")".$this->RightSep."/",$string2check,$M,PREG_PATTERN_ORDER);
    
    if(isset($M[1]))
    {
     $Muniq=array();
     $F=@array_flip($M[1]);

     foreach ($F as $Mk => $Mv)
     {
      if(strlen($Mk))
      {
        $Muniq[$Mk]=false;

        if(isset($this->Fields[$Mk]))
        {
           $Muniq[$Mk]=true;
        }
        else
        {
          if($allowVars)
          {
            if($this->parseObjectVariable($Mk,$allowVars))
            {
              $Muniq[$Mk]=$this->parseObjectVariable($Mk,$allowVars);
              $this->ObjValues[$Mk]=$Muniq[$Mk];
            }
          }
        }

        if($Muniq[$Mk]===false)
        {
         $this->parseResult=false;
//         $this->parseResultString="Not a valid field $Mk::$Mv -".print_r($F,1);
         $this->parseResultString="Not a valid field $Mk::$Mv -";
        }
      }
     }
    }
    if($this->parseResult)
    {
     return($Muniq);
    }
    return(false);
  }


  public function substituteObjects($string2check,$override=false)
  {
    $S=$string2check;
    $Muniq=$this->parseFields($S,$override);

    if( is_array($Muniq) )
    {
      foreach ($Muniq as $Mk => $Mv)
      {
        if(!($Mv===false))
        {
          if(substr($Mk,0,1)==$this->LeftObjSep && substr($Mk,-1)==$this->RightObjSep)
          {
             $setVal=$Mv;
             if(is_array($override))
             {
               if(isset($override[$Mk]))
               {
                 if(strlen(strval($override[$Mk])))
                 {
                   $setVal=$override[$Mk];
                 }
               }
             }
             $S=preg_replace('/'.$this->LeftSep.$Mk.$this->RightSep.'/',"'".$setVal."'",$S);
          }
        }
      }
    }
    return($S);
  }


  public function prepareRecord($string2check,$Record,$override=false)
  {
    $validation = false;
    $S=$string2check;

    if(is_array($Record))
    {
      foreach ($Record as $Fk => $Fv)
      {
        if (!is_array($Fv) && !is_object($Fv))
        {
          $S=preg_replace('/'.$this->LeftSep.$Fk.$this->RightSep.'/',"'".$Fv."'",$S);
        }
      }
      $validation = 'return (' . trim($S) . ') ? true : false;';
    }

    return($validation);

  }

  public function evalRecord($string2check,$Record,$override=false)
  {
    if (!empty($string2check)) {
        return(eval($this->prepareRecord($string2check,$Record,$override)));
    } else {
        return true;
    }
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

  public function getResultString()
  {
    return($this->Result);
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



  public function checkCFG($CFlist,$Cparm,$override=false)
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

                   $Muniq=$this->Parser->parseFields($CFG->$C,$override);
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



  public function get_data($alldata=false)
  {
    return null;
  }

  public function get_user_field($matches) {
      global $USER, $DB;

      if (isset($matches[1])) {
          $userfieldvalue = '';
          $customfields = $DB->get_records('user_info_field');
          if (!empty($customfields)) {
              foreach($customfields as $customfield) {
                  if ($customfield->shortname == $matches[1]) {
                      if (isset($USER->profile[$customfield->shortname]) && !empty($USER->profile[$customfield->shortname])) {
                          $userfieldvalue = $USER->profile[$customfield->shortname];
                      }
                  }
              }
          }
          if (empty($userfieldvalue)) {
              if (isset($USER->{$matches[1]})) {
                  $userfieldvalue = $USER->{$matches[1]};
              }
          }
          return $userfieldvalue;
      }

      return null;
  }

  public function __destruct()
  {
  }

}

