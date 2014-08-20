<?php

    function block_course_fisher_backend_lang($lang, $blockstrings) {
        global $CFG;

        $backends = scandir($CFG->dirroot.'/blocks/course_fisher/backend');
        foreach ($backends as $backend) {
            if (file_exists($CFG->dirroot.'/blocks/course_fisher/backend/'.$backend.'/lang/'.$lang.'/coursefisherbackend_'.$backend.'.php')) {
                $string = array();
                require_once($CFG->dirroot.'/blocks/course_fisher/backend/'.$backend.'/lang/'.$lang.'/coursefisherbackend_'.$backend.'.php');
                foreach ($string as $name => $translation) {
                    $blockstrings['backend_'.$backend.':'.$name] = $translation;
                }
            }
        }
        return $blockstrings;
    }

