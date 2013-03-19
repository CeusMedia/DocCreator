function appendTitle( string ) {
	var base	= "cmClasses Docs";
	var obj	= document.title;
	top.document.title	= obj + " @ " + base;
}

function openTreeToLink(href){
	var doc = $(top.control.document);
	var tree = doc.find("body #tree_container");
	tree.find("a").each(function(){
		if($(this).attr("href") == href){
			doc.find("#tree a.selected").removeClass("selected");
			$(this).addClass("selected");
			var parent = $(this).parent();
			while(parent.not("#tree").size()){
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
}

function initTree(){
	if($("#tree_container li").size()){
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
		type		 : "GET",
		url			: "search.php",
		async		: false,
		data		 : {query: $("#search-query").val()},
		dataType : "html",
		cache		: false,
		success	: function(html){
			$("#list").html(html);
		},
		error: function(msg){
			alert(msg);
		}
	});
}

$(document).ready(function(){

	if($("body.control").size()){
		if($("#tree").size())
			initTree();
		$("#control ul>li>a").click(function(){
			$("#control ul>li>a").removeClass('current');
			$(this).addClass('current');
		});
	}
	else if($("body.content").size()){
		if(top.location == self.location){
			top.location.href="index.html?" + document.URL;
			return false;
		}
		openTreeToLink(document.location.href.split("/").pop());
		if($("body.content-artefact").size()){
			if($("#index").size())
				initIndex();
		}
		else{
			if(!$("#file_info").size())
				$("li.index-file-info").hide();
			if(!$("#class_info").size())
				$("li.index-class-info").hide();
		}

	//  appendTitle();

		if($("#statistics").size())
			finishStatsStyle();
	}
});

