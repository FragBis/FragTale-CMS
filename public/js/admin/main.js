/**
 * A common check for e-mail validity
 * @param emailAddress
 * @returns
 */
function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
}

$(function(){
	/**
	 * Toggle password box (user edit)
	 */
	$('#open_pwd_box').click(function(e){
		e.preventDefault();
		if ($('#pwd_box').hasClass('hidden')){
			$('#pwd_box').	removeClass('hidden');
			$('#password').	addClass('mandatory');
			$('#chk_pwd').	addClass('mandatory');
			$('#password').	attr('name', 'password');
		}
		else{
			$('#pwd_box').	addClass('hidden');
			$('#password').	removeClass('mandatory');
			$('#chk_pwd').	removeClass('mandatory');
			$('#password').	removeClass('missing');
			$('#chk_pwd').	removeClass('missing');
			$('#password').	removeAttr('name');
		}
	});
	/**
	 * Toggle images
	 */
	$('.pic_toggle .toggle_preview').hide();
	$('.pic_toggle').click(function(e){
		e.preventDefault();
		$(this).find('.toggle_open, .toggle_preview').toggle(200);
	});
	
	/**
	 * Form validation
	 */
	$('form').submit(function(e){
		//Check mandatory fields
		$(this).find('.mandatory').each(function(i, elt){
			if (!elt.value.trim()){
				$(elt).addClass('missing');
				$('label[for="'+ elt.id +'"]').addClass('missingtext');
			}
			else{
				$(elt).removeClass('missing');
				$('label[for="'+ elt.id +'"]').removeClass('missingtext');
			}
		});
		//Check passwords match
		if ($('#password').hasClass('mandatory') &&
				(
					$('#password').val().toString().trim() != $('#chk_pwd').val().toString().trim() ||
					(!$('#password').val() || !$('#chk_pwd').val())
				)
			){
			$('#password').	addClass('missing');
			$('#chk_pwd').	addClass('missing');
			$('label[for="password"]').	addClass('missingtext');
			$('label[for="chk_pwd"]').	addClass('missingtext');
			$('#pwd_error_msg').removeClass('hidden');
		}
		else{
			$('#password').	removeClass('missing');
			$('#chk_pwd').	removeClass('missing');			
			$('label[for="password"]').	removeClass('missingtext');
			$('label[for="chk_pwd"]').	removeClass('missingtext');
			$('#pwd_error_msg').addClass('hidden');
		}
		if ($('#email').length){
			if (!isValidEmailAddress($('#email').val())){
				$('#email').addClass('missing');
				$('label[for="email"]').addClass('missingtext');
				$('#email_error_msg').	removeClass('hidden');
			}
			else{
				$('#email').removeClass('missing');
				$('label[for="email"]').removeClass('missingtext');
				$('#email_error_msg').	addClass('hidden');
			}
		}
		if ($(this).find('.missing').length){
			$(this).find('.missing').get(0).focus();
			return false;
		}
		else
			return true;
	});
	
	/* Button add new input */
	$('.add_file_input').click(function(e){
		e.preventDefault();
		if ($('#files').length){
			var count = $('#files').find('input').length;
			var newOne = $('#files').find('input:first').clone();
			var exp = newOne.attr('name').toString().split('[');
			var name= exp[0];
			
			newOne.attr('name', name + '[' + count + ']');
			$('#files').append(newOne);
		}
	});

	/* Wysiwyg	*/
	if (typeof Wysiwyg != 'undefined'){
		Wysiwyg('body',"edit-form");
		Wysiwyg('article_body',"edit-form");
	}
});