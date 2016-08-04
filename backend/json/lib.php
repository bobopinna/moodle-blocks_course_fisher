<?php

class block_course_fisher_backend_json extends block_course_fisher_backend {
    public function __construct() {
       parent::__construct();
    }

    public function description() {
       return("JSON backend");
    }

  
    public function get_data($alldata=false) {
        global $CFG;

        if ($this->init()) {
            $P = $this->getParser();
            $override = $P->parseFieldAssign($CFG->block_course_fisher_fieldtest);

            $Fld = array("block_course_fisher_fieldlevel",
                         "block_course_fisher_course_code",
                         "block_course_fisher_course_fullname",
                         "block_course_fisher_course_shortname",
                         "block_course_fisher_locator",
                         "block_course_fisher_parameters", 
                         "block_course_fisher_fieldtest");

            if (!(false===($this->checkCFG("block_course_fisher_fieldlist",$Fld,$override)))) {
                
				/*$D = array();
                $j = file_get_contents($CFG->block_course_fisher_locator);
                $d = json_decode($j,true);*/

				// aumento tempo di timeout
				$opts = array('http' =>
  
					array(
    					'method'  => 'GET',
						'header'=>"Content-Type: application/json; charset=utf-8",    
    					'timeout' => 500
  					)
				);
                       
				$context  = stream_context_create($opts);

				// carico il primo file utile alla lettura dell'offerta formativa
				$D = array();
				foreach(preg_split("/((\r?\n)|(\r\n?))/", $CFG->block_course_fisher_locator) as $line){
					$backend = $P->substituteObjects($line,false);
					$backend = str_replace("'", "", $backend);
                	$j = file_get_contents($backend, false, $context);
                	$d = json_decode($j,true);
					if($j && $d) 
						break;
				}
				

                while (list($k,$v)=each($d)) {
        
                    if ($alldata) { 
                        $D[] = (object)$v;
                    } elseif (eval($P->prepareRecord($P->substituteObjects($CFG->block_course_fisher_parameters,false),$v))) {
                        $D[] = (object)$v;
                    }
        
                }
                 return($D);
            }

        } // init

        return(false);

    }

}
