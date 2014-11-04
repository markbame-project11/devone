//////////////////////////////////////////////////////////////author:engr mark bame martires, ece - gearsoflife.mark@gmail.com
$(document).ready(function() 
{
	var website = "/dev3";
	var l = window.location;
    var a = l.pathname.split( '/' );
    var current_path = l.protocol + "//" + l.host;
    var checkgall = a[3];
    var checksubgall = a[4];

//////////////////////////////////////////////////////////////start:form validation
    $("#userform").validate({
	  rules: {
	    password: "required",
	    repassword: {
	      equalTo: "#password"
	    }
	  }
	});
//////////////////////////////////////////////////////////////end:form validation

//////////////////////////////////////////////////////////////start:make folder



//////////////////////////////////////////////////////////////end:make folder

//////////////////////////////////////////////////////////////start:upload file 
    var options = { 
	    beforeSend: function() 
	    {
	        $("#progress").show();
	        $("#bar").width('0%');
	        $("#message").html("");
	        $("#percent").html("0%");
	    },
	    uploadProgress: function(event, position, total, percentComplete) 
	    {
	        $("#bar").width(percentComplete+'%');
	        $("#percent").html(percentComplete+'%');
	 
	    },
	    success: function() 
	    {
	        $("#bar").width('100%');
	        $("#percent").html('100%');

	        if(checkgall == "gallery")
	        {
	        	location.reload();
	        }
	 
	    },
	    complete: function(response) 
	    {

	        $("#message").html("<font color='green'>"+response.responseText+"</font>");
	        $("#uploadmessage").fadeIn();
	        var dir = "/"+$("#dr").val();
	       	var instext = "<img src = '"+current_path+website+"/Lib/upload/Uploads"+dir+"/" + $("#filename").html()+"'>";

	       	if($("#photo").length)
	    	{	var delimage = $("#photo").val();
	    		
	    		var imagepath = current_path+website+"/Lib/upload/Uploads"+dir+"/" + $("#filename").html();

	    		$.post( current_path+"/"+website+"/Lib/upload/deletephoto.php",{ path: delimage,dir:"photos/"}, function( data ) {
	 	    		
		    		$("#photo").val(imagepath);
		    		$("#photoview").attr("src", imagepath);
	    		});

	    	}

	    	if($("#content").length)
	    	{
	        	tinyMCE.activeEditor.execCommand('mceInsertContent', false, instext);
	    	}
	       
	    },
	    error: function()
	    {
	        $("#message").html("<font color='red'> ERROR: unable to upload files</font>");
	 
	    }
	}; 

	$('#uploadform').ajaxForm(options);
//////////////////////////////////////////////////////////////end:upload file 



//////////////////////////////////////////////////////////////start:gallery
	$("#uploadgalimage").click(function() { 
       	
       	$('#dr').val("gallery/"+checksubgall);
       	
        $('#upload').modal("show");
    });	

    $("#makegalfolder").click(function() { 
        window.location.href = current_path+"/"+website+"/mbadmin/gallery/addfolder/"+$('#newfolder').val();
    });

    $(".galimgholder").click(function() { 

       $("#vimg").attr('src',$(this).children("img").attr('src'));
   	   $('#images').modal("show");
    });
//////////////////////////////////////////////////////////////end:gallery

//////////////////////////////////////////////////////////////start:user change password
    $("#changepassword").click(function() { 
        $("#passwordholder").html('<div class="form-group">'+
	              '<label for="" class="col-lg-2 control-label">Password</label>'+
	              '<div class="col-lg-6">'+
	                '<input type="password" class="form-control" name="password" id="password" value="" >'+
	             ' </div>'+
	            '</div>'+
	            '<div class="form-group">'+
	              '<label for="" class="col-lg-2 control-label">Retype Password</label>'+
	              '<div class="col-lg-6">'+
	                '<input type="password" class="form-control" name="repassword" id="repassword" value="" >'+
	              '</div>'+
	            '</div>');
    });	
//////////////////////////////////////////////////////////////end:user change password

//////////////////////////////////////////////////////////////jqplot:start
	
	if($("#analyticschart").length)
	{
		var retval = "";
		$.post( current_path+"/"+website+"/Lib/tracker/tracker.php",{ spectrum: "All"}, function( data ) {
	  		
	  		var jdata = jQuery.parseJSON(data);
	  		
		  	var plot1 = $.jqplot('analyticschart', [jdata], {
		    title:'Number of visits',
		    seriesDefaults: { 
		        showMarker:true	,
		        pointLabels: { show:true } 
		      },
		    axes:{xaxis:{renderer:$.jqplot.DateAxisRenderer}},
		    series:[{lineWidth:4, markerOptions:{style:'square'}}]
		  		});
	  	});

	  	
	}
//////////////////////////////////////////////////////////////jqplot:end

//////////////////////////////////////////////////////////////start:tinymce
 	tinymce.init({
		 plugins: [
      "advlist autolink lists link charmap print preview anchor",
      "searchreplace visualblocks code fullscreen",
      "insertdatetime table contextmenu paste"
      ],
    selector: "textarea",
    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | mybutton",
   	relative_urls: false,
   	convert_urls: false,
    remove_script_host : false,
    document_base_url : current_path + website,
    convert_urls : true,
    setup : function(ed) {
      ed.addButton('mybutton', {
        title : 'Upload',
        image : website + '/Cont/Themes/admin/images/upload.gif',
        onclick : function() {
          ed.focus();
            $("#progress").show();
	        $("#bar").width('0%');
	        $("#message").html("");
	        $("#percent").html("");
           $('#upload').modal("show");
            $("#uploadmessage").hide();
        }
      });
    }
 	});
//////////////////////////////////////////////////////////////end:tinymce
});


