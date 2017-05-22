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
 * @copyright  2014 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_course_fisher_backend_soap extends block_course_fisher_backend {

    public function __construct() {
        $this->name = 'soap';
    }

    /**
     * Reads informations for teacher courses using SOAP requests,
     * then returns it in an array of objects.
     *
     * @return array
     */
    public function get_data($alldata=false) {
        global $CFG;

        $datas = array();

        if (!empty($CFG->block_course_fisher_parameters)) {
            $requestdata =  preg_replace_callback('/\[\%(\w+)\%\]/', 'parent::get_user_field', $CFG->block_course_fisher_parameters);
            if ($alldata) {
                $requestdata = preg_replace('/\[\%(\w+)\%\]/', '%', $CFG->block_course_fisher_parameters);
            }
            $requests = explode("\n", $requestdata);
            if (!empty($requests)) {
                foreach ($requests as $requestrow) {
                    if ($datas !== false) {
                        $request = new stdClass();
                        $request->url = $CFG->block_course_fisher_locator;
                        list($request->call, $request->parameter, $fieldlist) = explode('#', trim($requestrow));
                        $request->fields = explode(',', $fieldlist);
                        $datas = $this->soap_request($request, $datas);
                    }
                }
            } else {
                return false;
            }
        }

        return $datas;
    }

    /**
     * Sostituisce eventuali campi recuperati da query precedenti nei parametri da passare alla nuova query
     *
     * @string $parameter - stringa dei parametri [* e *] delimitano il nome di un campo presente in $data
     * @object $data - dati recuperati da una precedente query
     *
     * return  @string
     */
    private function replace_parameter($parameter, $data) {
        if (!empty($data)) {
            foreach ($data as $fieldname => $value) {
                $parameter = str_replace('[*'.$fieldname.'*]', $value, $parameter);
            }   
        }   
        return $parameter;
    }   

    /**
     * Effettua una o più richiestesoap e restituisce un'array con i dati recuperat
     *
     * @object $request - richiesta da effettuare che deve contenere i campi:
     *                    @string url - url delle APIs
     *                    @string call - nome della chiamata alle APIs
     *                    @string parameter - elenco dei parametri da passare alla API nel formato nome=valore separati da ;
     *                    @array  fields - elenco dei nomi dei campi da restituire
     * @array  $datas - dati restituiti da una precedente query
     *
     * return  @array
     */
    private function soap_request($request, $datas=array()) {

        $results = array();
    
        $data = current($datas);
    
        do {
            $parameter = $this->replace_parameter($request->parameter, $data);
            try {
                $soap = new SoapClient($request->url);
                $soapdata = $soap->fn_retrieve_xml_p($request->call, $parameter);
                if (!empty($soapdata) && isset($soapdata['fn_retrieve_xml_pReturn']) && ($soapdata['fn_retrieve_xml_pReturn'] == 1) && !empty($soapdata['xml'])) {
                    $soapxml = new SimpleXMLIterator($soapdata['xml']);
                    $soapxml->rewind();
                    for ($i=0; $i<count($soapxml->DataSet->Row); $i++) {
                        $result = new stdClass();
                        if (!empty($data)) {
                            foreach ($data as $fieldname => $value) {
                                $result->$fieldname = $value;
                            }   
                        }   
                        foreach($request->fields as $fieldname) {
                            if (isset($soapxml->DataSet->Row[$i]->$fieldname)) {
                                $result->$fieldname = (string) $soapxml->DataSet->Row[$i]->$fieldname;
                            } else {
                                $result->$fieldname = ''; 
                            }   
                        }   
                        $results[] = $result;
                    }   
                }   
            } catch (SoapFault $e) {
                return false;
            }   
            $data = next($datas);
        } while ($data !== false);

        return $results;
    }

}
