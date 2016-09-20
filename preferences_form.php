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

               $singletext = '';
               $grouptext = '';

               $addsinglecoursestr = get_string('addsinglecourse', 'block_course_fisher');
               $addcoursegroupstr = get_string('addcoursegroup', 'block_course_fisher');

               if ((count($coursehashes) == 1) || !isset($CFG->block_course_fisher_forceonlygroups) || empty($CFG->block_course_fisher_forceonlygroups)) {
                   $coursecategories = html_writer::tag('span', $groupcourses[$selectedcoursehash]->path, array('class' => 'addcoursecategory'));
                   $coursename = html_writer::tag('span', $groupcourses[$selectedcoursehash]->fullname, array('class' => 'addcoursename'));
                   $singletext .= html_writer::tag ('span', $coursename.$coursecategories, array('class' => 'singlecourse'));
                   $courseidchoices[] = &$mform->createElement('radio', 'courseid', null, $addsinglecoursestr.$singletext, $selectedcoursehash);
               }
               if (count($coursehashes) > 1) {
                   $grouphash = implode('', $coursehashes);
                   $grouptext .= html_writer::start_tag ('span', array('class' => 'groupcourses'));
                   $first = true;
                   foreach ($groupcourses as $groupcourse) {
                       $class = 'groupcourse';
                       $alertmessage = '';
                       if ($first) {
                           $class .= ' groupfirstcourse';
                           $first = false;
                       }
                       $coursecategories = html_writer::tag('span', $groupcourse->path, array('class' => 'addcoursecategory'));
                       $coursename = html_writer::tag('span', $groupcourse->fullname, array('class' => 'addcoursename'));
                       if ($groupcourse->exists) {
                           $class .= ' existentcourse';
                           $alertmessage = html_writer::tag('span', get_string('existentcourse', 'block_course_fisher'), array('class' => 'existentcourse'));
                           $existscourse = true;
                           $courseurl = new moodle_url('/course/view.php', array('id' => $groupcourse->id));
                           $courselink = html_writer::tag('a', $groupcourse->fullname, array('href' => $courseurl, 'target' => '_blank'));
                           $coursename = html_writer::tag('span', $courselink, array('class' => 'addcoursename'));
                       }
                       $grouptext .= html_writer::tag ('span', $coursename.$alertmessage.$coursecategories, array('class' => $class));
                   }
                   $grouptext .= html_writer::end_tag ('span');
                   $courseidchoices[] = &$mform->createElement('radio', 'courseid', null, $addcoursegroupstr.$grouptext, $grouphash);
               }
               if (count($courseidchoices) == 2) {
                   $mform->addGroup($courseidchoices, 'coursegrp', get_string('choosewhatadd', 'block_course_fisher'), array(''), false);
                   $mform->setDefault('courseid', $grouphash);
               } else {
                   if (count($coursehashes) == 1) {
                       $mform->addElement('static', 'coursegrp', $addsinglecoursestr, $singletext);
                       $mform->addElement('hidden', 'courseid',  $selectedcoursehash);
                   } else {
                       $grouphash = implode('', $coursehashes);
                       $mform->addElement('static', 'coursegrp', $addcoursegroupstr, $grouptext);
                       $mform->addElement('hidden', 'courseid',  $grouphash);
                   }
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
