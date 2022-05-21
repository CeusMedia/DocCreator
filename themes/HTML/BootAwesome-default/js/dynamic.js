function appendTitle( string ) {
	var base	= "cmClasses Docs";
	var obj	= document.title;
	parent.document.title	= obj + " @ " + base;
}

function openTreeToLink(href){
	var doc = $(parent.frm_control.document);
	var tree = doc.find("body #tree_container");
	tree.find("a").each(function(){
		if($(this).attr("href") == href){
			doc.find("#tree a.selected").removeClass("selected");
			$(this).addClass("selected");
			var parent = $(this).parent();
			while(parent.not("#tree").length){
				if(parent.is("li.parent.expandable"))
					parent.children("div.hitarea").trigger("click");
				parent = parent.parent();
			}
			return false;
		}
	});
}

function applyCodeMirror(selector){
	var mirror = CodeMirror.fromTextArea( $(selector).get(0), {
		readOnly: true,
		lineNumbers: true,
		matchBrackets: true,
		indentWithTabs: true,
		indentUnit: 4,
		tabSize: 4,
		tabMode: "shift",
		enterMode: "keep",
		mode: "php"
	});
    $("body").data("mirror", mirror);
}

function initTree(){
	if($("#tree_container li").length){
		$("#tree_container").treeview({
			animated: "fast",
			collapsed: true,
			persist: "cookie",
			cookieId: "<%cookieId%>"
		});
	}else{
		$(".msgTreeEmpty").show();
	}
}

function finishStatsStyle(){
	//  --  ALL TABLES  --  //
	$("#statistics table.grid tr td").css("text-align","right");
	$("#statistics table.grid tr").each(function(){
		var cells = $("td", this);
		cells.eq(0).css("text-align","left");
	});

	//  --  TOTAL TABLE  --  //
	$("#statistics #totalTable table.grid tr").eq(1).css("font-weight","bold").css("font-size","1.1em");
}

function initIndex(){
	$("#index-hide-caller").click(function(){
		$.cookie('hideDocIndex',1)
		$("#index-list").slideUp();
		$(this).hide();
		$("#index-show-caller").show();
	});
	$("#index-show-caller").click(function(){
		$.cookie('hideDocIndex',null)
		$("#index-list").slideDown();
		$(this).hide();
		$("#index-hide-caller").show();
	});
	if($.cookie('hideDocIndex')){
		$("#index-list").hide();
		$("#index-hide-caller").hide();
		$("#index-show-caller").show();
	}
}

function sendQuery(){
	$.ajax({
		type		: "GET",
		url			: "search.php",
		async		: false,
		data		: {query: $("#search-query").val()},
		dataType	: "html",
		cache		: false,
		success		: function(html){
			$("#list").html(html);
		},
		error: function(msg){
			alert(msg);
		}
	});
}

function jumpToLine(number){
    var mirror = $("body").data("mirror");
    if(mirror){
        mirror.setCursor(Number(number)-1, 0);
        mirror.setSelection({line:Number(number)-1,ch:0},{line:Number(number),ch:0});
        mirror.focus();
    }
}

$(document).ready(function(){

	if($("body.control").length){
		if($("#tree").length)
			initTree();
		$("#control ul>li>a").click(function(){
			$("#control ul>li>a").removeClass('current');
			$(this).addClass('current');
		});
	}
	else if($("body.content").length){
		if(parent.location == self.location){
			parent.location.href="index.html?" + document.URL;
			return false;
		}
		openTreeToLink(document.location.href.split("/").pop());
		if($("body.content-artefact").length){
			applyCodeMirror("textarea#source-code");
//			if($("#index").length)
//				initIndex();
		}
		else{
			if(!$("#file_info").length)
				$("li.index-file-info").hide();
			if(!$("#class_info").length)
				$("li.index-class-info").hide();
		}

	//  appendTitle();

		if($("#statistics").length)
			finishStatsStyle();

		if(typeof Stickyfill === "object")
			Stickyfill.add(jQuery(".sticky"));
	}
});

