<?php

    defined('MOODLE_INTERNAL') || die();

    define('COURSE_EDIT', '1');
    define('COURSE_VIEW', '0');

    require_once($CFG->dirroot .'/course/lib.php');
    require_once($CFG->libdir .'/coursecatlib.php');

    function block_course_fisher_create_categories($categories) {
        global $DB;
        $parentid = 0;

        foreach ($categories as $categoryname) {
            $newcategory = new stdClass();
            $newcategory->parent = $parentid;
            $newcategory->name = $categoryname;
            if (! $oldcategory = $DB->get_record('course_categories', array('name' => $newcategory->name, 'parent' => $newcategory->parent))) {
                $category = coursecat::create($newcategory);
            } else {
                $category = $oldcategory;
            }
            $parentid = $category->id;
        }

        return $category->id;
    }

   /**
    * Create a course, if not exits, and assign an editing teacher
    *
    * @param string course_fullname  The course fullname
    * @param string course_shortname The course shortname
    * @param string teacher_id       The teacher id code
    * @param array  categories       The categories from top category for this course
    *
    * @return object or null
    *
    **/
    function block_course_fisher_create_course($course_fullname, $course_shortname, $teacher_id, $categories = array()) {
        global $DB, $CFG;

        
        $newcourse = new stdClass();

        $newcourse->id = '0';
/*
        $newcourse->MAX_FILE_SIZE = '0';
        $newcourse->format = 'topics';
        $newcourse->showgrades = '0';
        $newcourse->enablecompletion = '0';
        $newcourse->numsections = '2';
*/
        $newcourse->startdate = time();
        
        $newcourse->fullname = $course_fullname;
        $newcourse->shortname = $course_shortname;

        $course = null;
        if (!$oldcourse = $DB->get_record('course', array('shortname' => $newcourse->shortname))) {
            $newcourse->category = block_course_fisher_create_categories($categories);
            if (!$course = create_course($newcourse)) {
                print_error("Error inserting a new course in the database!");
            }
        } else {
            $course = $oldcourse;
        }

        $editingteacherroleid = $DB->get_field('role', 'id', array('shortname' => 'editingteacher'));

        if ($teacheruser = $DB->get_record('user', array('id' => $teacher_id))) {
            // Set student role at course context
            $coursecontext = context_course::instance($course->id);

            $enrolled = false;
            // we use only manual enrol plugin here, if it is disabled no enrol is done
            if (enrol_is_enabled('manual')) {
                $manual = enrol_get_plugin('manual');
                if ($instances = enrol_get_instances($course->id, false)) {
                    foreach ($instances as $instance) {
                        if ($instance->enrol === 'manual') {
                            $manual->enrol_user($instance, $teacheruser->id, $editingteacherroleid, time(), 0);
                            $enrolled = true;
                            break;
                        }
                    }
                }
            }
        }

        return $course;
    }

    function block_course_fisher_format_fields($formatstring, $data) {

        $callback = function($matches) use ($data) { 
             return block_course_fisher_get_field($matches, $data); 
        }; 
       
        $formattedstring = preg_replace_callback('/\[\%(\w+)(([#+-])(\d+))?\%\]/', $callback, $formatstring);

        return $formattedstring;
    }

    function block_course_fisher_get_field($matches, $data) {
        $replace = null;

        if (isset($matches[1])) {
            if (isset($data->$matches[1]) && !empty($data->$matches[1])) {
                if (isset($matches[2])) {
                    switch($matches[3]) {
                        case '#':
                           $replace = substr($data->$matches[1], 0, $matches[4]);
                        break;
                        case '+':
                           $replace = $data->$matches[1]+$matches[4];
                        break;
                        case '-':
                           $replace = $data->$matches[1]-$matches[4];
                        break;
                    }
                } else {
                    $replace = $data->$matches[1];
                }
            }
        }
        return $replace;
    }

