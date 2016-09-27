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
 * Strings for component 'block_course_fisher', language 'it', branch 'MOODLE_20_STABLE'
 *
 * @package   block_course_fisher
 * @copyright Roberto Pinna <roberto.pinna@unipmn.it
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['course_fisher:addinstance'] = 'Aggiungere un nuovo blocco Course Fisher';
$string['course_fisher:myaddinstance'] = 'Aggiungere un nuovo blocco Course Fisheri nella Dashboard';
$string['course_fisher:addallcourses'] = 'Aggiungere tutti i corsi attivabili con il Course Fisher';
$string['course_fisher:addcourses'] = 'Aggiungere i propri corsi attivabili con il Course Fisher';
$string['pluginname'] = 'Course Fisher';
$string['configtitle'] = 'Titolo';
$string['courseguides'] = 'Guide corsi';
$string['courseregisters'] = 'Registri corsi';
$string['addmoodlecourse'] = 'Aggiungi corso moodle';
$string['addcourse'] = 'Aggiungi corso';
$string['nocourseavailable'] = 'Non ci sono corsi disponibili';
$string['coursegroup'] = 'Gruppo di corsi';
$string['addsinglecourse'] = 'Aggiungi corso singolo';
$string['entercourse'] = 'Accedi al corso';
$string['enroltocourse'] = 'Accedi al corso come docente';
$string['availablecourses'] = 'Corsi creabili';
$string['existentcourses'] = 'Corsi esistenti';
$string['backendfailure'] = 'Non &egrave; possibile collegarsi al backend per il recupero dei corsi';
$string['edit'] = 'Modifica le impostazioni corso';
$string['view'] = 'Visualizza il corso';
$string['import'] = 'Importa dati da un altro corso';
$string['coursenotfound'] = 'Corso non disponibile';
$string['filter'] = 'Filtro utenti';
$string['shown'] = 'Mostrato';
$string['hidden'] = 'Nascosto';
$string['nouserfilterset'] = 'Nessun filtro definito';
$string['ifuserprofilefield'] = 'se il campo del profilo utente';
$string['nocourseavailable'] = 'Spicente non ci sono corsi attivabili';
$string['courselink'] = 'Corso collegato';
$string['courselinkmessage'] = 'Questo corso &egrave; collegato con il corso di {$a}. Cliccare sul link qui sotto.';
$string['choosewhatadd'] = 'Scegli cosa aggiungere:';
$string['choosenextaction'] = 'Cosa vuoi fare dopo aver creato il corso:';
$string['execute'] = 'Esegui';
$string['chooseexistsaction'] = 'Alcuni corsi del gruppo di corsi risultano già esistenti. Cosa vuoi fare con questi corsi?';
$string['join'] = 'Uniscili al gruppo di corsi';
$string['separated'] = 'Mantienili separati dal gruppo di corsi';
$string['educationaloffer'] = 'Pagina dell\'offerta Formativa';
$string['educationaloffermessage'] = 'Qui puoi trovare tutte le informazioni sull\'offerta formativa di questo corso';
$string['coursenotifysubject'] = 'Course Fisher - Un nuovo corso creato richiede la tua attenzione!';
$string['coursenotifytext'] = 'Gentile Amministratore,
è necessario verificare il nuovo corso
{$a->coursefullname}

link al Corso: {$a->courseurl}';
$string['coursenotifytextcomplete'] = 'Gentile Amministratore,
è necessario verificare il nuovo corso
{$a->coursefullname}

link al Corso: {$a->courseurl}

link alla pagina dell\'offerta formativa: {$a->educationalofferurl}';
$string['coursenotifyhtml'] = 'Gentile Amministratore,<br />
è necessario verificare il nuovo corso<br />
<b>{$a->coursefullname}</b><br /><br />
link al Corso: <a href="{$a->courseurl}">{$a->courseurl}</a>';
$string['coursenotifyhtmlcomplete'] = 'Gentile Amministratore,<br />
è necessario verificare il nuovo corso<br />
<b>{$a->coursefullname}</b><br /><br />
link al Corso: <a href="{$a->courseurl}">{$a->courseurl}</a><br />
link alla pagina dell\'offerta formativa: <a href="{$a->educationalofferurl}">{$a->educationalofferurl}</a>';
$string['meta'] = 'Connessi con il metodo di iscrizione Meta Link nel corso padre';
$string['guest'] = 'Connessi con l\'accesso agli ospiti attivato per i corsi figli';
$string['existentcourse'] = 'Questo corso è già stato creato';
$string['notifycoursecreation'] = 'Invia la mail di avviso creazione corso a';
$string['confignotifycoursecreation'] = 'Invia la notifica di creazione corso agli utenti selezionati. La notifica verrà inviata solo agli utenti che hanno il ruolo selezionato.';
// Appende le traduzioni dei backend come backend_<nome backend>:stringa in questo modo bisogna utilizzare per esempio get_string('backend_db:pluginname', 'block_course_fisher')
require_once($CFG->dirroot.'/blocks/course_fisher/langlib.php');
$string = block_course_fisher_backend_lang('it', $string);
