jQuery(function($){

/*-----------------------------------------------------------------------------------*/
/*	Uploader
/*-----------------------------------------------------------------------------------*/
	$( '.wolf-metabox-upload-button' ).click(function(e){
		e.preventDefault();
		var $el = $(this).parent();
		var uploader = wp.media({
			title : 'Choose an image',
			//button : {
			//	text : ''
			//},
			multiple : false
		})
		.on( 'select', function(){
			var selection = uploader.state().get('selection');
			var attachment = selection.first().toJSON();
			$('input', $el).val(attachment.url);
			$('img', $el).attr('src', attachment.url).show();
		})
		.open();
	});

/*-----------------------------------------------------------------------------------*/
/*	Reset Image preview
/*-----------------------------------------------------------------------------------*/

	$('.wolf-reset-metabox-bg').click(function(){
		
		$(this).parent().find("input").val('');
		$(this).parent().find(".wolf-metabox-img-preview").hide();
		return false;

	});

/*-----------------------------------------------------------------------------------*/
/*	Tipsy
/*-----------------------------------------------------------------------------------*/      
	
	$('.hastip').tipsy({fade: true, gravity: 's'});

/*-------------------------------*/
}); // end document ready
