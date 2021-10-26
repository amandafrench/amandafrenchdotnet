(function( $ ) {
	'use strict';
	
	 $( window ).load(function() {

      $("form.sform").on("submit", function (e) { 
	      
	   if( ! $(this).hasClass("needs-validation") && ! $(this).hasClass("block-validation") && $(this).hasClass("ajax") ) {
		   
	      var form = $(this).attr('form');
	      
 	      if ( $( '#spinner-' + form ).length ) {
	         $('#submission-' + form).addClass('d-none');
             $('#spinner-' + form).removeClass('d-none');
          }
         
	      if ( ajax_sform_processing.outside !== true  ) {                                            
             $('#error-message-' + form).addClass("v-invisible");
          }
          
	      $('.sform-field, .sform, div.captcha').removeClass('is-invalid');
          $('#errors-' + form + ' span').removeClass('v-visible');   
          var postdata = $('form#form-' + form).serialize();

		  $.ajax({
            type: 'POST',
            dataType: 'json',
            url:ajax_sform_processing.ajaxurl, 
            data: postdata + '&action=formdata_ajax_processing',
            success: function(data){
              $('#spinner-' + form).addClass('d-none');
              $('#submission-' + form).removeClass('d-none');	
	          var error = data['error'];
	          var showerror = data['showerror'];
	          var notice = data['notice'];
	          var label = data['label'];
	          var field = data['field'];
	          var redirect = data['redirect'];
	          var redirect_url = data['redirect_url'];
              if( error === true ){
	              
	            $.each(data, function(field, label) {
	            $('#sform-' + field + '-' + form).addClass('is-invalid');
                $('label[for="sform-' + field + '-' + form + '"].sform').addClass('is-invalid');
                $('div#' + field + '-field-' + form).addClass('is-invalid');  
                $('#' + field + '-error-' + form + ' span').text(label);
	            if( $('form#form-' + form).hasClass("needs-focus") ) { $('input.is-invalid, textarea.is-invalid').first().focus(); }
	            else { $('#errors-' + form).focus(); }
                });
	            $('#errors-' + form + ' span').addClass('v-visible');  
	            if ( ajax_sform_processing.outside === true || ( ajax_sform_processing.outside !== true  && showerror === true ) ) {                                            
                 $('#errors-' + form + ' span').removeClass("v-invisible");
                 $('#errors-' + form + ' span').html(data.notice);
                }
              }
              if( error === false ){	              
                if( redirect === false ){
                  $('#form-' + form +', #sform-introduction-' + form +', #sform-bottom-' + form).addClass('d-none');
                  $('#sform-confirmation-' + form).html(data.notice);
                  $('#sform-confirmation-' + form).focus();
                }
                else {
	              document.location.href = redirect_url;
                  $('#errors-' + form + ' span').removeClass('v-visible');                                                
                }
              }
            },
 			error: function(data){
              $('#spinner-' + form).addClass('d-none');
              $('#submission-' + form).removeClass('d-none');	            
              $('#errors-' + form + ' span').removeClass("v-invisible");
              $('#errors-' + form + ' span').addClass('v-visible');                                                
              $('#errors-' + form + ' span').html(ajax_sform_processing.ajax_error);
              $('#errors-' + form).focus();
	        } 	
		  });
		  e.preventDefault();
		  return false;
	   }	  
		  
	  });
   
   	 });

})( jQuery );