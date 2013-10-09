<?php

/**
 * AJAX handler for requests
 *
 * A param $request is received and tells wether to save or update a
 * note inside the videogallery module. The note can be associated with
 * any videogallery type (videos, photos, audio, games, texts, etc) and
 * works as a comment. It is (now, 2012) only for the user's control,
 * but we are planning to get it global inside a Moodle install,
 * making it public comments about a videogallery's content.
 *
 * @package    mod
 * @subpackage videogallery
 * @copyright  2012 Quantica
 */

require_once('../../config.php');

$request = optional_param('request', '', PARAM_INT);
$video_id = optional_param('video_id', '', PARAM_INT);
$note = optional_param('note', '', PARAM_TEXT);
 	
if (!empty($request) && $request == 1) {
	$anotacao = new stdClass;
	$anotacao->videogallery_video_id = $video_id;
	$anotacao->user_id = $USER->id;
	$anotacao->note = $note;
	$anotacao->timecreated = time();
	
	$existent = $DB->get_record('videogallery_note', array('videogallery_video_id'=>$video_id, 'user_id'=>$USER->id));
	$num = count($existent);
	
	if(!empty($existent) && $num > 0) {
		$existent->note = $anotacao->note;
		$existent->timemodified = time();
		if($DB->update_record('videogallery_note', $existent)) {
			echo "Salvo";
		} else {
			echo "Erro";
		}
	} else {
		if($DB->insert_record('videogallery_note', $anotacao)) {
			echo "Salvo";
		} else {
			echo "Erro";
		}
	}
} else if (!empty($request) && $request == 2) {
	$textarea_note = $DB->get_record('videogallery_note', array('videogallery_video_id'=>$video_id, 'user_id'=>$USER->id));
	if(!empty($textarea_note)) {
		echo $textarea_note->note;
	} else {
		echo "";
	}
}


