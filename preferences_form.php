<?php

   require_once($CFG->libdir.'/formslib.php');

   class preferences_form extends moodleform {
       function definition() {
           global $CFG;

           $mform = $this->_form;

           $selectedcoursehash = $this->_customdata['coursehash'];
           $groupcourses = $this->_customdata['groupcourses'];
           if (!empty($groupcourses)) {
               $coursehashes = array_keys($groupcourses);

               $courseidchoices = array();
               $existscourse = false;
               $coursecategories = html_writer::tag('span', $groupcourses[$selectedcoursehash]->path, array('class' => 'addcoursecategory'));
               $coursename = html_writer::tag('span', $groupcourses[$selectedcoursehash]->fullname, array('class' => 'addcoursename'));
               $singletext = get_string('addsinglecourse', 'block_course_fisher');
               $singletext .= $coursename.$coursecategories;
               $courseidchoices[] = &$mform->createElement('radio', 'courseid', null, $singletext, $selectedcoursehash);
               if (count($coursehashes) > 1) {
                   $grouphash = implode('', $coursehashes);
                   $grouptext = get_string('addcoursegroup', 'block_course_fisher');
                   $grouptext .= html_writer::start_tag ('span', array('class' => 'groupcourses'));
                   $first = true;
                   foreach ($groupcourses as $groupcourse) {
                       $class = 'groupcourse';
                       if ($first) {
                           $class .= ' groupfirstcourse';
                           $first = false;
                       }
                       if ($groupcourse->exists) {
                           $class .= ' existscourse';
                           $existscourse = true;
                       }
                       $coursecategories = html_writer::tag('span', $groupcourse->path, array('class' => 'addcoursecategory'));
                       $coursename = html_writer::tag('span', $groupcourse->fullname, array('class' => 'addcoursename'));
                       $grouptext .= html_writer::tag ('span', $coursename.$coursecategories, array('class' => $class));
                   }
                   $grouptext .= html_writer::end_tag ('span');
                   $courseidchoices[] = &$mform->createElement('radio', 'courseid', null, $grouptext, $grouphash);
               }
               if (count($courseidchoices) == 2) {
                   $mform->addGroup($courseidchoices, 'coursegrp', get_string('choosewhatadd', 'block_course_fisher'), array(''), false);
                   $mform->setDefault('courseid', $grouphash);
               } else {
                   $coursecategories = html_writer::tag('span', $groupcourses[$selectedcoursehash]->path, array('class' => 'addcoursecategory'));
                   $coursename = html_writer::tag('span', $groupcourses[$selectedcoursehash]->fullname, array('class' => 'addcoursename'));
                   $mform->addElement('static', 'coursegrp', get_string('addcourse', 'block_course_fisher'), $coursename.$coursecategories);
                   $mform->addElement('hidden', 'courseid',  $selectedcoursehash);
                   $mform->setType('courseid',  PARAM_ALPHANUM);
               }
             
               $existentchoices = array();
               if ($existscourse) {
                   $existentactions = array('join', 'separated');
                   foreach ($existentactions as $existentaction) {
                       $existentchoices[] = &$mform->createElement('radio', 'existent', null, get_string($existentaction, 'block_course_fisher'), $existentaction);
                   }
                   if (!empty($existentchoices)) {
                       $mform->addGroup($existentchoices, 'exitentgrp', get_string('chooseexistsaction', 'block_course_fisher'), array(''), false);
                       $mform->disabledIf('exitentgrp', 'courseid', 'neq', $grouphash);
                   }
               }
     
               $actionchoices = array();
               if (!empty($CFG->block_course_fisher_actions)) {
                   $permittedactions = explode(',', $CFG->block_course_fisher_actions);
                   foreach ($permittedactions as $permittedaction) {
                       $actionchoices[] = &$mform->createElement('radio', 'action', null, get_string($permittedaction, 'block_course_fisher'), $permittedaction);
                   }
                   if (!empty($actionchoices)) {
                       $mform->addGroup($actionchoices, 'actiongrp', get_string('choosenextaction', 'block_course_fisher'), array(''), false);
                   }
               }

               if ((count($courseidchoices) > 1) || (!empty($actionchoices) && (count($actionchoices) > 1)) || (!empty($existentchoices) && (count($existentchoices) > 1))) {
                   //normally you use add_action_buttons instead of this code
                   $mform->addElement('submit', 'submitbutton', get_string('execute', 'block_course_fisher'));
               } else if (!empty($actionchoices) && (count($actionchoices) == 1)) {
                   redirect(new moodle_url('/blocks/course_fisher/addcourse.php', array('courseid' => $selectedcoursehash, 'action' => $permittedactions[0])));
               } else {
                   redirect(new moodle_url('/blocks/course_fisher/addcourse.php', array('courseid' => $selectedcoursehash, 'action' => 'view')));
               }
           } else {
               redirect(new moodle_url('/blocks/course_fisher/addcourse.php'));
           }
       }
   }
