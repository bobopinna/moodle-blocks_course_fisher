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
 * Form for editing Course Fischer block instances.
 *
 * @package   block_course_fischer
 * @copyright 2014 Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Form for editing Course Fisher block instances.
 *
 * @copyright 2014 Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_course_fisher_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG, $DB;

        // Fields for editing Course Fischer block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_course_fisher'));
        $mform->setType('config_title', PARAM_TEXT);

        $choices = array('shown' => get_string('shown', 'block_course_fisher'),
                         'hidden' => get_string('hidden', 'block_course_fisher'));
        $filters[] = &$mform->createElement('select','config_display', 'shown', $choices);
        $filters[] = &$mform->createElement('static', 'config_ifuserprofilefield', null,  get_string('ifuserprofilefield', 'block_course_fisher').'<br />');

        $fieldnames = array('lastname', 'firstname', 'username', 'email', 'city', 'idnumber', 'institution', 'department', 'address');
        $fields = array('-' => get_string('choose'));
        foreach ($fieldnames as $fieldname) {
            $fields[$fieldname] = get_string($fieldname);
        }
        
        $customfields = $DB->get_records('user_info_field');
        if (!empty($customfields)) {
           foreach($customfields as $customfield) {
               $fields[$customfield->shortname] = $customfield->name;
           }
        }

        $filters[] = &$mform->createElement('select','config_userfield', null, $fields);
    
        $operators = array('contains' => get_string('contains', 'filters'),
                         'doesnotcontain' => get_string('doesnotcontain', 'filters'),
                         'isequalto' => get_string('isequalto', 'filters'),
                         'isnotequalto' => get_string('isnotequalto', 'filters'),
                         'startswith' => get_string('startswith', 'filters'),
                         'endswith' => get_string('endswith', 'filters'));
        $filters[] = &$mform->createElement('select','config_operator', null, $operators);

        $filters[] = &$mform->createElement('text','config_matchvalue');
        $mform->setType('config_matchvalue', PARAM_TEXT);

        $mform->addGroup($filters, 'config_filter', get_string('filter', 'block_course_fisher'), ' ', false);
    }

    function set_data($defaults) {
        if (!$this->block->user_can_edit()) {
            if  (!empty($this->block->config->title)) {
                 // If a title has been set but the user cannot edit it format it nicely
                 $title = $this->block->config->title;
                 $defaults->config_title = format_string($title, true, $this->page->context);
                 // Remove the title from the config so that parent::set_data doesn't set it.
                 unset($this->block->config->title);
            }
            if  (!empty($this->block->config->userfield) && $this->block->config->userfield != '-') {
                 $userfield = $this->block->config->userfield;
                 $defaults->config_userfield = format_string($userfield, true, $this->page->context);
                 unset($this->block->config->userfield);
                 if  (!empty($this->block->config->display)) {
                      $display = $this->block->config->display;
                      $defaults->config_display = format_string($display, true, $this->page->context);
                      unset($this->block->config->display);
                 }
                 if  (!empty($this->block->config->operator)) {
                      $operator = $this->block->config->operator;
                      $defaults->config_operator = format_string($operator, true, $this->page->context);
                      unset($this->block->config->operator);
                 }
                 if  (!empty($this->block->config->matchvalue)) {
                      $matchvalue = $this->block->config->matchvalue;
                      $defaults->config_matchvalue = format_string($matchvalue, true, $this->page->context);
                      unset($this->block->config->matchvalue);
                 }
            }
        }

        // have to delete text here, otherwise parent::set_data will empty content
        // of editor
        parent::set_data($defaults);
        // restore $text
        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }
        if (isset($title)) {
            // Reset the preserved title
            $this->block->config->title = $title;
        }
        if (isset($userfield)) {
            $this->block->config->userfield = $userfield;
            if (isset($display)) {
                $this->block->config->display = $display;
            }
            if (isset($operator)) {
                $this->block->config->operator = $operator;
            }
            if (isset($mathvalue)) {
                $this->block->config->mathvalue = $mathvalue;
            }
        }
    }
}
