function Wysiwyg(id,formClass){
	

	if(!id || !document.getElementById(id))
		return false;
	
	var TextAreaId = id;
	var MenuId = "menu_"+id;
	var MainContainorId = "main_containor_"+id;
	var IframeId = "iframe_"+id;
	var width = $("#"+TextAreaId).width();
	var height = $("#"+TextAreaId).height();

	// Add iframe
	var IFrame = $('<iframe name="richTextField" id="richTextField" style="border:#000000 1px solid; width:'+width+'; height:'+height+';"></iframe>');
	$("#"+TextAreaId).after(IFrame);
	
	var editor = document.getElementById("richTextField");
	
	if (editor.contentDocument)
		editorDoc = editor.contentDocument;
	else
		editorDoc = editor.contentWindow.document;
	
	// Make iframe editable
	richTextField.document.designMode = 'On';
	
	// Get content of textarea if exist
	var Text = ($("#"+TextAreaId).val()) ? $("#"+TextAreaId).val() : ""; 
	
	// display off textarea
	$("#"+TextAreaId).css("display","none");
	
	// Add text to iframe
	$(IFrame).contents().find("body").html(Text);
	
	// Add menu
	var Menu = $('<div class="'+MenuId+'"></div>');
	var Buttons = {0:'bold',1:'italic',2:'underline',3:'superscript',4:'subscript',5:'strikeThrough',6:'insertOrderedList',7:'InsertUnorderedList',
			8:'justifyLeft',9:'justifyCenter',10:'justifyRight',11:'justifyFull',12:'insertImage',13:'link',14:'toggleEditor'};
	
	for (var i in Buttons){
		$(Menu).append('<button type="button" id="'+Buttons[i]+'" class="wysiwyg_button" ></button>');
	};
		
	// Position menu and add it
	$(Menu).css("margin","10px 5px");
	
	$("#"+TextAreaId).before(Menu);
	
	// Initialize menu background position
	for (var i in Buttons){
		$("#"+Buttons[i]).css("background-position","-"+(i*20)+"px 0");
	}
	$("#toggleEditor").html("<>");
	
	// function linked to menu's buttons
	
	$("#bold").click(function (){
		editorDoc.execCommand("bold",false,null);
		setTimeout( function() {$(IFrame).contents().find("body").focus();});
	});
	
	$("#underline").click(function (){
		editorDoc.execCommand("underline",false,null);
		setTimeout( function() {$(IFrame).contents().find("body").focus();});
	});
	$("#italic").click(function (){
		editorDoc.execCommand("italic",false,null);
		setTimeout( function() {$(IFrame).contents().find("body").focus();});
	});
	$("#superscript").click(function (){
		editorDoc.execCommand("superscript",false,null);
		setTimeout( function() {$(IFrame).contents().find("body").focus();});
	});
	$("#subscript").click(function (){
		editorDoc.execCommand("subscript",false,null);
		setTimeout( function() {$(IFrame).contents().find("body").focus();});
	});
	$("#strikeThrough").click(function (){
		editorDoc.execCommand("strikeThrough",false,null);
		setTimeout( function() {$(IFrame).contents().find("body").focus();});
	});
	$("#insertOrderedList").click(function (){
		editorDoc.execCommand("insertOrderedList",false,null);
		setTimeout( function() {$(IFrame).contents().find("body").focus();});
	});	
	$("#InsertUnorderedList").click(function (){
		editorDoc.execCommand("InsertUnorderedList",false,null);
		setTimeout( function() {$(IFrame).contents().find("body").focus();});
	});
	$("#justifyLeft").click(function (){
		editorDoc.execCommand("justifyLeft",false,null);
		setTimeout( function() {$(IFrame).contents().find("body").focus();});
	});
	$("#justifyCenter").click(function (){
		editorDoc.execCommand("justifyCenter",false,null);
		setTimeout( function() {$(IFrame).contents().find("body").focus();});
	});
	$("#justifyRight").click(function (){
		editorDoc.execCommand("justifyRight",false,null);
		setTimeout( function() {$(IFrame).contents().find("body").focus();});
	});
	$("#justifyFull").click(function (){
		editorDoc.execCommand("justifyFull",false,null);
		setTimeout( function() {$(IFrame).contents().find("body").focus();});
	});
	$("#insertImage").click(function (){
		editorDoc.execCommand("insertImage",false,null);
		setTimeout( function() {$(IFrame).contents().find("body").focus();});
		$("#insertImage").css("background-position","-240px 40px");
	});
	$("#toggleEditor").click(function (){
		if($("#toggleEditor").hasClass("htmlOff")){
			$(IFrame).contents().find("body").html($("#"+TextAreaId).val());
			$(IFrame).css("display","block");
			$("#"+TextAreaId).css("display","none");
			$("#toggleEditor").removeClass("htmlOff");
			$("#toggleEditor").html("<>");
		}else{
			$(IFrame).css("display","none");
			$("#"+TextAreaId).val($(IFrame).contents().find("body").html());
			$("#"+TextAreaId).css("display","block");
			$("#toggleEditor").addClass("htmlOff");
			$("#toggleEditor").html("R");
		}
	});
	$("."+formClass).submit(function(){
		Text = $(IFrame).contents().find("body").html();
		$("#"+TextAreaId).val(Text);
	});

}