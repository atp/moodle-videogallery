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
 * Library of interface functions and constants for module videogallery
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the videogallery specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod
 * @subpackage videogallery
 * @copyright  2012 Quantica
 */

defined('MOODLE_INTERNAL') || die();

global $PAGE;

require_once('vendor/mobile_detect.php');

$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/videogallery/javascript/jquery.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/videogallery/javascript/fancybox.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/videogallery/javascript/jwplayer.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/videogallery/javascript/mod_videogallery_view.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/videogallery/javascript/mod_videogallery_functions.js'));


/** example constant */
//define('NEWMODULE_ULTIMATE_ANSWER', 42);

////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function videogallery_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:         return true;
		case FEATURE_MOD_ARCHETYPE:     return MOD_ARCHETYPE_RESOURCE;
		case FEATURE_NO_VIEW_LINK:      return true;
        default:                        return null;
    }
}

/**
 * Saves a new instance of the videogallery into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $videogallery An object from the form in mod_form.php
 * @param mod_videogallery_mod_form $mform
 * @return int The id of the newly inserted videogallery record
 */
function videogallery_add_instance(stdClass $videogallery, mod_videogallery_mod_form $mform = null) {
    global $DB;
    
	//print_r($videogallery);
    //die();

    $videogallery -> timecreated = time();    
    $videogallery -> id = $DB -> insert_record('videogallery', $videogallery);
    
    $total_videos = $videogallery->videogallery_repeats; //we need to know how many videos we will insert
    
    //So we've got a video array, and we need to update the videos and the links tables
    for($i = 0; $i < $total_videos; $i++) {
        
        $video -> title = $videogallery -> title[$i];
        $video -> origin = $videogallery -> origin[$i];
        $video -> videogallery_id = $videogallery -> id;
        $video -> video_intro = $videogallery -> video_intro[$i]['text'];
        $video -> thumb = $videogallery -> thumb[$i];
        $video -> subtitle = $videogallery -> subtitle[$i];
        $video -> weekid = $videogallery -> weekid[$i];
        $video -> timecreated = time();
        $video -> id = $DB -> insert_record('videogallery_video', $video); //and here is the $videogallery[$i] video ID
        
        if($videogallery -> watch[$i]) {
            $link -> videogallery_video_id = $video -> id;
            $link -> name = 'watch';
            $link -> url = $videogallery -> watch[$i];
            $link -> timecreated = time();
            $DB -> insert_record('videogallery_link', $link);
        }
    
        if($videogallery -> watch_libras[$i]) {
            $link -> video_id = $video -> id;
            $link -> name = 'watch_libras';
            $link -> url = $videogallery -> watch_libras[$i];
            $link -> timecreated = time();
            $DB -> insert_record('videogallery_link', $link);
        }
        
        if($videogallery -> download[$i]) {
            $link -> video_id = $video -> id;
            $link -> name = 'download';
            $link -> url = $videogallery -> download[$i];
            $link -> timecreated = time();
            $DB -> insert_record('videogallery_link', $link);
        }
        
        if($videogallery -> download_libras[$i]) {
            $link -> video_id = $video -> id;
            $link -> name = 'download_libras';
            $link -> url = $videogallery -> download_libras[$i];
            $link -> timecreated = time();
            $DB -> insert_record('videogallery_link', $link);
        }
    
    }
    return $videogallery->id;
}

/**
 * Updates an instance of the videogallery in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $videogallery An object from the form in mod_form.php
 * @param mod_videogallery_mod_form $mform
 * @return boolean Success/Fail
 */
function videogallery_update_instance(stdClass $videogallery, mod_videogallery_mod_form $mform = null) {
    global $DB;

    $videogallery->timemodified = time();
    $videogallery->id = $videogallery->instance;

    $total_videos = $videogallery->videogallery_repeats; //we need to know how many videos we will insert
    
    //So we've got a video array, and we need to update the videos and the links tables
    for($i = 0; $i < $total_videos; $i++) {
        
        $video = new stdClass;
        
        if($videogallery -> title[$i] && $video = $DB->get_record('videogallery_video', array('id' => $videogallery -> video_id[$i], 'videogallery_id' => $videogallery -> id))) {
            $video -> title = $videogallery -> title[$i];
            $video -> origin = $videogallery -> origin[$i];
            $video -> videogallery_id = $videogallery -> id;
            $video -> video_intro = $videogallery -> video_intro[$i]['text'];
            $video -> video_introformat = $videogallery -> video_intro[$i]['format'];
            $video -> thumb = $videogallery -> thumb[$i];
            $video -> subtitle = $videogallery -> subtitle[$i];
            $video -> weekid = $videogallery -> weekid[$i];
            $video -> timemodified = time();
            $video -> id = $DB -> update_record('videogallery_video', $video);
        } else if($videogallery -> title[$i]){
            $video -> title = $videogallery -> title[$i];
            $video -> videogallery_id = $videogallery -> id;
            $video -> intro = $videogallery -> video_description[$i]['text'];
            $video -> introformat = $videogallery -> video_description[$i]['format'];
            $video -> thumb = $videogallery -> thumb[$i];
            $video -> subtitle = $videogallery -> subtitle[$i];
            $video -> weekid = $videogallery -> weekid[$i];
            $video -> timecreated = time();
            $video -> id = $DB -> insert_record('videogallery_video', $video);
        } else if(empty($videogallery -> title[$i])) {
            $DB -> delete_records('videogallery_video', array('videogallery_id' => $videogallery->id, 'id' => $videogallery -> video_id[$i]));
            $DB -> delete_records('videogallery_link', array('videogallery_video_id' => $videogallery -> video_id[$i]));
        }
        
        if($videogallery -> watch[$i] && $link = $DB->get_record('videogallery_link', array('videogallery_video_id'=>$videogallery -> video_id[$i], 'name'=>'watch'))) {
            $link -> url = $videogallery -> watch[$i];
            $link -> timemodified = time();
            $DB -> update_record('videogallery_link', $link);
        } else if($videogallery -> watch[$i]) {
            $link -> videogallery_video_id = $videogallery -> video_id[$i];
            $link -> name = 'watch';
            $link -> url = $videogallery -> watch[$i];
            $link -> timecreated = time();
            $DB -> insert_record('videogallery_link', $link);
        } else if(empty($videogallery -> watch[$i])) {
            $DB -> delete_records('videogallery_link', array('videogallery_video_id' => $videogallery -> video_id[$i], 'name'=>'watch'));
        }
        
        if($videogallery -> download[$i] && $link = $DB->get_record('videogallery_link', array('videogallery_video_id' => $videogallery -> video_id[$i], 'name'=>'download'))) {
            $link -> url = $videogallery -> download[$i];
            $link -> timemodified = time();
            $DB -> update_record('videogallery_link', $link);
        } else if($videogallery -> download[$i]) {
            $link -> videogallery_video_id = $videogallery -> video_id[$i];
            $link -> name = 'download';
            $link -> url = $videogallery -> download[$i];
            $link -> timecreated = time();
            $DB -> insert_record('videogallery_link', $link);
        } else if(empty($videogallery -> download[$i])) {
            $DB -> delete_records('videogallery_link', array('videogallery_video_id' => $videogallery -> video_id[$i], 'name'=>'download'));
        }
        
        if($videogallery -> watch_libras[$i] && $link = $DB->get_record('videogallery_link', array('videogallery_video_id' => $videogallery -> video_id[$i], 'name'=>'watch_libras'))) {
            $link -> url = $videogallery -> watch_libras[$i];
            $link -> timemodified = time();
            $DB -> update_record('videogallery_link', $link);
        } else if($videogallery -> watch_libras[$i]) {
            $link -> videogallery_video_id = $videogallery -> video_id[$i];
            $link -> name = 'watch_libras';
            $link -> url = $videogallery -> watch_libras[$i];
            $link -> timecreated = time();
            $DB -> insert_record('videogallery_link', $link);
        } else if(empty($videogallery -> watch_libras[$i])) {
            $DB -> delete_records('videogallery_link', array('videogallery_video_id' => $videogallery -> video_id[$i], 'name'=>'watch_libras'));
        }
        
        if($videogallery -> download_libras[$i] && $link = $DB->get_record('videogallery_link', array('videogallery_video_id' => $videogallery -> video_id[$i], 'name'=>'download_libras'))) {
            $link -> url = $videogallery -> download_libras[$i];
            $link -> timemodified = time();
            $DB -> update_record('videogallery_link', $link);
        } else if($videogallery -> download_libras[$i]) {
            $link -> videogallery_video_id = $videogallery -> video_id[$i];
            $link -> name = 'download_libras';
            $link -> url = $videogallery -> download_libras[$i];
            $link -> timecreated = time();
            $DB -> insert_record('videogallery_link', $link);
        } else if(empty($videogallery -> download_libras[$i])) {
            $DB -> delete_records('videogallery_link', array('videogallery_video_id' => $videogallery -> video_id[$i], 'name'=>'download_libras'));
        }
    
    }

    return $DB->update_record('videogallery', $videogallery);
}

/**
 * Removes an instance of the videogallery from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function videogallery_delete_instance($id) {
    global $DB;

    if (! $videogallery = $DB->get_record('videogallery', array('id' => $id))) {
        return false;
    }
    $video = $DB->get_record('videogallery_video', array('videogallery_id' => $videogallery->id));
    
    $DB->delete_records('videogallery_link', array('videogallery_video_id' => $video->id));
    $DB->delete_records('videogallery_video', array('videogallery_id' => $videogallery->id));
    $DB->delete_records('videogallery', array('id' => $videogallery->id));

    return true;
}

function videogallery_cm_info_dynamic($coursemodule) {
    global $CFG, $DB, $COURSE;

    if($videogallery = $DB->get_record('videogallery', array('id'=>$coursemodule->instance), '*')) {
        $videos = $DB->get_records('videogallery_video', array('videogallery_id'=>$videogallery->id));
        $links = $DB->get_records('videogallery_link', array('videogallery_video_id'=>$videogallery->id));
        $output = '';
        
        $mobile = new Mobile_Detect();
        if($mobile->isMobile() && !$mobile->isTablet()) { //smartphone

            $output .= html_writer::start_tag('div', array('class' => 'separador_semanal_smartphone'));
                $output .= html_writer::tag('div', 'Vídeos', array('class' => 'separador_semanal_type'));
            $output .= html_writer::end_tag('div');

            foreach ($videos as $video) {
                unset($watch_link);
                unset($download_link);
                unset($watch_libras_link);
                unset($download_libras_link);
                $links = $DB->get_records('videogallery_link', array('videogallery_video_id'=>$video->id));
                foreach($links as $link) {
                    if($link->name == 'watch') $watch_link = $link->url;
                    if($link->name == 'download') $download_link = $link->url;
                    if($link->name == 'watch_libras') $watch_libras_link = $link->url;
                    if($link->name == 'download_libras') $download_libras_link = $link->url;
                }

                $output .= '<div class="videos">';
                    if($video->origin == 1) { //video externo
                        $output .= '<a href="'.$watch_link.'" target="_blank">';
                    } else {
                        $output .= '<a href="'.$watch_link.'">';
                    }
                        $output .= '<div class="container">';
                            $output .= '<img src="'.$video->thumb.'" />';
                            $output .= '<div class="legenda">'.$video->title.'</div>';
                        $output .= '</div>';
                    $output .= '</a>';
                 $output .= '</div>';
                 if($watch_libras_link) {
                    $output .= '<div class="ver_libras">';
                        $output .= '<a href="'.$watch_libras_link.'">Ver vídeo acima em Libras</a>';
                    $output .= '</div>';
                 }
            }

            $output .= html_writer::start_tag('div', array('class' => 'separador_semanal_smartphone'));
                $output .= html_writer::tag('div', 'Textos', array('class' => 'separador_semanal_type'));
            $output .= html_writer::end_tag('div');

            $output = '<div class="videogallery_smartphone">'.$output.'</div>';

        } else if($mobile->isTablet()) { //tablet
            $output .= html_writer::tag('a', '', array('id' => 'videos'));
            $output .= html_writer::start_tag('div', array('class' => 'separador_semanal'));
                $output .= html_writer::tag('div', 'Vídeos', array('class' => 'separador_semanal_type'));
                $output .= html_writer::tag('div', '', array('class' => 'separador_barra'));
                $output .= html_writer::link('#textos', 'Ir para textos');
            $output .= html_writer::end_tag('div');

            $output .= html_writer::start_tag('div', array('class'=>'current_video_column'));    
                    
                $output .= html_writer::start_tag('a', array('class'=>'current_video lightbox_trigger')); 
                    $output .= html_writer::empty_tag('img', array('src'=>$videogallery->thumb_inicial, 'width'=>520, 'height'=>322));       
                    $output .= html_writer::start_tag('div', array('class'=>'current_video_title'));
                        $output .= html_writer::tag('div', '', array('class'=>'current_video_number'));
                        $output .= html_writer::tag('div', $videogallery->name, array('class'=>'current_video_completetitle'));
                    $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('a');

                $output .= html_writer::tag('div', $videogallery->intro, array('class'=>'current_video_description'));

            $output .= html_writer::end_tag('div');

            $output .= html_writer::start_tag('div', array('class'=>'next_video_column'));
                foreach ($videos as $video) {
                    unset($watch_link);
                    unset($download_link);
                    unset($watch_libras_link);
                    unset($download_libras_link);
                    $links = $DB->get_records('videogallery_link', array('videogallery_video_id'=>$video->id));
                    foreach($links as $link) {
                        if($link->name == 'watch') $watch_link = $link->url;
                        if($link->name == 'download') $download_link = $link->url;
                        if($link->name == 'watch_libras') $watch_libras_link = $link->url;
                        if($link->name == 'download_libras') $download_libras_link = $link->url;
                    }
                    
                    $output .= html_writer::start_tag('a', array('class'=>'next_video_thumb next_video_thumb_tablet', 'href'=>' ', 'video_url'=>$watch_link, 'thumb'=>$video->thumb, 'name'=>$video->title, 'video_id'=>$video->id, 'intro'=>$video->video_intro, 'origin'=>$video->origin, 'video_weekid'=>$video->weekid));
                        $output .= html_writer::empty_tag('img', array('src'=>$video->thumb,'width'=>190, 'height'=>125));
                        $only_number = explode(':', $video->title);
                        $output .= html_writer::tag('div', $only_number[0], array('class'=>'next_video_thumb_title'));
                    $output .= html_writer::end_tag('a');


                    $output .= html_writer::start_tag('div', array('id'=>'video'.$video->id, 'class'=>'lightbox_wrapper lightbox_tablet'));
                        $output .= html_writer::tag('div', $video->title, array('class'=>'titulo'));
                        //The video
                        $output .= html_writer::start_tag('div', array('class'=>'video_container'));
                            $output .= html_writer::tag('div', '', array('id'=>'player'.$video->id));
                        $output .= html_writer::end_tag('div');

                        //Anotações e controle
                        $output .= html_writer::start_tag('div', array('class'=>'notes_container'));
                            $output .= html_writer::tag('div', '<b>Anotações</b> (clique em "salvar" para gravar suas anotações)', array('class'=>'annotation_title'));
                            $output .= html_writer::tag('div', '', array('class'=>'ajax_notification'));
                            $output .= html_writer::tag('textarea', '', array('title'=>'Anotações', 'class'=>'notetextarea annotation_input'.$video->id));
                            $output .= html_writer::start_tag('div', array('class'=>'inner_buttons_wrapper'));
                                $output .= html_writer::empty_tag('input', array('type'=>'button', 'value'=>'Salvar', 'class'=>'inner_notes_save', 'video_id'=>$video->id, 'video_weekid'=>$video->weekid));
                                $output .= html_writer::start_tag('div', array('class'=>'controles'));
                                    if($watch_link) $output .= html_writer::link('#', 'Ver vídeo sem Libras', array('class'=>'watch_button', 'video_id'=>$video->id, 'video_url'=>$watch_link));
                                    if($watch_libras_link) $output .= html_writer::link('#', ' | Ver vídeo com Libras', array('class'=>'signals_button', 'video_id'=>$video->id, 'video_url'=>$watch_libras_link));
                                
                                    //if($watch_link) $output .= html_writer::link('#', '', array('class'=>'video_buttons_child watch_button', 'video_id'=>$video->id, 'video_url'=>$watch_link));
                                    //if($download_link) $output .= html_writer::link($download_link, '', array('class'=>'video_buttons_child download_button'));
                                    //if($watch_libras_link) $output .= html_writer::link('#', '', array('class'=>'video_buttons_child signals_button', 'video_id'=>$video->id, 'video_url'=>$watch_libras_link));
                                    //if($download_libras_link) $output .= html_writer::link($download_libras_link, '', array('class'=>'video_buttons_child signals_download_button'));

                                $output .= html_writer::end_tag('div');
                            $output .= html_writer::end_tag('div');
                        $output .= html_writer::end_tag('div');
                    $output .= html_writer::end_tag('div');
                }
            $output .= html_writer::end_tag('div');
            
            $output .= html_writer::tag('a', '', array('id' => 'textos'));
            $output .= html_writer::start_tag('div', array('class' => 'separador_semanal'));
                $output .= html_writer::tag('div', 'Textos', array('class' => 'separador_semanal_type'));
                $output .= html_writer::tag('div', '', array('class' => 'separador_barra'));
                $output .= html_writer::link('#videos', 'Ir para vídeos');
            $output .= html_writer::end_tag('div');
            
            $output = '<div class="videogallery_tablet">'.$output.'</div>';

        } else { //desktop
            $output .= html_writer::tag('a', '', array('id' => 'videos'));
            $output .= html_writer::start_tag('div', array('class' => 'separador_semanal'));
                $output .= html_writer::tag('div', 'Vídeos', array('class' => 'separador_semanal_type'));
                $output .= html_writer::tag('div', '', array('class' => 'separador_barra'));
                $output .= html_writer::link('#textos', 'Ir para textos');
            $output .= html_writer::end_tag('div');
                
            $output .= html_writer::start_tag('div', array('class'=>'current_video_column'));                
                $output .= html_writer::start_tag('a', array('class'=>'current_video lightbox_trigger'));
                    $output .= html_writer::empty_tag('img', array('src'=>$videogallery->thumb_inicial, 'width'=>580, 'height'=>380));       
                    $output .= html_writer::start_tag('div', array('class'=>'current_video_title'));
                        $output .= html_writer::tag('div', '', array('class'=>'current_video_number'));
                        $output .= html_writer::tag('div', $videogallery->name, array('class'=>'current_video_completetitle'));
                    $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('a');

                $output .= html_writer::tag('div', $videogallery->intro, array('class'=>'current_video_description'));

            $output .= html_writer::end_tag('div');

            $output .= html_writer::start_tag('div', array('class'=>'next_video_column'));
                foreach ($videos as $video) {
                    unset($watch_link);
                    unset($download_link);
                    unset($watch_libras_link);
                    unset($download_libras_link);
                    $links = $DB->get_records('videogallery_link', array('videogallery_video_id'=>$video->id));
                    foreach($links as $link) {
                        if($link->name == 'watch') $watch_link = $link->url;
                        if($link->name == 'download') $download_link = $link->url;
                        if($link->name == 'watch_libras') $watch_libras_link = $link->url;
                        if($link->name == 'download_libras') $download_libras_link = $link->url;
                    }
                    
                    $output .= html_writer::start_tag('a', array('class'=>'next_video_thumb next_video_thumb_desktop', 'href'=>' ', 'video_url'=>$watch_link, 'thumb'=>$video->thumb, 'name'=>$video->title, 'video_id'=>$video->id, 'intro'=>$video->video_intro, 'origin'=>$video->origin, 'subtitle'=>$video->subtitle, 'video_weekid'=>$video->weekid));
                        $output .= html_writer::empty_tag('img', array('src'=>$video->thumb,'width'=>190, 'height'=>125));
                        $only_number = explode(':', $video->title);
                        $output .= html_writer::tag('div', $only_number[0], array('class'=>'next_video_thumb_title'));
                    $output .= html_writer::end_tag('a');

                    //The lightbox referred from link, must have an ID that concatenates a string (video) + $video->id
                    $output .= html_writer::start_tag('div', array('id'=>'video'.$video->id, 'class'=>'lightbox_wrapper'));
                        //The video
                        $output .= html_writer::start_tag('div', array('class'=>'video_container'));
                            $output .= html_writer::tag('div', '', array('id'=>'player'.$video->id));
                        $output .= html_writer::end_tag('div');
                        //Video title and description
                        $output .= html_writer::start_tag('div', array('class'=>'video_info'));
                            $output .= html_writer::tag('div', $video->title, array('class'=>'video_inner_title'));
                            $output .= html_writer::tag('div', $video->video_intro, array('class'=>'video_inner_description'));
                        $output .= html_writer::end_tag('div');
                        //Annotations
                        $output .= html_writer::start_tag('div', array('class'=>'notes_container'));
                            $output .= html_writer::tag('div', '<b>Anotações</b> (clique em "salvar" para gravar suas anotações)', array('class'=>'annotation_title'));
                            $output .= html_writer::tag('div', '', array('class'=>'ajax_notification'));
                            $output .= html_writer::tag('textarea', '', array('title'=>'Anotações', 'class'=>'notetextarea annotation_input'.$video->id));
                            $output .= html_writer::start_tag('div', array('class'=>'inner_buttons_wrapper'));
                                $output .= html_writer::empty_tag('input', array('type'=>'button', 'value'=>'Salvar', 'class'=>'inner_notes_save', 'video_id'=>$video->id, 'video_weekid'=>$video->weekid));
                                $currentvideo = $DB->get_record('video', array('weekid'=>$video->weekid), '*');
                                $output .= html_writer::link($CFG->wwwroot.'/mod/video/index.php', get_string('allnotes', 'video'), array('class'=>'inner_notes_allnotes'));
                                $output .= html_writer::link($CFG->wwwroot.'/mod/video/view.php?id='.$currentvideo->coursemodule, get_string('question', 'video'), array('class'=>'inner_notes_window'));
                            $output .= html_writer::end_tag('div');
                        $output .= html_writer::end_tag('div');
                        //Buttons
                        $output .= html_writer::start_tag('div', array('class'=>'video_buttons_container'));
                            if($watch_link) $output .= html_writer::link('#', '', array('class'=>'video_buttons_child watch_button', 'video_id'=>$video->id, 'video_url'=>$watch_link));
                            if($download_link) $output .= html_writer::link($download_link, '', array('class'=>'video_buttons_child download_button'));
                            if($watch_libras_link) $output .= html_writer::link('#', '', array('class'=>'video_buttons_child signals_button', 'video_id'=>$video->id, 'video_url'=>$watch_libras_link));
                            if($download_libras_link) $output .= html_writer::link($download_libras_link, '', array('class'=>'video_buttons_child signals_download_button'));
                        $output .= html_writer::end_tag('div');
                    $output .= html_writer::end_tag('div');
                }
            $output .= html_writer::end_tag('div');
            
            $output .= html_writer::tag('div', '', array('class' => 'videogallery_bottom_clearfix'));
            
            $output .= html_writer::tag('a', '', array('id' => 'textos'));
            $output .= html_writer::start_tag('div', array('class' => 'separador_semanal'));
                $output .= html_writer::tag('div', 'Textos', array('class' => 'separador_semanal_type'));
                $output .= html_writer::tag('div', '', array('class' => 'separador_barra'));
                $output .= html_writer::link('#videos', 'Ir para vídeos');
            $output .= html_writer::end_tag('div');
            
            $output .= html_writer::tag('div', '', array('class' => 'videogallery_bottom_clearfix'));
        }
        
        $coursemodule->set_content($output);

    } else {
        return null;
    }
}



/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function videogallery_user_outline($course, $user, $mod, $videogallery) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $videogallery the module instance record
 * @return void, is supposed to echp directly
 */
function videogallery_user_complete($course, $user, $mod, $videogallery) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in videogallery activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function videogallery_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link videogallery_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function videogallery_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see videogallery_get_recent_mod_activity()}

 * @return void
 */
function videogallery_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function videogallery_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function videogallery_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of videogallery?
 *
 * This function returns if a scale is being used by one videogallery
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $videogalleryid ID of an instance of this module
 * @return bool true if the scale is used by the given videogallery instance
 */
function videogallery_scale_used($videogalleryid, $scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists('videogallery', array('id' => $videogalleryid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of videogallery.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any videogallery instance
 */
function videogallery_scale_used_anywhere($scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists('videogallery', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the give videogallery instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $videogallery instance object with extra cmidnumber and modname property
 * @return void
 */
function videogallery_grade_item_update(stdClass $videogallery) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $item = array();
    $item['itemname'] = clean_param($videogallery->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax']  = $videogallery->grade;
    $item['grademin']  = 0;

    grade_update('mod/videogallery', $videogallery->course, 'mod', 'videogallery', $videogallery->id, 0, null, $item);
}

/**
 * Update videogallery grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $videogallery instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function videogallery_update_grades(stdClass $videogallery, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $grades = array(); // populate array of grade objects indexed by userid

    grade_update('mod/videogallery', $videogallery->course, 'mod', 'videogallery', $videogallery->id, 0, $grades);
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function videogallery_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for videogallery file areas
 *
 * @package mod_videogallery
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function videogallery_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the videogallery file areas
 *
 * @package mod_videogallery
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the videogallery's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function videogallery_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding videogallery nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the videogallery module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function videogallery_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the videogallery settings
 *
 * This function is called when the context for the page is a videogallery module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $videogallerynode {@link navigation_node}
 */
function videogallery_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $videogallerynode=null) {
}
