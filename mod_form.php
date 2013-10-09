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
 * The main videogallery configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod
 * @subpackage videogallery
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_videogallery_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        
        global $DB;

        $mform = $this->_form;
        

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('videogalleryname', 'videogallery'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding the standard "intro" and "introformat" fields
        $mform->addElement('select' , 'type', get_string('videogallerytype', 'videogallery'), array('0'=>'Video', '1'=>'Text', '2'=>'Photo','3'=>'Audio'));
       
        $mform->addElement('text', 'thumb_inicial', get_string('videogallerythumb', 'videogallery'), array('size'=>'64'));

        $this->add_intro_editor();

        //-------------------------------------------------------------------------------
        // Adding the rest of videogallery settings, spreeading all them into this fieldset
        // or adding more fieldsets ('header' elements) if needed for better logic
        
//-----------------------------------------------------------------------------------------------------------------------------------------------------------------
        $repeatarray = array();
        $repeatarray[] = &MoodleQuickForm::createElement('header', '', get_string('videogalleryvideo','videogallery').' {no}');
        $repeatarray[] = &MoodleQuickForm::createElement('text', 'title', get_string('videogalleryvideotitle','videogallery'), array('size' => '64'));
        $repeatarray[] = &MoodleQuickForm::createElement('select', 'origin', get_string('videogalleryvideoorigin','videogallery'), array('0' => 'Interno', '1' => 'Externo'));
        $repeatarray[] = &MoodleQuickForm::createElement('text', 'thumb', get_string('videogalleryvideothumb','videogallery'), array('size'=>'64'));
        $repeatarray[] = &MoodleQuickForm::createElement('text', 'weekid', get_string('videogalleryweekid','videogallery'), array('size'=>'64'));
        $repeatarray[] = &MoodleQuickForm::createElement('editor', 'video_intro', get_string('videogalleryvideodescription', 'videogallery'));
//-------------------------------------------------------- LINKS ----------------------------------------------------------------------------
        $repeatarray[] = &MoodleQuickForm::createElement('text', 'watch', get_string('videogallerywatch','videogallery'), array('size'=>'64'));
        $repeatarray[] = &MoodleQuickForm::createElement('text', 'download', get_string('videogallerydownload','videogallery'), array('size'=>'64'));
        $repeatarray[] = &MoodleQuickForm::createElement('text', 'watch_libras', get_string('videogallerywatchlibras','videogallery'), array('size'=>'64'));
        $repeatarray[] = &MoodleQuickForm::createElement('text', 'download_libras', get_string('videogallerydownloadlibras','videogallery'), array('size'=>'64'));
//-------------------------------------------------------------------------------------------------------------------------------------------   
        $repeatarray[] = &MoodleQuickForm::createElement('text', 'subtitle', get_string('videogallerysubtitle','videogallery'), array('size'=>'64'));
//-------------------------------------------------------------------------------------------------------------------------------------------      
        $repeatarray[] = &MoodleQuickForm::createElement('hidden', 'video_id', 0);
            
        if ($this->_instance){
            $repeatno = $DB->count_records('videogallery_video', array('videogallery_id'=>$this->_instance));
        } else {
            $repeatno = 1;
        }

        $this->repeat_elements($repeatarray, $repeatno, null, 'videogallery_repeats', 'videogallery_add_fields', 1);
        
//-----------------------------------------------------------------------------------------------------------------------------------------------------------------

        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }
    
    function data_preprocessing(&$default_values) {
        global $DB;
        $i = 0;
        
        if (!empty($this->_instance) && ($videos = $DB->get_records('videogallery_video', array('videogallery_id'=>$this->_instance)))) {
            foreach($videos as $video) {
                $default_values['title['.$i.']'] = $video->title;
                $default_values['origin['.$i.']'] = $video->origin;
                $default_values['thumb['.$i.']'] = $video->thumb;
                $default_values['weekid['.$i.']'] = $video->weekid;
                $default_values['video_intro['.$i.']']['text'] = $video->video_intro;
                $default_values['video_id['.$i.']'] = $video->id;
                $default_values['subtitle['.$i.']'] = $video->subtitle;
                
                if ($links = $DB->get_records('videogallery_link', array('videogallery_video_id'=>$video->id))) {
                    foreach($links as $link) {
                        if($link->name == 'watch') $default_values['watch['.$i.']'] = $link->url;
                        if($link->name == 'download') $default_values['download['.$i.']'] = $link->url;
                        if($link->name == 'watch_libras') $default_values['watch_libras['.$i.']'] = $link->url;
                        if($link->name == 'download_libras') $default_values['download_libras['.$i.']'] = $link->url;
                    }
                }
                $i++;
            }
        }
    }
}
