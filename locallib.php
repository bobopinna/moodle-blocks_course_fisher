<?php

    defined('MOODLE_INTERNAL') || die();

    require_once($CFG->dirroot .'/course/lib.php');
    require_once($CFG->libdir .'/coursecatlib.php');

    function block_course_fisher_create_categories($categories) {
        global $DB;
        $parent = 0;

        $parentcategories = explode(',', $this->config->categories);
        foreach ($parentcategories as $categoryfieldname) {
            $newcategory = new stdClass();
            $newcategory->parent = $parent;
            if (isset($categories[$categoryfieldname])) {
                $newcategory->name = $categories[$categoryfieldname];
                if (! $oldcategory = $DB->get_record('course_categories', array('name' => $newcategory->name, 'parent' => $newcategory->parent))) {
                    $category = coursecat::create($newcategory);
                } else {
                    $category = $oldcategory->id;
                }
                $parent = $category;
            }
        }

        return $category;
    }

   /**
    * Create a course, if not exits, and assign an editing teacher
    *
    * @param string course_fullname The course name
    * @param string course_id       The course code
    * @param int    year            The academic year
    * @param string teacher_id      The teacher id code
    * @param array  categories      The academic year for this course
    *
    * @return object or null
    *
    **/
    function block_course_fisher_create_course($course_fullname, $course_id, $year, $teacher_id, $extra = array()) {
        global $DB, $CFG;

        $shortnamemaxlength = 20;
        
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
        
        $endyear = $year+1;       
        $newcourse->fullname = $year.'/'.$endyear.' '.$course_fullname;
        $newcourse->shortname = textlib::substr($year.'-'.$course_id.' '.$course_fullname, 0, $shortnamemaxlength).'...';
        $newcourse->idnumber = $year.'-'.$course_id;

        $newcourse->summary = get_string('course_summary','block_course_fisher').$course_fullname;

        $course = null;
        if (!$oldcourse = $DB->get_record('course', array('shortname' => $newcourse->shortname))) {
            $newcourse->category = block_course_fisher_create_categories($extra);
            if (!$course = create_course($newcourse)) {
                print_error("Error inserting a new course in the database!");
            }
        } else {
            $course = $oldcourse;
        }

        $editingteacherroleid = $DB->get_field('role', 'id', array('shortname' => 'editingteacher'));

        if ($teacheruser = $DB->get_record('user', array('idnumber' => $teacher_id))) {
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
