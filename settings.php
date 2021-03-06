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
 * Settings for the Course Fisher block.
 *
 * @package   block_course_fisher
 * @copyright 2014 and above Roberto Pinna, Diego Fantoma, Angelo Calò
 * @copyright 2016 and above Francesco Carbone
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

    $linkistruz = '<a href="'.$CFG->wwwroot.'/blocks/course_fisher/backendtest.php">'.' // '.new lang_string('configurationtest', 'block_course_fisher').'</a>';

    $settings->add(new admin_setting_heading('block_course_fisher_backendtestlink', '', $linkistruz));

    $settings->add(new admin_setting_heading('block_course_fisher_backend_config', new lang_string('configurationbackend', 'block_course_fisher'), ''));

    $settings->add(new admin_setting_configselect('block_course_fisher_backend', new lang_string('backend', 'block_course_fisher'),new lang_string('backendtype', 'block_course_fisher'),'', $choices));

    $settings->add(new admin_setting_configtextarea('block_course_fisher_locator',new lang_string('locatorurl', 'block_course_fisher'),new lang_string('sourceformat', 'block_course_fisher'), ''));

    $settings->add(new admin_setting_configtextarea('block_course_fisher_parameters',new lang_string('parameters', 'block_course_fisher'),new lang_string('parametersformat', 'block_course_fisher'), ''));

    $settings->add(new admin_setting_configtextarea('block_course_fisher_fieldtest',new lang_string('testvalue', 'block_course_fisher'),new lang_string('testvalueformat', 'block_course_fisher'), ''));

    $settings->add(new admin_setting_configtext('block_course_fisher_separator',new lang_string('separator', 'block_course_fisher'),new lang_string('separatoruse', 'block_course_fisher'), ''));

    $settings->add(new admin_setting_configcheckbox('block_course_fisher_firstrow', new lang_string('firstrow', 'block_course_fisher'),new lang_string('firstrowcontent', 'block_course_fisher'),0));

    $settings->add(new admin_setting_configtextarea('block_course_fisher_fieldlist',new lang_string('fieldlist', 'block_course_fisher'),new lang_string('fieldlistformat', 'block_course_fisher'), ''));

    $settings->add(new admin_setting_configtextarea('block_course_fisher_fieldlevel','Ordine dei campi ','uno per riga tra quelli indicati sopra, il primo corrisponde al primo livello di categoria. Usare campoCodice:campoDescrizione per associare il codice al nome', ''));

    $settings->add(new admin_setting_configtext('block_course_fisher_course_code','Codice del corso','Codice del corso', ''));

    $settings->add(new admin_setting_configtext('block_course_fisher_course_fullname','Campo nome completo del corso','Usare campoCodice:campoDescrizione per associare il codice al nome', ''));

    $settings->add(new admin_setting_configtext('block_course_fisher_course_shortname','Campo nome breve del corso','Usare campoCodice:campoDescrizione per associare il codice al nome', ''));


    $settings->add(new admin_setting_heading('block_course_fisher_groups', 'Generazione di gruppi di corsi', 'Da qui &egrave; possibile decidere se permettere di creare gruppi di corsi. Es. mutuazioni'));
    
    $settings->add(new admin_setting_configtext('block_course_fisher_course_group','Raggruppamento corsi','Campo_Codice_del_corso_Padre=Combinazione di codici o singolo codice che identifica univocamente il corso (in genere stesso valore che si mette nel campo codice corso)<br>es:[%mut_padre_cod%]=[%aa_offerta%]-[%cds_cod%]-[%pds_cod%]-[%aa_regdid%]-[%af_cod%]-[%partizione_codice%]', ''));

    $settings->add(new admin_setting_configcheckbox('block_course_fisher_forceonlygroups', 'Creazione solo gruppi di corsi','Forza la creazione solo dei gruppi di corsi, i docenti non potranno creare corsi figli singolarmente. I corsi singoli potranno essere creati comunque.',0));

    $choices = array();
    $choices['meta'] = get_string('meta', 'block_course_fisher');
    $choices['guest'] = get_string('guest', 'block_course_fisher');
    $settings->add(new admin_setting_configselect('block_course_fisher_linktype', 'Collegamento ai corsi figli','L\'accesso dai corsi figli al corso padre deve avvenire tramite', 'meta', $choices));

    $settings->add(new admin_setting_configtext('block_course_fisher_linked_course_category','Isola corsi figli in una categoria a parte','es. query o filtri get. Usare [%campo%] per sostituire i campi utente, p.es. [%uidnumber%]', ''));

    
    $settings->add(new admin_setting_heading('block_course_fisher_templating', 'Impostazioni di base del corso', 'Da qui &egrave; possibile decidere se includere alcune risorse/attivit&agrave; nei nuovi corsi'));
    
    $settings->add(new admin_setting_configtextarea('block_course_fisher_course_summary','Introduzione al corso','Testo da usare come descrizione dei nuovi corsi', ''));
    
    $settings->add(new admin_setting_configtext('block_course_fisher_sectionzero_name','Nome della prima sezione','Nome della prima sezione', ''));
    
    $settings->add(new admin_setting_configtext('block_course_fisher_educationaloffer_link','Formato del link alla scheda dell\'insegnamento','Formato del link alla scheda dell\'insegnamento. Se vuoto il link non verr&agrave; creato', ''));
    
    $settings->add(new admin_setting_configtext('block_course_fisher_course_template','Nome breve template','Se indicato, il contenuto del corso corrispondente verr&agrave; importato nel nuovo spazio', ''));


    $settings->add(new admin_setting_heading('block_course_fisher_general_config', 'Configurazioni generali', ''));

    $settings->add(new admin_setting_configtext('block_course_fisher_course_helplink',new lang_string('linkhelppage', 'block_course_fisher'), new lang_string('insertlink', 'block_course_fisher'), ''));

    $choices = array();
    $choices['view'] = get_string('view', 'block_course_fisher');
    $choices['edit'] = get_string('edit', 'block_course_fisher');
    $choices['import'] = get_string('import', 'block_course_fisher');
    $defaultchoices = array('view', 'edit', 'import');
    $settings->add(new admin_setting_configmultiselect('block_course_fisher_actions', 'Dopo la creazione del corso','Cosa fare dopo la creazione del corso', $defaultchoices, $choices));

    $settings->add(new admin_setting_configtextarea('block_course_fisher_email_condition','Condizione per invio mail ad account di supporto','es. query o filtri get. Usare [%campo%] per sostituire i campi utente, p.es. [%uidnumber%]', ''));
    $settings->add(new admin_setting_users_with_capability('block_course_fisher_notifycoursecreation', new lang_string('notifycoursecreation', 'block_course_fisher'), new lang_string('confignotifycoursecreation', 'block_course_fisher'), array(), 'block/course_fisher:addallcourses'));

    $settings->add(new admin_setting_configcheckbox('block_course_fisher_autocreation', 'Creazione automatica corsi','Se il backend lo prevede, è pssibile abilitare la creazione automatica dei corsirecuperati dal backend ad ogni esecuzione del cron ',0));

}

