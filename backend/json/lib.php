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
                foreach (preg_split("/((\r?\n)|(\r\n?))/", $CFG->block_course_fisher_locator) as $line) {
                    $backend = ''; 
                    if ($alldata) { 
                        $backend = $P->substituteObjects($line, $override);
                    } else {
                        $backend = $P->substituteObjects($line, true);
                    }
                    $backend = str_replace('\'', '', $backend);

                    $json = download_file_content($backend, null, null, false, 500);
                    $data = json_decode($json,true);

                    if ($json && $data) {
                        break;
                    }

                }

                while (list($k,$v)=each($data)) {
        
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
