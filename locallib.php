<?php

    defined('MOODLE_INTERNAL') || die();

    define('COURSE_EDIT', '1');
    define('COURSE_VIEW', '0');

    require_once($CFG->dirroot .'/course/lib.php');
    require_once($CFG->libdir .'/coursecatlib.php');

    function block_course_fisher_create_categories($categories) {
        global $DB;
        $parentid = 0;
        $result = null;

        foreach ($categories as $category) {
            $newcategory = new stdClass();
            $newcategory->parent = $parentid;
            $newcategory->name = $category->description;
       
            $searchquery = array('name' => $newcategory->name, 'parent' => $newcategory->parent);
            if (!empty($category->code)) {
                $newcategory->idnumber = $category->code;
                $searchquery = array('idnumber' => $newcategory->idnumber);
            }
            
            if (! $oldcategory = $DB->get_record('course_categories', $searchquery)) {
                $result = coursecat::create($newcategory);
            } else {
                $result = $oldcategory;
            }
            $parentid = $result->id;
        }

        return $result->id;
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
    function block_course_fisher_create_course($course_fullname, $course_shortname, $course_code, $teacher_id = 0, $categories = array(), $linkedcourse = null) {
        global $DB, $CFG;


        $newcourse = new stdClass();

        $newcourse->id = '0';

        $courseconfig = get_config('moodlecourse');

        // Apply course default settings
        $newcourse->format             = $courseconfig->format;
        $newcourse->newsitems          = $courseconfig->newsitems;
        $newcourse->showgrades         = $courseconfig->showgrades;
        $newcourse->showreports        = $courseconfig->showreports;
        $newcourse->maxbytes           = $courseconfig->maxbytes;
        $newcourse->groupmode          = $courseconfig->groupmode;
        $newcourse->groupmodeforce     = $courseconfig->groupmodeforce;
        $newcourse->visible            = $courseconfig->visible;
        $newcourse->visibleold         = $newcourse->visible;
        $newcourse->lang               = $courseconfig->lang;

        $newcourse->startdate = time();

        $newcourse->fullname = $course_fullname;
        $newcourse->shortname = $course_shortname;
        $newcourse->idnumber = $course_code;

        if ($linkedcourse !== null) {
            if (in_array('courselink', get_sorted_course_formats(true))) {
                $newcourse->format = 'courselink';
                $newcourse->linkedcourse = $linkedcourse->shortname;
            } else {
                $newcourse->format = 'singleactivity';
                $newcourse->activitytype = 'url';
            }
        }

        $course = null;
        if (!empty($course_code)) {
            $oldcourse = $DB->get_record('course', array('idnumber' => $course_code));
        } else {
            $oldcourse = $DB->get_record('course', array('shortname' => $course_shortname));
        }
        if (!$oldcourse) {
            $newcourse->category = block_course_fisher_create_categories($categories);
            if (!$course = create_course($newcourse)) {
                print_error("Error inserting a new course in the database!");
            }
            if (($linkedcourse !== null) && ($course->format == 'singleactivity')) {
                require_once($CFG->dirroot.'/course/modlib.php');

                $cw = get_fast_modinfo($course->id)->get_section_info(0);

                $urlresource = new stdClass();

                $urlresource->cmidnumber = null;
                $urlresource->section = 0;

                $urlresource->course = $course->id;
                $urlresource->name = get_string('courselink', 'block_course_fisher');
                $urlresource->intro = get_string('courselinkmessage', 'block_course_fisher', $linkedcourse->fullname);

                $urlresource->display = 0;
                $displayoptions = array();
                $displayoptions['printintro'] = 1;
                $urlresource->displayoptions = serialize($displayoptions);
                $urlresource->parameters = '';

                $urlresource->externalurl = $CFG->wwwroot.'/course/view.php?id='.$linkedcourse->id;
                $urlresource->timemodified = time();

                $urlresource->visible = $cw->visible;
                $urlresource->instance = 0;

                $urlresource->module = $DB->get_field('modules', 'id', array('name' => 'url', 'visible' => 1));
                $urlresource->modulename = 'url';

                add_moduleinfo($urlresource, $course);
            }
        } else {
            $course = $oldcourse;
        }

        $editingteacherroleid = $DB->get_field('role', 'id', array('shortname' => 'editingteacher'));

        if (!empty($teacher_id) && ($teacheruser = $DB->get_record('user', array('id' => $teacher_id)))) {
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

    function block_course_fisher_get_fields_items($field, $items = array('code' => 2, 'description' => 3)) {
        $result = array();
        if (!is_array($field)) {
            $fields = array($field);
        } else {
            $fields = $field;
        }

        foreach($fields as $element) {
            preg_match('/^((.+)\=\>)?(.+)?$/', $element, $matches);
            $item = new stdClass();
            foreach ($items as $itemname => $itemid) {
                if (!empty($matches) && !empty($matches[$itemid])) {
                    $item->$itemname = $matches[$itemid];
                }
            }
            if (count((array)$item)) {
                if (count($items) == 1) {
                    reset($items);
                    $result[] = $item->{key($items)};
                } else {
                    $result[] = $item;
                }
            }
        }

        if (!is_array($field)) {
            if (!empty($result)) {
                return $result[0];
            } else {
                return null;
            }
        } else {
            return $result;
        }
    }


   function block_course_fisher_get_fields_description($field) {
       return block_course_fisher_get_fields_items($field, array('description' => 3));
   }

   function block_course_fisher_add_metacourses($course, $metacourseids = array()) {
       global $CFG;

       if (enrol_is_enabled('manual')) {
           require_once("$CFG->dirroot/enrol/meta/locallib.php");

           $context = context_course::instance($course->id, MUST_EXIST);
           if (!empty($metacourseids) && has_capability('moodle/course:enrolconfig', $context)) {
               $enrol = enrol_get_plugin('meta');
               if ($enrol->get_newinstance_link($course->id)) {
                   foreach ($metacourseids as $metacourseid) {
                       $eid = $enrol->add_instance($course, array('customint1'=>$metacourseid));
                   }
               }
               enrol_meta_sync($course->id);
           }
       }
   }
