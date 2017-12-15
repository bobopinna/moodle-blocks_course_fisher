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
 * Course fisher
 *
 * @package    blocks
 * @subpackage course_fisher
 * @copyright 2014 and above Roberto Pinna, Diego Fantoma, Angelo Calò
 * @copyright 2016 and above Francesco Carbone
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
            if (!empty($category->description)) {
                $newcategory = new stdClass();
                $newcategory->parent = $parentid;
                $newcategory->name = trim($category->description);

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
    function block_course_fisher_create_course($coursedata, $teacher_id = 0, $categories = array(), $linkedcourse = null, $existent) {
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
        if (isset($courseconfig->courseduration) && !empty($courseconfig->courseduration)) {
            $newcourse->enddate = time() + $courseconfig->courseduration;
        }

        $newcourse->fullname = $coursedata->fullname;
        $newcourse->shortname = $coursedata->shortname;
        $newcourse->idnumber = $coursedata->idnumber;
        if (isset($coursedata->summary) && !empty($coursedata->summary)) {
            $newcourse->summary = $coursedata->summary;
        }

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
        if (!empty($coursedata->idnumber)) {
            $oldcourse = $DB->get_record('course', array('idnumber' => $coursedata->idnumber));
        } else {
            $oldcourse = $DB->get_record('course', array('shortname' => $coursedata->shortname));
        }
        if (!$oldcourse) {
            $newcourse->category = block_course_fisher_create_categories($categories);
            if (!$course = create_course($newcourse)) {
                print_error("Error inserting a new course in the database!");
            }
            if ($coursedata->notifycreation) {

                $notifyinfo = new stdClass();
                $notifyinfo->coursefullname = $coursedata->fullname;
                $notifyinfo->courseurl = new moodle_url('/course/view.php', array('id' => $course->id));

                $notifysubject = get_string('coursenotifysubject', 'block_course_fisher');
                $notifytext = get_string('coursenotifytext', 'block_course_fisher', $notifyinfo);
                $notifyhtml = get_string('coursenotifyhtml', 'block_course_fisher', $notifyinfo);
                if (isset($coursedata->educationofferurl) && !empty($coursedata->educationofferurl)) {
                    $notifyinfo->educationalofferurl = $coursedata->educationofferurl;
                    $notifytext = get_string('coursenotifycomplete', 'block_course_fisher', $notifyinfo);
                    $notifyhtml = get_string('coursenotifyhtmlcomplete', 'block_course_fisher', $notifyinfo);
                }

                if (!isset($CFG->block_course_fisher_notifycoursecreation)) {
                    $CFG->block_course_fisher_notifycoursecreation = '$@NONE@$';
                }
                $recip = get_users_from_config($CFG->block_course_fisher_notifycoursecreation, 'block/course_fisher:addallcourses');
                foreach ($recip as $user) {
                    if (! email_to_user($user, \core_user::get_support_user(), $notifysubject, $notifytext, $notifyhtml)) {
                        mtrace('Error: Could not send out mail to user '.$user->id.' ('.$user->email.')');
                    }
                }

            }
            if (($linkedcourse !== null)) {
                if (isset($CFG->block_course_fisher_linktype) && ($CFG->block_course_fisher_linktype == 'guest')) {
                    if (enrol_is_enabled('guest')) {
                        $guest = enrol_get_plugin('guest');
                        $has_guest = false;
                        if ($instances = enrol_get_instances($course->id, false)) {
                            foreach ($instances as $instance) {
                                if ($instance->enrol === 'guest') {
                                    $guest->update_status($instance, ENROL_INSTANCE_ENABLED);
                                    $has_guest = true;
                                }
                                if ($instance->enrol !== 'guest') {
                                    $guest->update_status($instance, ENROL_INSTANCE_DISABLED);
                                }
                            }
                        }
                        if (!$has_guest) {
                            $guest->add_instance($course);
                        }
                    }
                }

                if (($course->format == 'singleactivity')) {
                   block_course_fisher_add_linkedcourse_url($course, $linkedcourse);
                }

            } else {
                // Set default name for section 0
                if (isset($coursedata->sectionzero) && !empty($coursedata->sectionzero)) {
                    $DB->set_field('course_sections', 'name', $coursedata->sectionzero, array('section' => 0, 'course' => $course->id));
                }

                // Add Educational offer external link
                if (isset($coursedata->educationalofferurl) && !empty($coursedata->educationalofferurl)) {
                    require_once($CFG->dirroot.'/course/modlib.php');
                    $url = new stdClass();
                    $url->module = $DB->get_field('modules', 'id', array('name' => 'url', 'visible' => 1));
                    $url->name = get_string('educationaloffer', 'block_course_fisher');
                    $url->intro = get_string('educationaloffermessage', 'block_course_fisher');
                    $url->externalurl = $coursedata->educationalofferurl;
                    // open the url in a new tab
                    $url->display = 3;
                    $url->cmidnumber = null;
                    $url->visible = 1;
                    $url->instance = 0;
                    $url->section = 0;
                    $url->modulename = 'url';
                    add_moduleinfo($url, $course);
                }

                // Import course activities and resources from template
                if (isset($coursedata->templateshortname) && !empty($coursedata->templateshortname)) {
                    require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
                    require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
                    $templateid = $DB->get_field('course', 'id', array('shortname' => $coursedata->templateshortname));
                    if (!$templateid) {
                        print_error("Error importing course template content!");
                    } else {
                        $primaryadmin = get_admin();

                        $bc = new backup_controller(backup::TYPE_1COURSE, $templateid, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_IMPORT, $primaryadmin->id);
                        $bc->execute_plan();

                        $rc = new restore_controller($bc->get_backupid(), $course->id, backup::INTERACTIVE_NO, backup::MODE_IMPORT, $primaryadmin->id, backup::TARGET_EXISTING_ADDING);
                        $rc->execute_precheck();
                        $rc->execute_plan();
                    }
                }
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
            if (isset($data->{$matches[1]}) && !empty($data->{$matches[1]})) {
                if (isset($matches[2])) {
                    switch($matches[3]) {
                        case '#':
                           $replace = substr($data->{$matches[1]}, 0, $matches[4]);
                        break;
                        case '+':
                           $replace = $data->{$matches[1]}+$matches[4];
                        break;
                        case '-':
                           $replace = $data->{$matches[1]}-$matches[4];
                        break;
                    }
                } else {
                    $replace = $data->{$matches[1]};
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

    function block_course_fisher_add_linkedcourse_url($course, $linkedcourse) {
        global $CFG, $DB;

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

   function block_course_fisher_add_metacourses($course, $metacourseids = array()) {
       global $CFG;

       if (enrol_is_enabled('meta')) {
           $context = context_course::instance($course->id, MUST_EXIST);
           if (!empty($metacourseids) && has_capability('moodle/course:enrolconfig', $context)) {
               $enrol = enrol_get_plugin('meta');
               $context = context_course::instance($course->id, MUST_EXIST);
               if (has_capability('enrol/meta:config', $context)) {
                   foreach ($metacourseids as $metacourseid) {
                       $eid = $enrol->add_instance($course, array('customint1'=>$metacourseid));
                   }
               }
           }
       }
   }

   function block_course_fisher_get_coursehashes($courses) {
       global $CFG;

       // Generate courses hash
       $hashedcourses = array();
       if (!empty($courses)) {
           foreach ($courses as $i => $course) {
               $courseidnumber = '';
               $courseshortname = block_course_fisher_format_fields($CFG->block_course_fisher_course_shortname, $course);
               if (isset($CFG->block_course_fisher_course_code) && !empty($CFG->block_course_fisher_course_code)) {
                   $courseidnumber = block_course_fisher_format_fields($CFG->block_course_fisher_course_code, $course);
               }
               $coursecode = !empty($courseidnumber)?$courseidnumber:$courseshortname;

               $fieldlist = block_course_fisher_format_fields($CFG->block_course_fisher_fieldlevel, $course);
               $categories = block_course_fisher_get_fields_description(array_filter(explode("\n", $fieldlist)));
               $coursepath = implode(' / ', $categories);
               $coursehash = md5($coursepath.' / '.$coursecode);
               $hashedcourses[$coursehash] = $course;
           }
       }
       return $hashedcourses;
   }

   function block_course_fisher_get_groupcourses($courses, $selectedcoursehash, $coursedata) {
       global $CFG, $DB;

       $groupcourses = array();

       $selectedcourse = $courses[$selectedcoursehash];

       $coursedata->exists = false;

       $firstcoursematch = null;
       $othercoursesmatch = null;
       if (isset($CFG->block_course_fisher_course_group) && !empty($CFG->block_course_fisher_course_group)) {
           $firstcoursematch = substr($CFG->block_course_fisher_course_group, strpos($CFG->block_course_fisher_course_group,"=")+1);
           $othercoursesmatch = substr($CFG->block_course_fisher_course_group, 0, strpos($CFG->block_course_fisher_course_group,"="));

           /* Search for course group leader and members */
           $firstcourseid = block_course_fisher_format_fields($firstcoursematch, $selectedcourse);
           $othercourseid = block_course_fisher_format_fields($othercoursesmatch, $selectedcourse);

           if (!empty($othercourseid)) {
               /* Search for firstcourse */
               foreach ($courses as $coursehash => $course) {
                   if ($othercourseid == block_course_fisher_format_fields($firstcoursematch, $course)) {
                       /* Found firstcourse match */
                       $firstcoursedata = new stdClass();
                       $firstcoursedata->idnumber = '';
                       $firstcoursedata->shortname = block_course_fisher_format_fields($CFG->block_course_fisher_course_shortname, $course);
                       if (isset($CFG->block_course_fisher_course_code) && !empty($CFG->block_course_fisher_course_code)) {
                           $firstcoursedata->idnumber = block_course_fisher_format_fields($CFG->block_course_fisher_course_code, $course);
                       }
                       $firstcoursedata->code = !empty($firstcoursedata->idnumber)?$firstcoursedata->idnumber:$firstcoursedata->shortname;
                       $categories = array_filter(explode("\n", block_course_fisher_format_fields($CFG->block_course_fisher_fieldlevel, $course)));
                       $categoriesdescriptions = block_course_fisher_get_fields_description($categories);
                       $firstcoursedata->path = implode(' / ', $categoriesdescriptions);
                       $firstcoursedata->fullname = block_course_fisher_format_fields($CFG->block_course_fisher_course_fullname, $course);
                       $firstcoursedata->hash = $coursehash;
                       $firstcoursedata->exists = false;
                       if (!empty($firstcoursedata->idnumber)) {
                           $oldcourse = $DB->get_record('course', array('idnumber' => $firstcoursedata->idnumber));
                       } else {
                           $oldcourse = $DB->get_record('course', array('shortname' => $firstcoursedata->shortname));
                       }
                       if ($oldcourse) {
                           $firstcoursedata->exists = true;
                           $firstcoursedata->id = $oldcourse->id;
                       }

                       $groupcourses[$coursehash] = $firstcoursedata;
                       $firstcourseid = block_course_fisher_format_fields($firstcoursematch, $course);
                   }
               }
           } else {
               $groupcourses[$selectedcoursehash] = $coursedata;
           }
           if ((count($groupcourses) == 1) && !empty($firstcourseid)) {
               /* Search for othercourses */
               foreach ($courses as $coursehash => $course) {
                   if ($firstcourseid == block_course_fisher_format_fields($othercoursesmatch, $course)) {
                       /* Found firstcourse match */
                       $othercoursedata = new stdClass();
                       $othercoursedata->idnumber = '';
                       $othercoursedata->shortname = block_course_fisher_format_fields($CFG->block_course_fisher_course_shortname, $course);
                       if (isset($CFG->block_course_fisher_course_code) && !empty($CFG->block_course_fisher_course_code)) {
                           $othercoursedata->idnumber = block_course_fisher_format_fields($CFG->block_course_fisher_course_code, $course);
                       }
                       $othercoursedata->code = !empty($othercoursedata->idnumber)?$othercoursedata->idnumber:$othercoursedata->shortname;
                       $categories = array_filter(explode("\n", block_course_fisher_format_fields($CFG->block_course_fisher_fieldlevel, $course)));
                       $categoriesdescriptions = block_course_fisher_get_fields_description($categories);
                       $othercoursedata->path = implode(' / ', $categoriesdescriptions);
                       $othercoursedata->fullname = block_course_fisher_format_fields($CFG->block_course_fisher_course_fullname, $course);
                       $othercoursedata->hash = $coursehash;
                       $othercoursedata->exists = false;
                       if (!empty($othercoursedata->idnumber)) {
                           $oldcourse = $DB->get_record('course', array('idnumber' => $othercoursedata->idnumber));
                       } else {
                           $oldcourse = $DB->get_record('course', array('shortname' => $othercoursedata->shortname));
                       }
                       if ($oldcourse) {
                           $othercoursedata->exists = true;
                           $othercoursedata->id = $oldcourse->id;
                       }

                       $groupcourses[$coursehash] = $othercoursedata;
                   }
               }
           } else {
               $groupcourses[$selectedcoursehash] = $coursedata;
           }
       } else {
           $groupcourses[$selectedcoursehash] = $coursedata;
       }
       return $groupcourses;
   }
