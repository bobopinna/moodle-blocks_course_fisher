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
 * @copyright  2014 adn above Angelo Calò
 * @copyright  2016 Francesco Carbone
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
               
                // carico il primo file utile alla lettura dell'offerta formativa
                $D = array();
                $jsondata = null;
                foreach (preg_split("/((\r?\n)|(\r\n?))/", $CFG->block_course_fisher_locator) as $line) {
                    $backend = ''; 
                    if ($alldata) { 
                        $backend = $P->substituteObjects($line, $override);
                    } else {
                        $backend = $P->substituteObjects($line, true);
                    }
                    if ((strpos($line, '[%') !== false) && ($backend == $line)) {
                        // Era prevista una sostituzione dei dati ma non Ã stata fatta. Esco.  
                        return(false);
                    }
                    $backend = str_replace('\'', '', $backend);

                    $jsonstring = download_file_content($backend, null, null, false, 500);
                    $jsondata = json_decode($jsonstring,true);

                    if ($jsonstring && $jsondata) {
                        break;
                    } else if (empty($jsonstring)) {
                        return(false);
                        // Il backend inserito non ha restituito dati, non esiste. 
                        print_error(curl_error($request).' l\'URL '.$backend.' inserito non &egrave; corretto');
                    } else if (!is_array($jsondata)) { 
                        switch (json_last_error()) {
                            case JSON_ERROR_NONE: 
                                print_error('No errors');
                            break;
                            case JSON_ERROR_DEPTH:
                                print_error('Maximum stack depth exceeded');
                            break;
                            case JSON_ERROR_STATE_MISMATCH:
                                print_error('Underflow or the modes mismatch');
                            break;
                            case JSON_ERROR_CTRL_CHAR:
                                print_error('Unexpected control character found');
                            break;
                            case JSON_ERROR_SYNTAX:
                                print_error('Syntax error, malformed JSON');
                            break;
                            case JSON_ERROR_UTF8:
                                print_error('Malformed UTF-8 characters, possibly incorrectly encoded');
                            break;
                            default:
                                print_error('Unknown error');
                            break;
                        }
                    }
                }

                foreach ($jsondata as $k => $v) {
        
                    if ($alldata) { 
                        $D[] = (object)$v;
                    } elseif ($P->evalRecord($P->substituteObjects($CFG->block_course_fisher_parameters, true),$v)) {
                        $D[] = (object)$v;
                    }
        
                }
                 return($D);
            }

        } // init

        return(false);

    }

}
