/**
 * @author Quantica
 */
$(document).ready(function() {

	
	$('.next_video_thumb_desktop').click(function(e) {
		
		e.preventDefault();
		
		var novo_video;
		
		var video_id = $(this).attr("video_id");
		var video_title = $(this).attr("name");
		var video_desc = $(this).attr("intro");
		var video_url = $(this).attr("video_url");
		var video_thumb = $(this).attr("thumb");
		var origin = $(this).attr("origin");
		var subtitle = $(this).attr("subtitle");
		
		var video_aux = video_title.split(':');

		var video_numero = video_aux[0];
		var video_titulo = video_title.split(video_numero+':').pop();

		var video_weekid = $(this).attr('video_weekid');//
		
		if(origin == 1) {
			novo_video = $('<a class="current_video" href="'+video_url+'" target="_blank" subtitle="'+subtitle+'"><img src="'+video_thumb+'" width="580" height="380"><div class="current_video_title"><div class="current_video_number">'+video_numero+'</div><div class="current_video_completetitle">'+video_titulo+'</div></div></a>');	
		} else {
			novo_video = $('<a class="current_video lightbox_trigger" subtitle="'+subtitle+'" href="#video'+video_id+'", video_id='+video_id+', video_url='+video_url+'><img src="'+video_thumb+'" width="580" height="380"><div class="current_video_title"><div class="current_video_number">'+video_numero+'</div><div class="current_video_completetitle">'+video_titulo+'</div></div></a>');	
		}
		
		var nova_desc = $('<div class="current_video_description"><span>'+video_desc+'</span></div>');
		$('a.current_video').hide().replaceWith(novo_video.fadeIn());
		$('div.current_video_description').hide().replaceWith(nova_desc.fadeIn());
		
		$(".lightbox_trigger").fancybox({
			maxWidth: 900,
			maxHeight: 600,
			closeClick: false,
			openEffect: 'elastic',
			closeEffect: 'elastic',
			enableEscapeButton: true,
			margin: 0,
			padding: 0,
			scrolling: 'no',
		}).click(function() {
			jwplayer("player"+video_id).setup({
				autostart: false,
				file: $(this).attr("video_url"),
				flashplayer: "../mod/videogallery/player/player.swf",
				volume: 100,
				width: 550,
				height: 380,
				'plugins': {
					'captions-2': {
						'back': 'false',
						'file': $(this).attr("subtitle")
					}
				}
			});
			
			$.ajax({
				type: "POST",
				//url: "../mod/videogallery/note.php",
				url: "../mod/video/note.php",
				//data: {request: 2, video_id: video_id},
				data: {request: 2, conteudo: video_weekid},
				success: function(msg) {
					$(".annotation_input"+video_id).html(msg);
				},
				error: function(msg) {
					$(".annotation_input"+video_id).html("");
				}
			});
		});	
		if(origin == 1) $('a.current_video').removeClass('lightbox_trigger');
	});

	$('.next_video_thumb_tablet').click(function(e) {
		
		e.preventDefault();
		
		var novo_video;
		
		var video_id = $(this).attr("video_id");
		var video_title = $(this).attr("name");
		var video_desc = $(this).attr("intro");
		var video_url = $(this).attr("video_url");
		var video_thumb = $(this).attr("thumb");
		var origin = $(this).attr("origin");

		var video_aux = video_title.split(':');

		var video_numero = video_aux[0];
		var video_titulo = video_aux[1];
		
		console.log(origin);
		if(origin == 1) {
			novo_video = $('<a class="current_video" href="'+video_url+'" target="_blank"><img src="'+video_thumb+'" width="580" height="380"><div class="current_video_title"><div class="current_video_number">'+video_numero+'</div><div class="current_video_completetitle">'+video_titulo+'</div></div></a>');	
		} else {
			novo_video = $('<a class="current_video lightbox_trigger" href="#video'+video_id+'", video_id='+video_id+', video_url='+video_url+'><img src="'+video_thumb+'" width="580" height="380"><div class="current_video_title"><div class="current_video_number">'+video_numero+'</div><div class="current_video_completetitle">'+video_titulo+'</div></div></a>');	
		}
		
		var nova_desc = $('<div class="current_video_description"><span>'+video_desc+'</span></div>');
		$('a.current_video').hide().replaceWith(novo_video.fadeIn());
		$('div.current_video_description').hide().replaceWith(nova_desc.fadeIn());
		
		$(".lightbox_trigger").fancybox({
			maxWidth: 600,
			maxHeight: 675,
			closeClick: false,
			openEffect: 'elastic',
			closeEffect: 'elastic',
			enableEscapeButton: true,
			margin: 0,
			padding: 0,
			scrolling: 'no',
		}).click(function() {
			jwplayer("player"+video_id).setup({
				autostart: false,
				file: $(this).attr("video_url"),
				flashplayer: "../mod/videogallery/player/player.swf",
				volume: 100,
				width: 550,
				height: 380,
				'plugins': {
					'captions-2': {
						'back': 'false',
						'file': $(this).attr("subtitle")
					}
				}
			});
			
			$.ajax({
				type: "POST",
				//url: "../mod/videogallery/note.php",
				url: "../mod/video/note.php",
				//data: {request: 2, video_id: video_id},
				data: {request: 2, conteudo: video_weekid},
				success: function(msg) {
					$(".annotation_input"+video_id).html(msg);
				},
				error: function(msg) {
					$(".annotation_input"+video_id).html("");
				}
			});
		});	
		if(origin == 1) $('a.current_video').removeClass('lightbox_trigger');
	});
	
	$(".inner_notes_save").click(function() {
		var video_id = $(this).attr("video_id");
		var note = $(".annotation_input"+video_id).val();
		var video_weekid = $(this).attr('video_weekid');//

		$.ajax({
			type: "POST",
			//url: "../mod/videogallery/note.php",
			url: "../mod/video/note.php",
			//data: {request: 1, video_id: video_id, note: note},
			data: {request: 1, conteudo: video_weekid, note: note},
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
	
	$('.watch_button').click(function() {
		var video_id = $(this).attr("video_id");
		var video_url = $(this).attr("video_url");
		jwplayer('player'+video_id).load(video_url).play();
	});
	
	$('.signals_button').click(function() {
		var video_id = $(this).attr("video_id");
		var video_url = $(this).attr("video_url");
		jwplayer('player'+video_id).load(video_url).play();
	});
	
}); // $(document).ready() end
