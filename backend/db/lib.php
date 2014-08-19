<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir .'/adodb/adodb.inc.php');

class block_course_fisher_backend_db extends block_course_fisher_backend {

    public function __construct() {
        $this->name = 'db';
    }

    /**
     * Reads informations for a courses from external database,
     * then returns it in an array of objects.
     *
     * @return array
     */
    public function get_data() {
        global $CFG;

        $result = array();

        if (!empty($CFG->block_course_fisher_parameters)) {
            $sql = preg_replace_callback('/\[\%(\w+)\%\]/', 'parent::get_user_field', $CFG->block_course_fisher_parameters);
            if ($coursesdb = $this->db_init()) {
                $rs = $coursesdb->Execute($sql);
                if (!$rs) {
                    $coursesdb->Close();
                    debugging(get_string('auth_dbcantconnect','auth_db'));
                    debugging($sql);
                    return false;
                 } else {
                    if (!$rs->EOF) {
                        while ($fields_obj = $rs->FetchRow()) {
                            $fields_obj = (object)array_change_key_case((array)$fields_obj , CASE_LOWER);
                            $row = new stdClass();
                            foreach ($fields_obj as $name => $value) {
                                $row->$name = textlib::convert($value, 'iso8859-1', 'utf-8');
                            }
                            $result[] = $row;
                        }
                     }
                     $rs->Close();
                }
                $coursesdb->Close();
            } else {
                return false;
            }
        }

        return $result;
    }

    private function db_init() {
        global $CFG;

        // Connect to the external database (forcing new connection).
        $db = ADONewConnection($CFG->block_course_fisher_locator);
        if ($db) {
            $db->SetFetchMode(ADODB_FETCH_ASSOC);
        }

        return $db;
    }

}
