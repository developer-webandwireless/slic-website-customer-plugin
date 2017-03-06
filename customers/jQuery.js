

//$.noConflict();
jQuery(document).ready(function($) {
	   
$("#new-message-dialog").css({ top: '20%' }); //play with percentage

	  $(function() {
   $( "#datepicker" ).datepicker().datepicker("setDate", new Date());
   $("#datepicker").datepicker("option", "minDate", 0);
	$('#timepicker').timepicker({
	controlType: 'select',
	oneLine: true,
	defaultTime: 'now',
	timeFormat: 'hh:mm tt'
		});
		$('#timepicker').timepicker("setDate", new Date());
  });

	
	$('#newMessage').validate({
		rules:{
			message:{
				required: true,
				minlength: 2
			}
			

		},
		
		messages:{
			cname:{
				required: "Please enter your message",
				minlength: "your message must consist of at least 2 characters"
			}
			
		},
		 submitHandler: function(form) {    var $form = $( this ),
          url = $form.attr( 'action' );
			$(form).ajaxSubmit({
				type:"POST",
				dataType: "JSON",
                data: $(form).serialize(),
				url: url,
				
				success: function(data) { 
				var formData = $(form).serialize();
					//console.log(formData);
                    $('div#success').fadeIn();
					//var data = $.parseJSON(data);
					console.log(data);
					//location.reload();
					$(form)[0].reset();
					$( "#datepicker" ).datepicker().datepicker("setDate", new Date());
					
					var html;
					/*if(data.Status == 0)  html = "<i class='uk-icon-close message-not-sent' >";
					else  html = "<i class='uk-icon-check message-sent' >";*/
					html = "<i class='uk-badge uk-badge-danger' >MESSAGE NOT DELIVERED</i>";
					$('.display-message tr:first').after('<tr><td>' + data.Message + '</td> <td>'
														 + data.Date + '</td><td>'
														 + html + '</td></tr>');
														 
					if(data.Credits == 0) $("#add-new-message").addClass("disabled");	
					
					 $("#new-message-dialog").dialog('close');
                },
				error: function() {
                      $('#error').fadeIn();
					  $("#new-message-dialog").dialog('close');
						
                }
				
			 });
		 }
	});
	
	
		   $("#new-message-dialog").dialog({ 
			autoOpen: false,
			resizable: true,
			modal: true,
			width:'auto'
			
	   
	   });
		   $("#no-message-dialog").dialog({ 
			autoOpen: false,
			resizable: true,
			modal: true,
			width:'auto'
			
	   
	   });
	   
	   
	   $("#add-new-message").click(function () {
				
				  if($(this).hasClass('disabled')){
					 $("#no-message-dialog").dialog('open');
					return false;
				 }else{
						$("#new-message-dialog").dialog('open');
						return false;
				 }
        });

	
});