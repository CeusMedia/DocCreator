function appendTitle( string ) {
  var base	= "cmClasses Docs";
  var obj	= document.title;
  top.document.title	= obj + " @ " + base;
}

$(document).ready(function(){
  $("#source-line-hide-caller").click(function(){
    $(".source-line-hidden").hide();
    $("#source-line-hide-caller").hide();
    $("#source-line-show-caller").show();
  });
  $("#source-line-show-caller").click(function(){
    $(".source-line-hidden").show();
    $("#source-line-hide-caller").show();
    $("#source-line-show-caller").hide();
  });
  if($("#index").size()){
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
  if(!$("#file_info").size())
    $("li.index-file-info").hide();
  if(!$("#class_info").size())
    $("li.index-class-info").hide();

  appendTitle();

  //  --  ALL TABLES  --  //
  $("#statistics table.grid tr td").css("text-align","right");
  $("#statistics table.grid tr").each(function(){
    var cells = $("td", this);
    cells.eq(0).css("text-align","left");
  });

  //  --  TOTAL TABLE  --  //
  $("#statistics #totalTable table.grid tr").eq(1).css("font-weight","bold").css("font-size","1.1em");

});

