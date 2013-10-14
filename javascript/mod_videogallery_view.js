/**
 * @author Marcos Soledade
 */

$(document).ready(function() {
	
	var video1 = $('#view_jwcontainer_common').attr('video');
	var video2 = $('#view_jwcontainer_accessibility').attr('video');
	var subtitle1 = $('#view_jwcontainer_common').attr('subtitle');
	var subtitle2 = $('#view_jwcontainer_accessibility').attr('subtitle');
		
	if(video1 != null) {
			jwplayer("view_jwcontainer_common").setup({
				autostart: false,
				file: video1,
				flashplayer: "../videogallery/player/player.swf",
				volume: 100,
				width: 550,
				height: 380,
			'plugins': {
		   		'captions-2': {
					'back': 'false',
					'file': subtitle1
				}
			}
		});
	}
	if(video2 != null) {
		jwplayer("view_jwcontainer_accessibility").setup({
			autostart: false,
			file: video2,
			flashplayer: "../videogallery/player/player.swf",
			volume: 100,
			width: 550,
			height: 380,
		'plugins': {
		   		'captions-2': {
					'back': 'false',
					'file': subtitle2
				}
			}
		});
	}
	
	var video_id = $('#alldata').val();
		
	$.ajax({
		type: "POST",
		url: "../mod/videogallery/note.php",
		data: {request: 2, conteudo: video_id},
		success: function(msg) {
			$(".video_notes_view_textarea").html(msg);
		},
		error: function(msg) {
			$(".video_notes_view_textarea").html("");
		}
	});
	
	$(".view_notes_save").click(function() {
		
		var note = $(".video_notes_view_textarea").val();
					
		$.ajax({
			type: "POST",
			url: "../mod/videogallery/note.php",
			data: {request: 1, conteudo: video_id, note: note},
			success: function(msg) {
				$(".ajax_notification").css("background-color", "#0d610d");
				$(".ajax_notification").css("color", "#fff");
				$(".ajax_notification").html(msg).hide();
				$(".ajax_notification").fadeIn(2000).fadeOut(2000);
			},
			error: function(msg) {
				$(".ajax_notification").css("background-color", "#e7673b");
				$(".ajax_notification").css("color", "#fff");
				$(".ajax_notification").html(msg).hide();
				$(".ajax_notification").fadeIn(2000).fadeOut(2000);
			}
		});
	});
});
