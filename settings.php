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
 * Settings for the RSS client block.
 *
 * @package   block_rss_client
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    require_once('backendlib.php');

    $choices = array();
    $backends = scandir($CFG->dirroot.'/blocks/course_fisher/backend');
    foreach ($backends as $backend) {
        if (file_exists($CFG->dirroot.'/blocks/course_fisher/backend/'.$backend.'/lib.php')) {
            require_once($CFG->dirroot.'/blocks/course_fisher/backend/'.$backend.'/lib.php');
            if (class_exists('block_course_fisher_backend_'.$backend)) {
                $choices[$backend] = get_string('backend_'.$backend.':pluginname', 'block_course_fisher');
            }
        }
    }
    
    $linkistruz = '<a href="'.$CFG->wwwroot.'/blocks/course_fisher/backendtest.php">'.' // '.'Test della configurazione'.'</a>';
    
    $settings->add(new admin_setting_heading('block_course_fisher_addheading', '', $linkistruz));

    $settings->add(new admin_setting_configselect('block_course_fisher_backend', 'Backend','tipo di backend da usare','', $choices));

    $settings->add(new admin_setting_configtextarea('block_course_fisher_locator','Locator (URL)','eg. file://path or mysql:username:password@host:port/database/table use multiple rows if want to use multiple sources in a first match order', ''));

    $settings->add(new admin_setting_configtextarea('block_course_fisher_parameters','Parametri da passare','es. query o filtri get. Usare [%campo%] per sostituire i campi utente, p.es. [%uidnumber%]', ''));

    $settings->add(new admin_setting_configtextarea('block_course_fisher_fieldtest','Valori per i test','uno per riga in forma [CAMPO]:valore', ''));

    $settings->add(new admin_setting_configtext('block_course_fisher_separator','separatore','separatore dei campi, usato solo dove serve (es. csv)', '')); 

    $settings->add(new admin_setting_configcheckbox('block_course_fisher_firstrow', 'Salta la prima riga','se contiene la lista dei campi CSV',0));

    $settings->add(new admin_setting_configtextarea('block_course_fisher_fieldlist','Lista dei campi ricevuti','uno per riga nell\'ordine in cui vengono ricevuti', ''));

    $settings->add(new admin_setting_configtextarea('block_course_fisher_fieldlevel','Ordine dei campi ','uno per riga tra quelli indicati sopra, il primo corrisponde al primo livello di categoria. Usare campoCodice:campoDescrizione per associare il codice al nome', ''));

    $settings->add(new admin_setting_configtext('block_course_fisher_course_code','Codice del corso','Codice del corso', ''));

    $settings->add(new admin_setting_configtext('block_course_fisher_course_fullname','Campo nome completo del corso','Usare campoCodice:campoDescrizione per associare il codice al nome', ''));
    
    $settings->add(new admin_setting_configtext('block_course_fisher_course_shortname','Campo nome breve del corso','Usare campoCodice:campoDescrizione per associare il codice al nome', ''));

    $choices = array();
    $choices[0] = get_string('viewcourse', 'block_course_fisher');
    $choices[1] = get_string('editcourse', 'block_course_fisher');
    $settings->add(new admin_setting_configselect('block_course_fisher_redirect', 'Dopo la creazione del corso','Cosa fare dopo la creazione del corso', 0, $choices));
}

