/**
 * This class is not coded intending to be re-used in any case. That is particular for the auto-completion of images.
 * @param object trigger	The input type text box on which we'll bind the event onkeyup for auto-completion
 * @param object target		The container that will receive the HTML result (the list of images)
 */
function AutoComplete(trigger, target){
	AutoComplete.ajaxContainerTpl = '<div class="autocompleteresult" style="position:absolute;z-index:1000;"></div>';
	
	/* This layer is used to enable the user to close the auto complete container when he clicks outside */
	this.layer = $('<div class="autocompletelayer" style="position:absolute;display:none;z-index:999;top:0;left:0;"></div>');
	this.layer.css('background', 'rgba(150, 150, 150, .05)');
	$('body').append(this.layer);
	
	/* Declare properties */
	this.inputObj = $(trigger);
	this.inputObj.attr('autocomplete', 'off');
	this.inputId = this.inputObj.attr('id');
	this.hiddenObj = $('#hidden_'+this.inputId);
	this.autoId = 'auto_' + this.inputId;
	this.resultContainer = $(target);
	this.json = null;
	
	/* Auto instance to be called in the functions */
	var This = this;
	
	/* Declare functions */
	//Ajax: get Json data and build HTML list
	AutoComplete.prototype.ajax = function(obj){
		$.get(WEB_ROOT + '/ajax/json?model=cms&class=Files&method=get&search=' + obj.value.toString().trim(), function(json) {
			This.json = json;
			
			var ul = '<ul class="ul_autocomplete">';
			var list = '';
			var classIter = 'iter';
			for (var i in json){
				classIter = (classIter=='iter') ? '' : 'iter';
				list += '<li class="li_autocomplete ' + classIter + '">' +
					'<a class="a_autocomplete">' +
						'<input type="hidden" value="' + json[i].fid + '" />' +
						'<img class="img_autocomplete" src="' + WEB_ROOT + json[i].path + '" />' +
						'<span class="span_autocomplete">' + json[i].filename + '</span>' +
					'</a></li>';
			}
			if (!list) return false;
			ul += list + '</ul>';
			
			var newResult = $(AutoComplete.ajaxContainerTpl);
			newResult.html(ul);
			$(This.resultContainer).html(newResult);
			
			This.show();
			This.bindA();
		});
	}
	//Bind procedures on <a> click (element chosen)
	AutoComplete.prototype.bindA = function(){
		$('.a_autocomplete').click(function(e){
			e.preventDefault();
			This.inputObj.val($(this).find('span').text());
			This.hiddenObj.val($(this).find('input').val());
			This.hide();
		});
	}
	//Display the containers (the layer too)
	AutoComplete.prototype.show = function(){
		This.resultContainer.show();
		This.layer.height($('body').height()).width($('body').width()).show();
	}
	//Hide the containers (the layer too)
	AutoComplete.prototype.hide = function(){
		$('.autocompleteresult').hide();
		This.layer.hide();
		This.checkFid();
	}
	//Check if the value in the text box corresponds to a registered value (a file name)
	AutoComplete.prototype.checkFid = function(){
		var value = This.inputObj.val().toString().trim();
		var isIn = false;
		if (value)
		for (var i in This.json){
			if (This.json[i].filename==value)
				isIn = true;
		}
		if (!isIn)
			this.hiddenObj.val('');
	}
	AutoComplete.prototype.clear = function(){
		this.hiddenObj.val('');
		this.hide();
		return false;
	}
	//Bind to events
	this.layer.click(This.hide);
	this.inputObj.keyup(function(e){
		e.preventDefault();
		if (typeof this.value == 'undefined' || this.value.toString().trim()==''){
			return This.clear();
		}
		if (e.keyCode!=13 && e.which!=13){
			This.ajax(this);
		}
		else
			return This.hide();
		This.checkFid();
		return false;
	});
	this.inputObj.focus(function(){
		if (typeof this.value == 'undefined' || this.value.toString().trim()==''){
			return This.clear();
		}
		This.ajax(this);
		This.checkFid();
	});
}

$(function(){
	//Init Auto Complete
	if ($('.autocomplete').length)
	$('.autocomplete').each(function(){
		new AutoComplete(this, $('.' + this.id));
	});
});