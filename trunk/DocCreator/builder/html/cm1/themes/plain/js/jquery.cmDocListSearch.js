$(document).ready(function(){
  var countAll = $("div.file").size();
  $("#counter").html(countAll+" / "+countAll);
  $("#search-query").bind('keyup',function(){
    query = $(this).val();
    if(query){
      queryParts = query.split(/ /);
	  $("div.file").each(function(){
	    var file = $(this);
	    var found = true;
        $(queryParts).each(function(){
          if(this.length)
            count = $("li:containsIgnoreCase('"+this+"')",file).size();
            found = found && count;
//            console.log($("a",file).html()+": ["+this+"] "+count);
        })
	    visible = file.filter(":visible").size();
        if(found&&!visible)
          file.show();
        else if(!found&&visible)
          file.hide();
	  });
    }
    else
      $("div.file").show();
    count = $("div.file:visible").size();;
    $("#counter").html(count+" / "+countAll);
  });
});
