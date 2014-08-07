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
    public function get_teacher_courses($teacherid) {
        $coursesdb = $this->db_init();
        $thisyear = date('Y');
        $lastyear = $thisyear-1;

        // Array to map local fieldnames we want, to external fieldnames.
        $selectfields = array();
        $selectfields['aa'] = 'AA_ID';
        $selectfields['dep_fullname'] = 'ANVUR.DIP_DES';
        $selectfields['cds_fullname'] = 'ANVUR.NOME_CDS';
        $selectfields['cds_type'] = 'ANVUR.TIPO_CORSO_COD';
        $selectfields['course_id'] = 'ANVUR.AF_GEN_COD';
        $selectfields['course_fullname'] = 'case when (ANVUR_AF.DES is null) or (lower(ANVUR.AF_GEN_DES) like (lower(ANVUR_AF.DES)+\'%\')) then ANVUR.AF_GEN_DES else ANVUR_AF.DES + \': \' + ANVUR.AF_GEN_DES end';
        $selectfields['site'] = 'ANVUR.SEDE_DES';

        $result = array();
        // If at least one field is mapped from external db, get that mapped data.
        if ($selectfields) {
            $select = array();
            foreach ($selectfields as $localname=>$externalname) {
                $select[] = "$externalname AS $localname";
            }
            $select = implode(', ', $select);
            $sql = 'SELECT '.$select.'
                      FROM ANVUR 
                 LEFT JOIN ANVUR_AF ON ANVUR.AF_PDR_ID=ANVUR_AF.AF_ID
                     WHERE ANVUR.MATRICOLA = "'.$teacherid.'"
                       AND (ANVUR.AA_ID = '.$thisyear.' OR ANVUR.AA_ID = '.$lastyear.')
                  GROUP BY ANVUR.AA_ID, ANVUR.DIP_DES, ANVUR.TIPO_CORSO_COD, ANVUR.NOME_CDS, ANVUR.AF_GEN_COD, ANVUR.AF_GEN_DES, ANVUR_AF.DES, ANVUR.DES_TIPO_CICLO, ANVUR_AF.DES_TIPO_CICLO, ANVUR.MATRICOLA, ANVUR.SEDE_DES
                  ORDER BY aa ASC, dep_fullname ASC, cds_fullname ASC, course_fullname ASC, site ASC';
            $rs = $coursesdb->Execute($sql);
            if (!$rs) {
                $coursesdb->Close();
                debugging(get_string('auth_dbcantconnect','auth_db'));
                debugging($sql);
                return $result;
             } else {
                if (!$rs->EOF) {
                    while ($fields_obj = $rs->FetchRow()) {
                        $fields_obj = (object)array_change_key_case((array)$fields_obj , CASE_LOWER);
                        $row = new stdClass();
                        foreach ($selectfields as $localname=>$externalname) {
                            $row->$localname = textlib::convert($fields_obj->{$localname}, 'iso8859-1', 'utf-8');
                        }
                        $result[] = $row;
                    }
                 }
                 $rs->Close();
            }
        }
        $coursesdb->Close();

        return $result;
    }

    private function db_init() {
        // Connect to the external database (forcing new connection).
        $db = ADONewConnection('mssql');
        $db->Connect('193.206.59.249', 'bobo', 'robertopinna', 'UGOV', true);
        $db->SetFetchMode(ADODB_FETCH_ASSOC);

        return $db;
    }

}
