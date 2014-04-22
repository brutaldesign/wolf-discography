jQuery(function($){

/*-----------------------------------------------------------------------------------*/
/*	Sortable
/*-----------------------------------------------------------------------------------*/

	$('.wolf-repeatable-add').click(function() {
		field = $(this).closest('td').find('.wolf-custom-repeatable li:last').clone(true);
		fieldLocation = $(this).closest('td').find('.wolf-custom-repeatable li:last');
		$('input', field).val('').attr('name', function(index, name) {
			return name.replace(/(\d+)/, function(fullMatch, n) {
				return Number(n) + 1;
			});
		})
		field.insertAfter(fieldLocation, $(this).closest('td'))
		return false;
	});
	
	$('.wolf-repeatable-remove').click(function(){
		field = $('.wolf-custom-repeatable li');
		if(field.length>1){
  			$(this).parent().remove();
  		}
		return false;
	});
		
	$('.wolf-custom-repeatable').sortable({
		opacity: 0.6,
		revert: true,
		cursor: 'move',
		handle: '.sort'
	});

});