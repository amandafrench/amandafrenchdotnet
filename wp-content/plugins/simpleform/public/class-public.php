<?php
	
/**
 * Defines the public-specific functionality of the plugin.
 *
 * @since      1.0
 */

class SimpleForm_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0
	 */
	 
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0
	 */
	 
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0
	 */
	 
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0
	 */
	 
	public function enqueue_styles() {
		
      wp_register_style('sform-public-style', plugins_url( 'css/public-min.css', __FILE__ ),[], filemtime( plugin_dir_path( __FILE__ ) . 'css/public-min.css' ) );

	  $plugin_file = 'simpleform/custom-style.css';

      if (is_child_theme() ) {
	    if ( file_exists( get_stylesheet_directory()  . '/' . $plugin_file ) ) {
	    wp_register_style( 'sform-custom-style', get_stylesheet_directory_uri() . '/simpleform/custom-style.css' , __FILE__ );
	    }
      }
      else { 
	    if ( file_exists( get_template_directory()  . '/' . $plugin_file ) ) {
	    wp_register_style( 'sform-custom-style', get_template_directory_uri() . '/simpleform/custom-style.css' , __FILE__ );
	    }
      }
 
    }
    
	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0
	 */
	 
	public function enqueue_scripts() {

 	  wp_register_script('sform_form_script', plugins_url( 'js/script-min.js', __FILE__ ), array( 'jquery' ), filemtime( plugin_dir_path( __FILE__ ) . 'js/script-min.js' ) );
 	  wp_register_script('sform_public_script', plugins_url( 'js/public-min.js', __FILE__ ), array( 'jquery' ), filemtime( plugin_dir_path( __FILE__ ) . 'js/public-min.js' ) );
	 
    }

	/**
	 * Apply shortcode and return the contact form for the public-facing side of the site.
	 *
	 * @since    1.0
	 */

    public function sform_shortcode($atts) { 
          
      $atts_array = shortcode_atts( array( 'id' => '1', 'type' => '' ), $atts );	 
	  
	  if ( $atts_array['id'] == '1'  ) { 
             $attributes = get_option('sform_attributes');
 	         $settings = get_option('sform_settings');
 	  } else { 
	 	     $option = 'sform_'.$atts_array['id'].'_attributes';
             $attributes_option = get_option($option);
             $attributes = $attributes_option != false ? $attributes_option : get_option('sform_attributes');
             $settings_option = get_option('sform_'.$atts_array['id'].'_settings');
             $settings = $settings_option != false ? $settings_option : get_option('sform_settings');
 	  }

      $show_for = ! empty( $attributes['show_for'] ) ? esc_attr($attributes['show_for']) : 'all';
      $user_role = ! empty( $attributes['user_role'] ) ? esc_attr($attributes['user_role']) : 'any';  
      $admin_limits = ! empty( $settings['admin_limits'] ) ? esc_attr($settings['admin_limits']) : 'false';
      $custom_css = ! empty( $attributes['additional_css'] ) ? esc_attr($attributes['additional_css']) : ''; 	    
      
      if ( ( current_user_can('manage_options') && $admin_limits != 'true' && $show_for == 'out' && is_user_logged_in() ) || ( !current_user_can('manage_options') && $show_for == 'out' && is_user_logged_in() ) || ( $show_for == 'in' && ! is_user_logged_in() ) )
      return;
        
      $current_user = wp_get_current_user();
      if ( ( current_user_can('manage_options') && $admin_limits != 'true' || !current_user_can('manage_options') ) && $show_for != 'all' && $user_role != 'any' && ! in_array( $user_role, (array) $current_user->roles ) )
      return;
	        
      include 'partials/form-variables.php'; 
	  
      $template = ! empty( $settings['form_template'] ) ? esc_attr($settings['form_template']) : 'default'; 
      $stylesheet = ! empty( $settings['stylesheet'] ) ? esc_attr($settings['stylesheet']) : 'false';
      
      switch ($template) {
      case 'customized':
      $file = '';
      if ( $stylesheet == 'false' ) {
	    wp_enqueue_style( 'sform-public-style' );
        wp_add_inline_style( 'sform-public-style', $custom_css );
	  }
      break;
      default:
      $file = 'partials/template.php';
      if ( $stylesheet == 'false' ) {
	    wp_enqueue_style( 'sform-public-style' );
        wp_add_inline_style( 'sform-public-style', $custom_css );	    
	  }
      }
      
      if ( $stylesheet != 'false' ) {
        $cssfile = ! empty( $settings['stylesheet_file'] ) ? esc_attr($settings['stylesheet_file']) : 'false';
	    if( $cssfile == 'true' ) {
        wp_enqueue_style( 'sform-custom-style' );
        }
      }
      
      if( empty($file) ) { 
 	    $template_file = 'simpleform/custom-template.php';
        if (is_child_theme() ) {
	      if ( file_exists( get_stylesheet_directory()  . '/' . $template_file ) ) {
 	         include get_stylesheet_directory() . '/simpleform/custom-template.php';
	      }
	      else {
             include 'partials/template.php'; 
		  }    
        }
        else { 
	      if ( file_exists( get_template_directory()  . '/' . $template_file ) ) {
 	         include get_template_directory() . '/simpleform/custom-template.php';
	      }
	      else {
             include 'partials/template.php'; 
		  }    
        }
      }
      else {
	   include $file;
      }
      
      $ajax = ! empty( $settings['ajax_submission'] ) ? esc_attr($settings['ajax_submission']) : 'false'; 
      $javascript = ! empty( $settings['javascript'] ) ? esc_attr($settings['javascript']) : 'false';
      $ajax_error = ! empty( $settings['ajax_error'] ) ? stripslashes(esc_attr($settings['ajax_error'])) : __( 'Error occurred during AJAX request. Please contact support!', 'simpleform' );
      $outside_error = ! empty( $settings['outside_error'] ) ? esc_attr($settings['outside_error']) : 'bottom';
      $outside = $outside_error == 'top' || $outside_error == 'bottom' ? 'true' : 'false';
      $multiple_spaces = ! empty( $settings['multiple_spaces'] ) ? esc_attr($settings['multiple_spaces']) : 'false';
      
      wp_localize_script('sform_public_script', 'ajax_sform_processing', array('ajaxurl' => admin_url('admin-ajax.php'), 'ajax_error' => $ajax_error, 'outside' => $outside ));	

      wp_enqueue_script( 'sform_form_script');
      if( $ajax == 'true' ) {
       wp_enqueue_script( 'sform_public_script');
      }

      if ( $multiple_spaces != 'false' )
      wp_add_inline_script( 'sform_form_script', 'jQuery(document).ready(function(){jQuery("input,textarea").on("input",function(){jQuery(this).val(jQuery(this).val().replace(/\s\s+/g," "));});});' );
      
      if ( $javascript == 'true' ) { 
        if (is_child_theme() ) { 
	        wp_enqueue_script( 'sform-custom-script',  get_stylesheet_directory_uri() . '/simpleform/custom-script.js',  array( 'jquery' ), '', true );
        }
        else { 
	        wp_enqueue_script( 'sform-custom-script',  get_template_directory_uri() . '/simpleform/custom-script.js',  array( 'jquery' ), '', true );
        }
	  }

      $above_form = isset( $_GET['sending'] ) && $_GET['sending'] == 'success' && isset( $_GET['form'] ) && $_GET['form'] == $atts_array['id'] ? '' : '<div id="sform-introduction-'.$atts_array['id'].'" class="sform-introduction '.$class_direction.'">'.$introduction_text.'</div>';
      $below_form = isset( $_GET['sending'] ) && $_GET['sending'] == 'success' && isset( $_GET['form'] ) && $_GET['form'] == $atts_array['id'] ? '' : '<div id="sform-bottom-'.$atts_array['id'].'" class="sform-bottom '.$class_direction.'">'.$bottom_text.'</div>';

  	  if ( $atts_array['type'] != '' ) { return $contact_form; } 
  	  else { return $above_form . $contact_form . $below_form; }

    } 

	/**
	 * Validate the form data after submission without Ajax
	 *
	 * @since    1.0
	 */
	 
    public function formdata_validation($data) {
		
	    $form_id = isset($_POST['form-id']) ? absint($_POST['form-id']) : '1';

	    if ( $form_id == '1' ) { 
             $attributes = get_option('sform_attributes');
 	         $settings = get_option('sform_settings');
 	    } else { 
             $attributes_option = get_option('sform_'.$form_id.'_attributes');
             $attributes = $attributes_option != false ? $attributes_option : get_option('sform_attributes');
             $settings_option = get_option('sform_'.$form_id.'_settings');
             $settings = $settings_option != false ? $settings_option : get_option('sform_settings');
 	    }
 	   
		$ajax = ! empty( $settings['ajax_submission'] ) ? esc_attr($settings['ajax_submission']) : 'false'; 
        $name_field = ! empty( $attributes['name_field'] ) ? esc_attr($attributes['name_field']) : 'visible';
        $name_requirement = ! empty( $attributes['name_requirement'] ) ? esc_attr($attributes['name_requirement']) : 'optional';
        $lastname_field = ! empty( $attributes['lastname_field'] ) ? esc_attr($attributes['lastname_field']) : 'hidden';
        $lastname_requirement = ! empty( $attributes['lastname_requirement'] ) ? esc_attr($attributes['lastname_requirement']) : 'optional';
        $email_field = ! empty( $attributes['email_field'] ) ? esc_attr($attributes['email_field']) : 'visible';
        $email_requirement = ! empty( $attributes['email_requirement'] ) ? esc_attr($attributes['email_requirement']) : 'required';
        $phone_field = ! empty( $attributes['phone_field'] ) ? esc_attr($attributes['phone_field']) : 'hidden';
        $phone_requirement = ! empty( $attributes['phone_requirement'] ) ? esc_attr($attributes['phone_requirement']) : 'optional';        
        $subject_field = ! empty( $attributes['subject_field'] ) ? esc_attr($attributes['subject_field']) : 'visible';
        $subject_requirement = ! empty( $attributes['subject_requirement'] ) ? esc_attr($attributes['subject_requirement']) : 'required';
        $consent_field = ! empty( $attributes['consent_field'] ) ? esc_attr($attributes['consent_field']) : 'visible';
        $consent_requirement = ! empty( $attributes['consent_requirement'] ) ? esc_attr($attributes['consent_requirement']) : 'required'; 
        $captcha_field = ! empty( $attributes['captcha_field'] ) ? esc_attr($attributes['captcha_field']) : 'hidden';            
      
        if( $ajax != 'true' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submission']) && isset( $_POST['sform_nonce'] ) && wp_verify_nonce( $_POST['sform_nonce'], 'sform_nonce_action' ) ) {
	
        $formdata = array(
			'name' => isset($_POST['sform-name']) ? sanitize_text_field($_POST['sform-name']) : '',
			'lastname' => isset($_POST['sform-lastname']) ? sanitize_text_field($_POST['sform-lastname']) : '',
			'email' => isset($_POST['sform-email']) ? sanitize_email($_POST['sform-email']) : '',
			'phone' => isset($_POST['sform-phone']) ? sanitize_text_field($_POST['sform-phone']) : '',			
			'subject' => isset($_POST['sform-subject']) ? sanitize_text_field($_POST['sform-subject']) : '',	
			'message' => isset($_POST['sform-message']) ? sanitize_textarea_field($_POST['sform-message']) : '',
			'consent' => isset($_POST['sform-consent']) ? 'true' : 'false',
		    'captcha' => isset( $_POST['sform-captcha'] ) && is_numeric( $_POST['sform-captcha'] ) ? intval($_POST['sform-captcha']) : '',
            'captcha_one' => isset( $_POST['captcha_one'] ) && is_numeric( $_POST['captcha_one'] ) ? intval($_POST['captcha_one']) : 0,
            'captcha_two' => isset( $_POST['captcha_two'] ) && is_numeric( $_POST['captcha_two'] ) ? intval($_POST['captcha_two']) : 0,
			'url' => isset($_POST['url-site']) ? sanitize_text_field($_POST['url-site']) : '',
			'telephone' => isset($_POST['hobbies']) ? sanitize_text_field($_POST['hobbies']) : '',
			'fakecheckbox' => isset($_POST['contact-phone']) ? 'true' : 'false',
		);
		
        $error = '';		
        $invalid_form = '';		

		if ( ! empty($formdata['url']) || ! empty($formdata['telephone']) || $formdata['fakecheckbox'] == 'true' ) {
		    $error .= $form_id.';form_honeypot;';
		}
		
	    $url_data = $formdata['url'];
	    $telephone_data = $formdata['telephone'];
	    $fakecheckbox_data = $formdata['fakecheckbox'];
	
	    if( has_filter('akismet_validation') ) {
   	        $check = '';
            $error .= $form_id.';' . apply_filters('akismet_validation', $formdata['name'], $formdata['email'], $formdata['message'], $check );
            // It would be best to send Akismet the original content, prior to sanitizing
        }
	  
	    $duplicate = ! empty( $settings['duplicate'] ) ? esc_attr($settings['duplicate']) : 'true';	
	    $last_request = get_transient( 'sform_last_message' ) ? get_transient( 'sform_last_message' ) : ''; 
	  
        if ( $duplicate == 'true' && ! empty($last_request) ) { 
	        
	      $separator = '<b>'. __('Message', 'simpleform') .':</b>&nbsp;&nbsp;';
	      $previous_request = isset(explode($separator, $last_request)[1]) ? str_replace('</div>', '', explode($separator, $last_request)[1]) : '';	    
	   
	      if ( $previous_request == $formdata['message'] ) {
	      
	      $string1 = '<div style="line-height:18px;"><b>'. __('From', 'simpleform') .':</b>&nbsp;&nbsp;';
	   	  $string2 = '<br><b>'. __('Date', 'simpleform');
	      $previous_requester = explode($string2,str_replace($string1, '', explode($separator, $last_request)[0]))[0];

          if ( ! empty($formdata['name']) ) { 
            $requester_name = $formdata['name'];      
          }
          else {
	        if ( is_user_logged_in() ) {
		    global $current_user;
		    $requester_name = ! empty($current_user->user_name) ? $current_user->user_name : $current_user->display_name;
            }
            else {
		    $requester_name = '';        
            }
          }
    
          if ( ! empty($formdata['lastname']) ) { 
            $requester_lastname = ' ' . $formdata['lastname'];      
          }
          else {
	        if ( is_user_logged_in() ) {
		    global $current_user;
		    $requester_lastname = ! empty($current_user->user_lastname) ? ' ' . $current_user->user_lastname : '';
            }
            else {
		    $requester_lastname = '';       
            }
          }

          $requester = $requester_name != '' || $requester_lastname != '' ? trim($requester_name . $requester_lastname) : __( 'Anonymous', 'simpleform' );
	    
          if ( ! empty($formdata['email']) ) { 
            $email_value = $formdata['email'];
          }
          else {
	        if ( is_user_logged_in() ) {
		      global $current_user;
		      $email_value = $current_user->user_email;
            }
            else {
		      $email_value = '';
            }
          }
	  
	      if ( strpos($previous_requester, $requester) !== false && strpos($previous_requester, $email_value) !== false ) {
			   $error .= $form_id.';duplicate_form;';
	      }
	      
	      }
	      
	    }

        $name_length = isset( $attributes['name_minlength'] ) ? esc_attr($attributes['name_minlength']) : '2';
        $name_regex = '#[0-9]+#';

        if ( $name_field == 'visible' || $name_field == 'registered' && is_user_logged_in() || $name_field == 'anonymous' && ! is_user_logged_in() )  {

        if ( $name_requirement == 'required' )	{
	      if (  ! empty($formdata['name']) && preg_match($name_regex, $formdata['name'] ) ) { 
		     $error .= $form_id.';name_invalid;';
	      }	
          else {
          if ( empty($formdata['name']) || strlen($formdata['name']) < $name_length ) {
		     $error .= $form_id.';name;';
          }
	      }		
        }

        else {	
		  if (  ! empty($formdata['name']) && preg_match($name_regex, $formdata['name'] ) ) { 
		     $error .= $form_id.';name_invalid;';
         }

	      else {
		  if ( ! empty($formdata['name']) && strlen($formdata['name']) < $name_length ) {
			 $error .= $form_id.';name;';
	      }         
	      }
        }

        }

        $data_name = $formdata['name'];

        $lastname_length = isset( $attributes['lastname_minlength'] ) ? esc_attr($attributes['lastname_minlength']) : '2';
        $lastname_regex = '#[0-9]+#';

        if ( $lastname_field == 'visible' || $lastname_field == 'registered' && is_user_logged_in() || $lastname_field == 'anonymous' && ! is_user_logged_in() )  {
         if ( $lastname_requirement == 'required' )	{
	      if (  ! empty($formdata['lastname']) && preg_match($lastname_regex, $formdata['lastname'] ) ) { 
		   $error .= $form_id.';lastname_invalid;';
	      }
          else {
          if ( empty($formdata['lastname']) || strlen($formdata['lastname']) < $lastname_length ) {
		     $error .= $form_id.';lastname;';
          }
	      }	
         }
         else {	
		  if (  ! empty($formdata['lastname']) && preg_match($lastname_regex, $formdata['lastname'] ) ) { 
		     $error .= $form_id.';lastname_invalid;';
	      }	
		  else {
		  if ( ! empty($formdata['lastname']) && strlen($formdata['lastname']) < $lastname_length ) {
		     $error .= $form_id.';lastname;';
		  }
          }
         }
        }

        $data_lastname = $formdata['lastname'];

        if ( $email_field == 'visible' || $email_field == 'registered' && is_user_logged_in() || $email_field == 'anonymous' && ! is_user_logged_in() )  {
          if ( $email_requirement == 'required' )	{
	       if ( empty($formdata['email']) || ! is_email($formdata['email']) ) {
              $error .= $form_id.';email;';
		   }
          }
          else {		
		   if ( ! empty($formdata['email']) && ! is_email($formdata['email']) ) {
              $error .= $form_id.';email;';
		   }
          }		
        }		

		$data_email = $formdata['email'];

        $phone_regex = '/^[0-9\-\(\)\/\+\s]*$/';  // allowed characters: -()/+ and space

        if ( $phone_field == 'visible' || $phone_field == 'registered' && is_user_logged_in() || $phone_field == 'anonymous' && ! is_user_logged_in() )  {
         if ( $phone_requirement == 'required' )	{
	      if (  ! empty($formdata['phone']) && ! preg_match($phone_regex, $formdata['phone'] ) ) { 
	         $error .= $form_id.';phone_invalid;';
	      }	
          else {		
          if ( empty($formdata['phone']) ) {
		     $error .= $form_id.';phone;';
          }
	      }		
         }
         else {		
		  if (  ! empty($formdata['phone']) && ! preg_match($phone_regex, $formdata['phone'] ) ) { 
	         $error .= $form_id.';phone_invalid;';
          }
         }		
        }		

		$data_phone = $formdata['phone'];

        $subject_length = isset( $attributes['subject_minlength'] ) ? esc_attr($attributes['subject_minlength']) : '5';
        $subject_regex = '/^[^#$%&=+*{}|<>]+$/';
		
        if ( $subject_field == 'visible' || $subject_field == 'registered' && is_user_logged_in() || $subject_field == 'anonymous' && ! is_user_logged_in() )  {
        if ( $subject_requirement == 'required' )	{
	      if (  ! empty($formdata['subject']) && ! preg_match($subject_regex, $formdata['subject'] ) ) { 
		     $error .= $form_id.';subject_invalid;';
    	  }		
          else {		
          if ( empty($formdata['subject']) || strlen($formdata['subject']) < $subject_length ) {
		     $error .= $form_id.';subject;';
          }
     	  }		
        }
        else {	
		  if (  ! empty($formdata['subject']) && ! preg_match($subject_regex, $formdata['subject'] ) ) { 
		     $error .= $form_id.';subject_invalid;';
          }
          else {		
		   if ( ! empty($formdata['subject']) && strlen($formdata['subject']) < $subject_length ) {
		      $error .= $form_id.';subject;';
		   }
          }		
        }
        }
	
        $data_subject = stripslashes($formdata['subject']);

        $message_length = isset( $attributes['$message_minlength'] ) ? esc_attr($attributes['$message_minlength']) : '10';
        $message_regex = '/^[^#$%&=+*{}|<>]+$/';

	    if (  ! empty($formdata['message']) && ! preg_match($message_regex, $formdata['message'] )  ) { 
		   $error .= $form_id.';message_invalid;';
	    } 
	    
	    else {		
	    if ( strlen($formdata['message']) < $message_length ) {
		   $error .= $form_id.';message;';
	    }
	    }

        $data_message = $formdata['message'];		
					
        if ( $consent_field == 'visible' || $consent_field == 'registered' && is_user_logged_in() || $consent_field == 'anonymous' && ! is_user_logged_in() )  {
        if ( $consent_requirement == 'required' && $formdata['consent'] !=  "true" )	{
 		    $error .= $form_id.';consent;'; 
       }
	    $data_consent = $formdata['consent'];
        }
        else {
		    $data_consent = '';
	    }
	    
        $captcha_type = '';
        $captcha_checking = apply_filters( 'sform_captcha_type', $attributes, $captcha_type );
	    $math_captcha = is_array($captcha_checking) || empty($captcha_checking) ? true : false;

        if ( $math_captcha && ( $captcha_field == 'visible' || $captcha_field == 'registered' && is_user_logged_in() || $captcha_field == 'anonymous' && ! is_user_logged_in() ) ) {	
        $captcha_one = $formdata['captcha_one'];
        $captcha_two = $formdata['captcha_two'];
	    $result = $captcha_one + $captcha_two;
        $answer = stripslashes($formdata['captcha']);		

	    if ( empty($captcha_one) || empty($captcha_two) || empty($answer) || $result != $answer ) {
			$data_captcha_one = '';
		    $data_captcha_two = '';
		   	$data_captcha = $answer;
		    $error .= $form_id.';captcha;';
	    }
	    else {
		    $data_captcha_one = $formdata['captcha_one'];
		    $data_captcha_two = $formdata['captcha_two'];
		   	$data_captcha = $answer;
	    }

        }
        
        else {
		    $data_captcha_one = '';
		    $data_captcha_two = '';
		   	$data_captcha = '';
	    }
	  
	    if( has_filter('recaptcha_challenge') ) {
   	        $check = '';
            $error .= $form_id.';' . apply_filters('recaptcha_challenge', $attributes, $error, $check );
        }
      
       // Remove duplicate Form ID
       $error = implode(';',array_unique(explode(';', $error)));      
      
	   $error = apply_filters('sform_send_email', $formdata, $error );
	
		$data = array( 'form' => $form_id, 'name' => $data_name,'lastname' => $data_lastname,'email' => $data_email,'phone' => $data_phone,'subject' => $data_subject,'message' => $data_message,'consent' => $data_consent,'captcha' => $data_captcha,'captcha_one' => $data_captcha_one,'captcha_two' => $data_captcha_two,'url' => $url_data,'telephone' => $telephone_data,'fakecheckbox' => $fakecheckbox_data,'error' => $error );

	    }
  
        else {	
        $data = array( 'form' => $form_id, 'name' => '','lastname' => '','email' => '','phone' =>'','subject' => '','message' => '','consent' => '','captcha' => '','captcha_one' => '','captcha_two' => '','url' => '','telephone' => '','fakecheckbox' => '' );
        
		}
		
        return $data;

	}

	/**
	 * Modify the HTTP response header (buffer the output so that nothing gets written until you explicitly tell to do it)
	 *
	 * @since    1.8.1
	 */
    
    public function ob_start_cache($error) {

 	  $form_id = isset($_POST['form-id']) ? absint($_POST['form-id']) : '1';

	  if ( $form_id == '1' ) { 
             $attributes = get_option('sform_attributes');
 	         $settings = get_option('sform_settings');
      } else { 
             $attributes_option = get_option('sform_'.$form_id.'_attributes');
             $attributes = $attributes_option != false ? $attributes_option : get_option('sform_attributes');
             $settings_option = get_option('sform_'.$form_id.'_settings');
             $settings = $settings_option != false ? $settings_option : get_option('sform_settings');
      }

      $ajax = ! empty( $settings['ajax_submission'] ) ? esc_attr($settings['ajax_submission']) : 'true'; 
      if( $ajax != 'true' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submission']) && isset( $_POST['sform_nonce'] ) && wp_verify_nonce( $_POST['sform_nonce'], 'sform_nonce_action' ) ) {
	      
	  if ($error == '') {
         ob_start();
      }
      
      }
      
    }

	/**
	 * Process the form data after submission with post callback function
	 *
	 * @since    1.0
	 */

    public function formdata_processing($formdata, $error) {    

 	  $form_id = isset($_POST['form-id']) ? absint($_POST['form-id']) : '1';
 	  
	  if ( $form_id == '1' ) { 
             $attributes = get_option('sform_attributes');
 	         $settings = get_option('sform_settings');
      } else { 
             $attributes_option = get_option('sform_'.$form_id.'_attributes');
             $attributes = $attributes_option != false ? $attributes_option : get_option('sform_attributes');
             $settings_option = get_option('sform_'.$form_id.'_settings');
             $settings = $settings_option != false ? $settings_option : get_option('sform_settings');
      }

      $ajax = ! empty( $settings['ajax_submission'] ) ? esc_attr($settings['ajax_submission']) : 'false'; 
 
      if( $ajax != 'true' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submission']) && isset( $_POST['sform_nonce'] ) && wp_verify_nonce( $_POST['sform_nonce'], 'sform_nonce_action' ) ) {
  	            
    if ( ! empty($formdata['name']) ) { 
    $requester_name = $formdata['name'];      
    $name_value = $formdata['name'];
    }
    else {
	  if ( is_user_logged_in() ) {
		global $current_user;
		$requester_name = ! empty($current_user->user_name) ? $current_user->user_name : $current_user->display_name;
        $name_value = $requester_name;
      }
      else {
		$requester_name = '';        
		$name_value = '';     
      }
    }
    
    if ( ! empty($formdata['lastname']) ) { 
    $requester_lastname = ' ' . $formdata['lastname'];      
    $lastname_value = ' ' . $formdata['lastname'];
    }
    else {
	  if ( is_user_logged_in() ) {
		global $current_user;
		$requester_lastname = ! empty($current_user->user_lastname) ? ' ' . $current_user->user_lastname : '';
        $lastname_value = ' ' . $requester_lastname;
      }
      else {
		$requester_lastname = '';       
		$lastname_value = '';     
      }
    }

      $requester = $requester_name != '' || $requester_lastname != '' ? trim($requester_name . $requester_lastname) : __( 'Anonymous', 'simpleform' );
            
    if ( ! empty($formdata['email']) ) { 
    $email_value = $formdata['email'];
    }
    else {
	  if ( is_user_logged_in() ) {
		global $current_user;
		$email_value = $current_user->user_email;
      }
      else {
		$email_value = '';
      }
    }
            
    if ( ! empty($formdata['phone']) ) { 
    $phone_value = $formdata['phone'];
    }
    else {
	$phone_value = '';       
    }

    if ( ! empty($formdata['subject']) ) { 
    $subject_value = $formdata['subject'];
    $request_subject = $formdata['subject'];
    }
    else {
	$subject_value = '';         
	$request_subject = __( 'No Subject', 'simpleform' );	         
    }

	$search = $form_id. ';';
    if ( str_replace($search,'',$error) == '' ) {
			
     $flagged = '';

	 if( has_filter('akismet_action') ) {
         $flagged = apply_filters('akismet_action', $formdata['name'], $formdata['email'], $formdata['message'], $flagged );
     }
     
     
    $mailing = 'false';
    $submission_timestamp = time();
    $submission_date = date('Y-m-d H:i:s');

	global $wpdb;
	$table_name = "{$wpdb->prefix}sform_submissions"; 
    $requester_type  = is_user_logged_in() ? 'registered' : 'anonymous';
    $user_ID = is_user_logged_in() ? get_current_user_id() : '0';
   
    $sform_default_values = array( "form" => $form_id, "date" => $submission_date, "requester_type" => $requester_type, "requester_id" => $user_ID );
    $extra_fields = array('notes' => '');
  
    $submitter = $requester_name != '' ? $requester_name : __( 'Anonymous', 'simpleform' );
  
    $sform_extra_values = array_merge($sform_default_values, apply_filters( 'sform_storing_values', $extra_fields, $form_id, $formdata['name'], $requester_lastname, $formdata['email'], $phone_value, $subject_value, $formdata['message'], $flagged ));
    
    $success = $wpdb->insert($table_name, $sform_extra_values);

    if ($success)  {

    $from_data = '<b>'. __('From', 'simpleform') .':</b>&nbsp;&nbsp;';
    $from_data .= $requester;
    
    if ( ! empty($email_value) ):
    $from_data .= '&nbsp;&nbsp;&lt;&nbsp;' . $email_value . '&nbsp;&gt;';
    else:
    $from_data .= '';
    endif;
    $from_data .= '<br>';
    
    if ( ! empty($phone_value) ) { $phone_data = '<b>'. __('Phone', 'simpleform') .':</b>&nbsp;&nbsp;' . $phone_value .'<br>'; }
    else { $phone_data = ''; }
    $from_data .= $phone_data;
    
    if ( ! empty($subject_value) ) { $subject_data = '<br><b>'. __('Subject', 'simpleform') .':</b>&nbsp;&nbsp;' . $flagged . $subject_value .'<br>'; }
    else { 
	      if ( !empty($flagged)) { $subject_data = '<br><b>'. __('Subject', 'simpleform') .':</b>&nbsp;&nbsp;' . $flagged .'<br>'; } 
	      else { $subject_data = '<br>'; }
    }
    $tzcity = get_option('timezone_string');
    $tzoffset = get_option('gmt_offset');
    if ( ! empty($tzcity))  { 
    $current_time_timezone = date_create('now', timezone_open($tzcity));
    $timezone_offset =  date_offset_get($current_time_timezone);
    $website_timestamp = $submission_timestamp + $timezone_offset; 
    }
    else { 
    $timezone_offset =  $tzoffset * 3600;
    $website_timestamp = $submission_timestamp + $timezone_offset;  
    }
       
    $website_date = date_i18n( get_option( 'date_format' ), $website_timestamp ) . ' ' . __('at', 'simpleform') . ' ' . date_i18n( get_option('time_format'), $website_timestamp );
	
	$last_message = '<div style="line-height:18px;">' . $from_data . '<b>'. __('Date', 'simpleform') .':</b>&nbsp;&nbsp;' . $website_date . $subject_data . '<b>'. __('Message', 'simpleform') .':</b>&nbsp;&nbsp;' .  $formdata['message'] . '</div>';

       set_transient('sform_last_'.$form_id.'_message', $last_message, 0 );
       set_transient( 'sform_last_message', $last_message, 0 );

    $notification = ! empty( $settings['notification'] ) ? esc_attr($settings['notification']) : 'true';

    if ($notification == 'true') { 
	       
    $to = ! empty( $settings['notification_recipient'] ) ? esc_attr($settings['notification_recipient']) : esc_attr( get_option( 'admin_email' ) );
    $submission_number = ! empty( $settings['submission_number'] ) ? esc_attr($settings['submission_number']) : 'visible';
    $subject_type = ! empty( $settings['notification_subject'] ) ? esc_attr($settings['notification_subject']) : 'request';
    $subject_text = ! empty( $settings['custom_subject'] ) ? stripslashes(esc_attr($settings['custom_subject'])) : __('New Contact Request', 'simpleform');
    $subject = $subject_type == 'request' ? $request_subject : $subject_text;
        
    if ( $submission_number == 'visible' && empty($flagged) ):
          $reference_number = $wpdb->get_var($wpdb->prepare("SELECT id FROM `$table_name` WHERE date = %s", $submission_date) );
    	  $admin_subject = '#' . $reference_number . ' - ' . $subject;	
     	  else:
     	  $admin_subject = $flagged . $subject;	
    endif;

    $admin_message_email = '<div style="line-height:18px; padding-top:10px;">' . $from_data . '<b>'. __('Sent', 'simpleform') .':</b>&nbsp;&nbsp;' . $website_date . $subject_data  . '<br>' .  $formdata['message'] . '</div>'; 
	$headers = "Content-Type: text/html; charset=UTF-8" .  "\r\n";
    $notification_reply = ! empty( $settings['notification_reply'] ) ? esc_attr($settings['notification_reply']) : 'true';
    $bcc = ! empty( $settings['bcc'] ) ? esc_attr($settings['bcc']) : '';
	
    if ( ( ! empty($formdata['email']) || is_user_logged_in() ) && $notification_reply == 'true' ) { $headers .= "Reply-To: ".$requester." <".$email_value.">" . "\r\n"; }
    if ( ! empty($bcc) ) { $headers .= "Bcc: ".$bcc. "\r\n"; } 
  
    do_action('check_smtp');
    add_filter( 'wp_mail_from_name', array ( $this, 'alert_sender_name' ) ); 
    add_filter( 'wp_mail_from', array ( $this, 'alert_sender_email' ) );
    $recipients = explode(',', $to);
	$sent = wp_mail($recipients, $admin_subject, $admin_message_email, $headers); 	
    remove_filter( 'wp_mail_from_name', array ( $this, 'alert_sender_name' ) );
    remove_filter( 'wp_mail_from', array ( $this, 'alert_sender_email' ) );

    if ($sent):
      $mailing = 'true';
    endif;

	}

    $confirmation = ! empty( $settings['autoresponder'] ) ? esc_attr($settings['autoresponder']) : 'false';
			
		if ( $confirmation == 'true' && ! empty($formdata['email']) ) {
			
		  $from = ! empty( $settings['autoresponder_email'] ) ? esc_attr($settings['autoresponder_email']) : esc_attr( get_option( 'admin_email' ) );
          $subject = ! empty( $settings['autoresponder_subject'] ) ? stripslashes(esc_attr($settings['autoresponder_subject'])) : esc_attr__( 'Your request has been received. Thanks!', 'simpleform' );
          $code_name = '[name]';
          $message = ! empty( $settings['autoresponder_message'] ) ? stripslashes(wp_kses_post($settings['autoresponder_message'])) : printf(__( 'Hi %s', 'simpleform' ),$code_name) . ',<p>' . __( 'We have received your request. It will be reviewed soon and we\'ll get back to you as quickly as possible.', 'simpleform' ) . __( 'Thanks,', 'simpleform' ) . __( 'The Support Team', 'simpleform' );          
          $reply_to = ! empty( $settings['autoresponder_reply'] ) ? esc_attr($settings['autoresponder_reply']) : $from;
		  $headers = "Content-Type: text/html; charset=UTF-8" . "\r\n";
		  $headers .= "Reply-To: <".$reply_to.">" . "\r\n";
	      $sql = "SELECT id FROM `$table_name` WHERE date = %s";
          $reference_number = $wpdb->get_var( $wpdb->prepare( $sql, $submission_date ) );
	      $tags = array( '[name]','[lastname]','[email]','[phone]','[subject]','[message]','[submission_id]' );
          $values = array( $formdata['name'],$formdata['lastname'],$formdata['email'],$formdata['phone'],$formdata['subject'],$formdata['message'],$reference_number );
          $content = str_replace($tags,$values,$message);
			
          do_action('check_smtp');
          add_filter( 'wp_mail_from_name', array ( $this, 'autoreply_sender_name' ) ); 
          add_filter( 'wp_mail_from', array ( $this, 'autoreply_sender_email' ) ); 
	      wp_mail($formdata['email'], $subject, $content, $headers);
          remove_filter( 'wp_mail_from_name', array ( $this, 'autoreply_sender_name' ) );
          remove_filter( 'wp_mail_from', array ( $this, 'autoreply_sender_email' ) );

		}
		
    $success_action = ! empty( $settings['success_action'] ) ? esc_attr($settings['success_action']) : 'message';    
    $thanks_url = ! empty( $settings['thanks_url'] ) ? esc_url($settings['thanks_url']) : '';    

    if( $success_action == 'message' ) { $redirect_to = esc_url_raw(add_query_arg( array('sending' => 'success','form' => $form_id), $_SERVER['REQUEST_URI'] )); }
    else { $redirect_to = ! empty($thanks_url) ? esc_url_raw($thanks_url) : esc_url_raw(add_query_arg( array('sending' => 'success','form' => $form_id), $_SERVER['REQUEST_URI'] )); }

    if ( ! has_filter('sform_post_message') ) { 
      if ( $mailing == 'true' ) {
	     header('Location: '. $redirect_to);
	     ob_end_flush();
         exit(); 
      } 	 
	  else  {  
		      $error = $form_id.';server_error';
	  }
	}
	
	else { $error = apply_filters( 'sform_post_message', $form_id, $mailing ); 
      if ( $error == '' ) {
	     header('Location: '. $redirect_to);
         ob_end_flush();
         exit(); 
      } 	 
      
	}
		 
    } 

    else  {  
	         $error = $form_id.';server_error';
    }
			
    }

    return $error;

    }
     
}
    
	/**
	 * Process the form data after submission with Ajax callback function
	 *
	 * @since    1.0
	 */

    public function formdata_ajax_processing() {
	
      if( 'POST' !== $_SERVER['REQUEST_METHOD'] ) { die ( 'Security checked!'); }
      elseif ( ! wp_verify_nonce( $_POST['sform_nonce'], 'sform_nonce_action' ) )  { die ( 'Security checked!'); }

      else {	
      $form_id = isset( $_POST['form-id'] ) ? absint($_POST['form-id']) : '1';
      $name = isset($_POST['sform-name']) ? sanitize_text_field($_POST['sform-name']) : '';
      $email = isset($_POST['sform-email']) ? sanitize_email($_POST['sform-email']) : '';
      $email_data = isset($_POST['sform-email']) ? sanitize_text_field($_POST['sform-email']) : '';
      $lastname = isset($_POST['sform-lastname']) ? sanitize_text_field($_POST['sform-lastname']) : '';
	  $phone = isset($_POST['sform-phone']) ? sanitize_text_field($_POST['sform-phone']) : '';			
      $object = isset($_POST['sform-subject']) ? sanitize_text_field(str_replace("\'", "’", $_POST['sform-subject'])) : '';
      $request = isset($_POST['sform-message']) ? sanitize_textarea_field($_POST['sform-message']) : '';
      $consent = isset($_POST['sform-consent']) ? 'true' : 'false';
      $captcha_one = isset($_POST['captcha_one']) && is_numeric( $_POST['captcha_one'] ) ? intval($_POST['captcha_one']) : ''; 
      $captcha_two = isset($_POST['captcha_two']) && is_numeric( $_POST['captcha_two'] ) ? intval($_POST['captcha_two']) : '';
      $captcha_result = isset($_POST['captcha_one']) && isset($_POST['captcha_two']) ? $captcha_one + $captcha_two : ''; 
      $captcha_answer = isset($_POST['sform-captcha']) && is_numeric( $_POST['sform-captcha'] ) ? intval($_POST['sform-captcha']) : '';
      $honeypot_url = isset($_POST['url-site']) ? sanitize_text_field($_POST['url-site']) : '';
      $honeypot_telephone = isset($_POST['hobbies']) ? sanitize_text_field($_POST['hobbies']) : '';
      $honeypot_checkbox = isset($_POST['contact-phone']) ? 'true' : 'false';

 	  if ( $form_id == '1' ) { 
             $attributes = get_option('sform_attributes');
 	         $settings = get_option('sform_settings');
      } else { 
             $attributes_option = get_option('sform_'.$form_id.'_attributes');
             $attributes = $attributes_option != false ? $attributes_option : get_option('sform_attributes');
             $settings_option = get_option('sform_'.$form_id.'_settings');
             $settings = $settings_option != false ? $settings_option : get_option('sform_settings');
      }
     
      $name_field = ! empty( $attributes['name_field'] ) ? esc_attr($attributes['name_field']) : 'visible';
      $name_requirement = ! empty( $attributes['name_requirement'] ) ? esc_attr($attributes['name_requirement']) : 'required';
      $lastname_field = ! empty( $attributes['lastname_field'] ) ? esc_attr($attributes['lastname_field']) : 'hidden';
      $lastname_requirement = ! empty( $attributes['lastname_requirement'] ) ? esc_attr($attributes['lastname_requirement']) : 'optional';
      $email_field = ! empty( $attributes['email_field'] ) ? esc_attr($attributes['email_field']) : 'visible';
      $email_requirement = ! empty( $attributes['email_requirement'] ) ? esc_attr($attributes['email_requirement']) : 'required';
      $phone_field = ! empty( $attributes['phone_field'] ) ? esc_attr($attributes['phone_field']) : 'hidden';
      $phone_requirement = ! empty( $attributes['phone_requirement'] ) ? esc_attr($attributes['phone_requirement']) : 'optional';  
      $subject_field = ! empty( $attributes['subject_field'] ) ? esc_attr($attributes['subject_field']) : 'visible';
      $subject_requirement = ! empty( $attributes['subject_requirement'] ) ? esc_attr($attributes['subject_requirement']) : 'required';
      $consent_field = ! empty( $attributes['consent_field'] ) ? esc_attr($attributes['consent_field']) : 'visible';
      $consent_requirement = ! empty( $attributes['consent_requirement'] ) ? esc_attr($attributes['consent_requirement']) : 'required'; 
      $captcha_field = ! empty( $attributes['captcha_field'] ) ? esc_attr($attributes['captcha_field']) : 'hidden';     
      
      if ( ! empty($name) ) { $requester_name = $name; }
      else {
	    if ( is_user_logged_in() ) {
		global $current_user;
		$requester_name = ! empty($current_user->user_name) ? $current_user->user_name : $current_user->display_name;
        }
        else { $requester_name = ''; }
      }
            
      if ( ! empty($lastname) ) { $requester_lastname = ' ' . $lastname; }
      else {
	    if ( is_user_logged_in() ) {
		global $current_user;
		$requester_lastname = ! empty($current_user->user_lastname) ? ' ' . $current_user->user_lastname : '';
        }
        else { $requester_lastname = ''; }
      }

      $requester = $requester_name != '' || $requester_lastname != '' ? trim($requester_name . $requester_lastname) : __( 'Anonymous', 'simpleform' );

      if ( ! empty($email) ) { $requester_email = $email; }
      else {
	    if ( is_user_logged_in() ) {
		global $current_user;
		$requester_email = $current_user->user_email;
        }
        else { $requester_email = ''; }
      }

      if ( ! empty($object) ) { 
        $subject_value = $object;
        $request_subject = $object;
      }
      else {
	    $subject_value = '';         
	    $request_subject = __( 'No Subject', 'simpleform' );	         
      }
      
       if (has_action('spam_check_execution')):
          do_action( 'spam_check_execution' );
       endif;	    
      
      if ( ! empty($honeypot_url) || ! empty($honeypot_telephone) || $honeypot_checkbox == 'true' ) { 
	  $error = ! empty( $settings['honeypot_error'] ) ? stripslashes(esc_attr($settings['honeypot_error'])) : __('Error occurred during processing data', 'simpleform');
        echo json_encode(array('error' => true, 'notice' => $error, 'showerror' => true ));
	    exit; 
	  }
	  
	  $duplicate = ! empty( $settings['duplicate'] ) ? esc_attr($settings['duplicate']) : 'true';	
	  $last_request = get_transient( 'sform_last_message' ) ? get_transient( 'sform_last_message' ) : ''; 
	  
      if ( $duplicate == 'true' && ! empty($last_request) ) { 	      
	    $separator = '<b>'. esc_html__('Message', 'simpleform') .':</b>&nbsp;&nbsp;';
	    $previous_request = isset(explode($separator, $last_request)[1]) ? str_replace('</div>', '', explode($separator, $last_request)[1]) : '';	  
		if ( $previous_request == $request ) {
	      $string1 = '<div style="line-height:18px;"><b>'. __('From', 'simpleform') .':</b>&nbsp;&nbsp;';
		  $string2 = '<br><b>'. __('Date', 'simpleform');
	      $previous_requester = explode($string2,str_replace($string1, '', explode($separator, $last_request)[0]))[0];
		  if ( strpos($previous_requester, $requester) !== false && strpos($previous_requester, $requester_email) !== false ) {
	        $error = ! empty( $settings['duplicate_error'] ) ? stripslashes(esc_attr($settings['duplicate_error'])) : __('The form has already been submitted. Thanks!', 'simpleform');
            echo json_encode(array('error' => true, 'notice' => $error, 'showerror' => true ));
	        exit; 
	      }
	    }
	  }
	  
       if (has_action('akismet_spam_checking')):
          do_action( 'akismet_spam_checking', $name, $email, $request );
       endif;	    

       $flagged = '';

	   if( has_filter('akismet_action') ) {
            $flagged = apply_filters('akismet_action', $name, $email, $request, $flagged );
       }
	  
        $outside_error = ! empty( $settings['outside_error'] ) ? esc_attr($settings['outside_error']) : 'bottom';
        $showerror = $outside_error == 'top' || $outside_error == 'bottom' ? true : false;
        $errors_query = array();
        $field_error = '';
        $empty_fields = ! empty( $settings['empty_fields'] ) ? stripslashes(esc_attr($settings['empty_fields'])) : __( 'There were some errors that need to be fixed', 'simpleform' );
        $characters_length = ! empty( $settings['characters_length'] ) ? esc_attr($settings['characters_length']) : 'true';
     
      if ( $name_field == 'visible' || $name_field == 'registered' && is_user_logged_in() || $name_field == 'anonymous' && ! is_user_logged_in() )  {  
        $name_length = isset( $attributes['name_minlength'] ) ? esc_attr($attributes['name_minlength']) : '2';
        $name_regex = '#[0-9]+#';
        $name_numeric_error = $characters_length == 'true' && ! empty( $settings['incomplete_name'] ) && preg_replace('/[^0-9]/', '', $settings['incomplete_name']) == $name_length ? stripslashes(esc_attr($settings['incomplete_name'])) : sprintf( __('Please enter at least %d characters', 'simpleform' ), $name_length );
        $name_generic_error = $characters_length != 'true' && ! empty( $settings['incomplete_name'] ) && preg_replace('/[^0-9]/', '', $settings['incomplete_name']) == '' ? stripslashes(esc_attr($settings['incomplete_name'])) : __('Please type your full name', 'simpleform' );
        $error_name_label = $characters_length == 'true' ? $name_numeric_error : $name_generic_error;
        $error_invalid_name_label = ! empty( $settings['invalid_name'] ) ? stripslashes(esc_attr($settings['invalid_name'])) : __( 'The name contains invalid characters', 'simpleform' );
        $error = ! empty( $settings['name_error'] ) ? stripslashes(esc_attr($settings['name_error'])) : __('Error occurred validating the name', 'simpleform');
        if ( $name_requirement == 'required' )	{
        if ( empty($name) || strlen($name) < $name_length ) {
	         $field_error = true;
	         $errors_query['name'] = $error_name_label;
             $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields; 
             $errors_query['error'] = TRUE;
             $errors_query['showerror'] = $showerror;
        }
	    if (  ! empty($name) && preg_match($name_regex, $name ) ) { 
            $field_error = true;
            $errors_query['name'] = $error_invalid_name_label;
            $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields; 
 	        $errors_query['error'] = TRUE;
            $errors_query['showerror'] = $showerror;
        }		
        }
        else {	
	    if ( ! empty($name) && strlen($name) < $name_length ) {
            $field_error = true;
            $errors_query['name'] = $error_name_label;
            $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields; 
	        $errors_query['error'] = TRUE;
            $errors_query['showerror'] = $showerror;
	    }
	    if ( ! empty($name) && preg_match($name_regex, $name ) ) { 
            $field_error = true;
            $errors_query['name'] = $error_invalid_name_label;
            $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields; 
 	   	    $errors_query['error'] = TRUE;
            $errors_query['showerror'] = $showerror;
        }
        }
      }

      if ( $lastname_field == 'visible' || $lastname_field == 'registered' && is_user_logged_in() || $lastname_field == 'anonymous' && ! is_user_logged_in() )  {  

        $lastname_length = isset( $attributes['lastname_minlength'] ) ? esc_attr($attributes['lastname_minlength']) : '2';
        $lastname_regex = '#[0-9]+#';
        $error_invalid_lastname_label = ! empty( $settings['invalid_lastname'] ) ? stripslashes(esc_attr($settings['invalid_lastname'])) : __( 'The last name contains invalid characters', 'simpleform' );
        $error = ! empty( $settings['lastname_error'] ) ? stripslashes(esc_attr($settings['lastname_error'])) : __('Error occurred validating the last name', 'simpleform');
$lastname_numeric_error = $characters_length == 'true' && ! empty( $settings['incomplete_lastname'] ) && preg_replace('/[^0-9]/', '', $settings['incomplete_lastname']) == $lastname_length ? stripslashes(esc_attr($settings['incomplete_lastname'])) : sprintf( __('Please enter at least %d characters', 'simpleform' ), $lastname_length );
$lastname_generic_error = $characters_length != 'true' && ! empty( $settings['incomplete_lastname'] ) && preg_replace('/[^0-9]/', '', $settings['incomplete_lastname']) == '' ? stripslashes(esc_attr($settings['incomplete_lastname'])) : __('Please type your full last name', 'simpleform' );
$error_lastname_label = $characters_length == 'true' ? $lastname_numeric_error : $lastname_generic_error;
	
        if ( $lastname_requirement == 'required' )	{
        if ( empty($lastname) || strlen($lastname) < $lastname_length ) {
            $field_error = true;
            $errors_query['lastname'] = $error_lastname_label;
         	$errors_query['error'] = TRUE;
            $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields; 	    
            $errors_query['showerror'] = $showerror;
        }
	    if (  ! empty($lastname) && preg_match($lastname_regex, $lastname ) ) { 
            $field_error = true;
            $errors_query['lastname'] = $error_invalid_lastname_label;
            $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields; 
      		$errors_query['error'] = TRUE;
            $errors_query['showerror'] = $showerror;
        }		
        }

        else {	
	    if ( ! empty($lastname) && strlen($lastname) < $lastname_length ) {
            $field_error = true;
            $errors_query['lastname'] = $error_lastname_label;
            $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields; 		                
            $errors_query['error'] = TRUE;
            $errors_query['showerror'] = $showerror;
        }
	    if (  ! empty($lastname) && preg_match($lastname_regex, $lastname ) ) { 
            $field_error = true;
            $errors_query['lastname'] = $error_invalid_lastname_label;       		               
            $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields; 
            $errors_query['error'] = TRUE;
            $errors_query['showerror'] = $showerror;
        }
        }

      }
      
      if ( $email_field == 'visible' || $email_field == 'registered' && is_user_logged_in() || $email_field == 'anonymous' && ! is_user_logged_in() )  {  

        $error_email_label = ! empty( $settings['invalid_email'] ) ? stripslashes(esc_attr($settings['invalid_email'])) : __( 'Please enter a valid email', 'simpleform' );
        $error = ! empty( $settings['email_error'] ) ? stripslashes(esc_attr($settings['email_error'])) : __('Error occurred validating the email', 'simpleform');
        if ( $email_requirement == 'required' )	{
	    if ( empty($email) || ! is_email($email) ) {
            $field_error = true;
            $errors_query['email'] = $error_email_label;
            $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields;  
	     	$errors_query['error'] = TRUE;
            $errors_query['showerror'] = $showerror;
	    }
        }
        else {		
	    if ( ! empty($email_data) && ! is_email($email) ) {
            $field_error = true;
            $errors_query['email'] = $error_email_label;	    		                
            $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields;  
            $errors_query['error'] = TRUE;
            $errors_query['showerror'] = $showerror;
        }
        }		
		
      }	
      
      $phone_regex = '/^[0-9\-\(\)\/\+\s]*$/';  // allowed characters: -()/+ and space

      if ( $phone_field == 'visible' || $phone_field == 'registered' && is_user_logged_in() || $phone_field == 'anonymous' && ! is_user_logged_in() )  {
        $empty_phone = ! empty( $settings['empty_phone'] ) ? stripslashes(esc_attr($settings['empty_phone'])) : __( 'Please provide your phone number', 'simpleform' );
        $error_phone_label = ! empty( $settings['invalid_phone'] ) ? stripslashes(esc_attr($settings['invalid_phone'])) : __( 'The phone number contains invalid characters', 'simpleform' );
        $error = ! empty( $settings['phone_error'] ) ? stripslashes(esc_attr($settings['phone_error'])) : __( 'Error occurred validating the phone number', 'simpleform' );
        if ( $phone_requirement == 'required' )	{
          if ( empty($phone) ) {
            $field_error = true;
            $errors_query['phone'] = $empty_phone;       		               
            $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields;
	        $errors_query['error'] = TRUE;
            $errors_query['showerror'] = $showerror;
          }
	      if (  ! empty($phone) && ! preg_match($phone_regex, $phone ) ) { 
            $field_error = true;
            $errors_query['phone'] = $error_phone_label;       		               
            $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields;
	        $errors_query['error'] = TRUE;
            $errors_query['showerror'] = $showerror;
	      }		
        }
        else {		
	      if ( ! empty($phone) && ! preg_match($phone_regex, $phone ) ) { 
            $field_error = true;
            $errors_query['phone'] = $error_phone_label;       		               
            $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields;
	        $errors_query['error'] = TRUE;
            $errors_query['showerror'] = $showerror;
	      }		
        }		
		
      }		
      
      if ( $subject_field == 'visible' || $subject_field == 'registered' && is_user_logged_in() || $subject_field == 'anonymous' && ! is_user_logged_in() )  { 
        $subject_length = isset( $attributes['subject_minlength'] ) ? esc_attr($attributes['subject_minlength']) : '5';
        $subject_regex = '/^[^#$%&=+*{}|<>]+$/';
        $error_invalid_subject_label = ! empty( $settings['invalid_subject'] ) ? stripslashes(esc_attr($settings['invalid_subject'])) : esc_attr__( 'Enter only alphanumeric characters and punctuation marks', 'simpleform' );
        $error = ! empty( $settings['subject_error'] ) ? stripslashes(esc_attr($settings['subject_error'])) : __('Error occurred validating the subject', 'simpleform');
        $subject_numeric_error = $characters_length == 'true' && ! empty( $settings['incomplete_subject'] ) && preg_replace('/[^0-9]/', '', $settings['incomplete_subject']) == $subject_length ? stripslashes(esc_attr($settings['incomplete_subject'])) : sprintf( __('Please enter a subject at least %d characters long', 'simpleform' ), $subject_length );
        $subject_generic_error = $characters_length != 'true' && ! empty( $settings['incomplete_subject'] ) && preg_replace('/[^0-9]/', '', $settings['incomplete_subject']) == '' ? stripslashes(esc_attr($settings['incomplete_subject'])) : __('Please type a short and specific subject', 'simpleform' );
        $error_subject_label = $characters_length == 'true' ? $subject_numeric_error : $subject_generic_error;

        if ( $subject_requirement == 'required' )	{
          if ( empty($object) || strlen($object) < $subject_length ) {
             $field_error = true;
             $errors_query['subject'] = $error_subject_label;       		               
             $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields;
	         $errors_query['error'] = TRUE;
             $errors_query['showerror'] = $showerror;
          }
	      if (  ! empty($object) && ! preg_match($subject_regex, $object ) ) { 
             $field_error = true;
             $errors_query['subject'] = $error_invalid_subject_label;       		               
             $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields;
	         $errors_query['error'] = TRUE;
             $errors_query['showerror'] = $showerror;
	      }		
        }

        else {	
    	if ( ! empty($object) && strlen($object) < $subject_length ) {
             $field_error = true;
             $errors_query['subject'] = $error_subject_label;       		               
             $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields;
	         $errors_query['error'] = TRUE;
             $errors_query['showerror'] = $showerror;
	    }
	    if (  ! empty($object) && ! preg_match($subject_regex, $object ) ) { 
             $field_error = true;
             $errors_query['subject'] = $error_invalid_subject_label;       		               
             $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields;
	         $errors_query['error'] = TRUE;
             $errors_query['showerror'] = $showerror;
        }
        }

      }

      $message_length = isset( $attributes['message_minlength'] ) ? esc_attr($attributes['message_minlength']) : '10';
      $message_regex = '/^[^#$%&=+*{}|<>]+$/';
      $error_invalid_message_label = ! empty( $settings['invalid_message'] ) ? stripslashes(esc_attr($settings['invalid_message'])) : esc_attr__( 'Enter only alphanumeric characters and punctuation marks', 'simpleform' );
      $error = ! empty( $settings['message_error'] ) ? stripslashes(esc_attr($settings['message_error'])) : __('Error occurred validating the message', 'simpleform');
$message_numeric_error = $characters_length == 'true' && ! empty( $settings['incomplete_message'] ) && preg_replace('/[^0-9]/', '', $settings['incomplete_message']) == $message_length ? stripslashes(esc_attr($settings['incomplete_message'])) : sprintf( __('Please enter a message at least %d characters long', 'simpleform' ), $message_length );
$message_generic_error = $characters_length != 'true' && ! empty( $settings['incomplete_message'] ) && preg_replace('/[^0-9]/', '', $settings['incomplete_message']) == '' ? stripslashes(esc_attr($settings['incomplete_message'])) : __('Please type a clearer message so we can respond appropriately', 'simpleform' );
$error_message_label = $characters_length == 'true' ? $message_numeric_error : $message_generic_error;
     	
      if ( empty($request) || strlen($request) < $message_length ) {
            $field_error = true;
            $errors_query['message'] = $error_message_label;       		               
            $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields;
	        $errors_query['error'] = TRUE;
            $errors_query['showerror'] = $showerror;
      }

      if (  ! empty($request) && ! preg_match($message_regex, $request )  ) { 
            $field_error = true;
            $errors_query['message'] = $error_invalid_message_label;       		               
            $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields;
	        $errors_query['error'] = TRUE;
            $errors_query['showerror'] = $showerror;
      }

      if ( $consent_field == 'visible' || $consent_field == 'registered' && is_user_logged_in() || $consent_field == 'anonymous' && ! is_user_logged_in() )  {  
        $error = ! empty( $settings['consent_error'] ) ? stripslashes(esc_attr($settings['consent_error'])) : __( 'Please accept our privacy policy before submitting form', 'simpleform' );
        if ( $consent_requirement == 'required' && $consent == "false" ) { 
             $field_error = true;
             $errors_query['consent'] = $error;       		               
             $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields;
	         $errors_query['error'] = TRUE;
             $errors_query['showerror'] = $showerror;
        }
      }
      
      do_action('recaptcha_ajax_challenge', $attributes, $captcha_one, $captcha_two, $captcha_result, $captcha_answer, $field_error );
  
      if ( ! has_action('recaptcha_ajax_challenge') && ( $captcha_field == 'visible' || $captcha_field == 'registered' && is_user_logged_in() || $captcha_field == 'anonymous' && ! is_user_logged_in() ) && ! empty($captcha_one) && ! empty($captcha_two) && ( empty($captcha_answer) || $captcha_result != $captcha_answer ) ) { 
        $error_captcha_label = ! empty( $settings['invalid_captcha'] ) ? stripslashes(esc_attr($settings['invalid_captcha'])) : esc_attr__( 'Please enter a valid captcha value', 'simpleform' );
	    $error = ! empty( $settings['captcha_error'] ) ? stripslashes(esc_attr($settings['captcha_error'])) : __('Error occurred validating the captcha', 'simpleform');
        $field_error = true;
        $errors_query['captcha'] = $error_captcha_label;       		               
        $errors_query['notice'] = !isset($errors_query['error']) ? $error : $empty_fields;
	    $errors_query['error'] = TRUE;
        $errors_query['showerror'] = $showerror;
      }

      else {
	  if ( empty($field_error) ) { 
      $mailing = 'false';
      $success_action = ! empty( $settings['success_action'] ) ? esc_attr($settings['success_action']) : 'message';    
      $confirmation_img = plugins_url( 'img/confirmation.png', __FILE__ );
      $thank_string1 = __( 'We have received your request!', 'simpleform' );
      $thank_string2 = __( 'Your message will be reviewed soon, and we\'ll get back to you as quickly as possible.', 'simpleform' );
      $thank_you_message = ! empty( $settings['success_message'] ) ? stripslashes(wp_kses_post($settings['success_message'])) : '<div class="form confirmation" tabindex="-1"><h4>' . $thank_string1 . '</h4><br>' . $thank_string2 . '</br><img src="'.$confirmation_img.'" alt="message received"></div>';    
      $thanks_url = ! empty( $settings['thanks_url'] ) ? esc_url($settings['thanks_url']) : '';    
	  if( $success_action == 'message' ):
		   $redirect = false;
		   $redirect_url = '';
		   else:
		   $redirect = true;
		   $redirect_url = $thanks_url;
	  endif;
      $submission_timestamp = time();
      $submission_date = date('Y-m-d H:i:s');
	  global $wpdb;
	  $table_name = "{$wpdb->prefix}sform_submissions"; 
      $requester_type  = is_user_logged_in() ? 'registered' : 'anonymous';
      $user_ID = is_user_logged_in() ? get_current_user_id() : '0';
      
      $sform_default_values = array( "form" => $form_id, "date" => $submission_date, "requester_type" => $requester_type, "requester_id" => $user_ID );  
      $extra_fields = array('notes' => '');
      $submitter = $requester_name != ''  ? $requester_name : __( 'Anonymous', 'simpleform' );      
      $sform_extra_values = array_merge($sform_default_values, apply_filters( 'sform_storing_values', $extra_fields, $form_id, $name, $requester_lastname, $email, $phone, $subject_value, $request, $flagged )); 
      $sform_additional_values = array_merge($sform_extra_values, apply_filters( 'sform_testing', $extra_fields ));
      $success = $wpdb->insert($table_name, $sform_additional_values);
      $server_error = ! empty( $settings['server_error'] ) ? stripslashes(esc_attr($settings['server_error'])) : __( 'Error occurred during processing data. Please try again!', 'simpleform' );
      
      if ( $success )  {		   
       if (has_action('spam_check_activation')):
          do_action( 'spam_check_activation' );
       endif;	      
       $notification = ! empty( $settings['notification'] ) ? esc_attr($settings['notification']) : 'true';
      
       if ($notification == 'true') { 
       $to = ! empty( $settings['notification_recipient'] ) ? esc_attr($settings['notification_recipient']) : esc_attr( get_option( 'admin_email' ) );
       $submission_number = ! empty( $settings['submission_number'] ) ? esc_attr($settings['submission_number']) : 'visible';
       $subject_type = ! empty( $settings['notification_subject'] ) ? esc_attr($settings['notification_subject']) : 'request';
       $subject_text = ! empty( $settings['custom_subject'] ) ? stripslashes(esc_attr($settings['custom_subject'])) : __('New Contact Request', 'simpleform');
       $subject = $subject_type == 'request' ? $request_subject : $subject_text;
         if ( $submission_number == 'visible' && empty($flagged) ):
         $reference_number = $wpdb->get_var($wpdb->prepare("SELECT id FROM `$table_name` WHERE date = %s", $submission_date) );
         $admin_subject = '#' . $reference_number . ' - ' . $subject;	
     	 else:
     	 $admin_subject = $flagged . $subject;	
         endif;
       $from_data = '<b>'. __('From', 'simpleform') .':</b>&nbsp;&nbsp;';
       $from_data .= $requester;       
       if ( ! empty($requester_email) ):
       $from_data .= '&nbsp;&nbsp;&lt;&nbsp;' . $requester_email . '&nbsp;&gt;';
       else:
       $from_data .= '';
       endif;
       $from_data .= '<br>';       
       if ( ! empty($phone) ) { $phone_data = '<b>'. __('Phone', 'simpleform') .':</b>&nbsp;&nbsp;' . $phone .'<br>'; }
       else { $phone_data = ''; }
       $from_data .= $phone_data;
       if ( ! empty($subject_value) ) { $subject_data = '<br><b>'. __('Subject', 'simpleform') .':</b>&nbsp;&nbsp;' . $flagged . $subject_value .'<br>'; }
       else { 
	       if ( !empty($flagged)) { $subject_data = '<br><b>'. __('Subject', 'simpleform') .':</b>&nbsp;&nbsp;' . $flagged .'<br>'; } 
	       else { $subject_data = '<br>'; }
	   }
       $tzcity = get_option('timezone_string'); 
       $tzoffset = get_option('gmt_offset');
       if ( ! empty($tzcity))  { 
       $current_time_timezone = date_create('now', timezone_open($tzcity));
       $timezone_offset =  date_offset_get($current_time_timezone);
       $website_timestamp = $submission_timestamp + $timezone_offset; 
       }
       else { 
       $timezone_offset =  $tzoffset * 3600;
       $website_timestamp = $submission_timestamp + $timezone_offset;  
       }
       $website_date = date_i18n( get_option( 'date_format' ), $website_timestamp ) . ' ' . __('at', 'simpleform') . ' ' . date_i18n( get_option('time_format'), $website_timestamp );
       $admin_message_email = '<div style="line-height:18px; padding-top:10px;">' . $from_data . '<b>'. __('Sent', 'simpleform') .':</b>&nbsp;&nbsp;' . $website_date . $subject_data  . '<br>' .  $request . '</div>';    
       $notification_email = ! empty( $settings['notification_email'] ) ? esc_attr($settings['notification_email']) : esc_attr( get_option( 'admin_email' ) );
	   $from =  $notification_email;
	   $notification_reply = ! empty( $settings['notification_reply'] ) ? esc_attr($settings['notification_reply']) : 'true';
       $bcc = ! empty( $settings['bcc'] ) ? esc_attr($settings['bcc']) : '';
	   $headers = "Content-Type: text/html; charset=UTF-8" .  "\r\n";
       if ( ( ! empty($email) || is_user_logged_in() ) && $notification_reply == 'true' ) { $headers .= "Reply-To: ".$requester." <".$requester_email.">" . "\r\n"; }
       if ( ! empty($bcc) ) { $headers .= "Bcc: ".$bcc. "\r\n"; } 
       
       do_action('check_smtp');
       add_filter( 'wp_mail_from_name', array ( $this, 'alert_sender_name' ) ); 
       add_filter( 'wp_mail_from', array ( $this, 'alert_sender_email' ) );
       $recipients = explode(',', $to);
	   $sent = wp_mail($recipients, $admin_subject, $admin_message_email, $headers); 
       remove_filter( 'wp_mail_from_name', array ( $this, 'alert_sender_name' ) );
       remove_filter( 'wp_mail_from', array ( $this, 'alert_sender_email' ) );
	   $last_message = '<div style="line-height:18px;">' . $from_data . '<b>'. __('Date', 'simpleform') .':</b>&nbsp;&nbsp;' . $website_date . $subject_data . '<b>'. __('Message', 'simpleform') .':</b>&nbsp;&nbsp;' .  $request . '</div>';
       set_transient('sform_last_'.$form_id.'_message', $last_message, 0 );
       set_transient( 'sform_last_message', $last_message, 0 ); 
        if ($sent):
         $mailing = 'true';
        endif;
	  } 

      $confirmation = ! empty( $settings['autoresponder'] ) ? esc_attr($settings['autoresponder']) : 'false';
			
	   if ( $confirmation == 'true' && ! empty($email) ) {
		  $from = ! empty( $settings['autoresponder_email'] ) ? esc_attr($settings['autoresponder_email']) : esc_attr( get_option( 'admin_email' ) );
          $subject = ! empty( $settings['autoresponder_subject'] ) ? stripslashes(esc_attr($settings['autoresponder_subject'])) : __( 'Your request has been received. Thanks!', 'simpleform' );
          $code_name = '[name]';
          $message = ! empty( $settings['autoresponder_message'] ) ? stripslashes(wp_kses_post($settings['autoresponder_message'])) : printf(__( 'Hi %s', 'simpleform' ),$code_name) . ',<p>' . __( 'We have received your request. It will be reviewed soon and we\'ll get back to you as quickly as possible.', 'simpleform' ) . __( 'Thanks,', 'simpleform' ) . __( 'The Support Team', 'simpleform' );          
          $reply_to = ! empty( $settings['autoresponder_reply'] ) ? esc_attr($settings['autoresponder_reply']) : $from;
		  $headers = "Content-Type: text/html; charset=UTF-8" . "\r\n";
		  $headers .= "Reply-To: <".$reply_to.">" . "\r\n";
	      $sql = "SELECT id FROM `$table_name` WHERE date = %s";
          $reference_number = $wpdb->get_var( $wpdb->prepare( $sql, $submission_date ) );
	      $tags = array( '[name]','[lastname]','[email]','[phone]','[subject]','[message]','[submission_id]' );
          $values = array( $name,$lastname,$email,$phone,$object,$request,$reference_number );
          $content = str_replace($tags,$values,$message);
          do_action('check_smtp');
          add_filter( 'wp_mail_from_name', array ( $this, 'autoreply_sender_name' ) );
          add_filter( 'wp_mail_from', array ( $this, 'autoreply_sender_email' ) ); 
	      wp_mail($email, $subject, $content, $headers);
          remove_filter( 'wp_mail_from_name', array ( $this, 'autoreply_sender_name' ) );
          remove_filter( 'wp_mail_from', array ( $this, 'autoreply_sender_email' ) );
	   }
	   
       if ( ! has_action('sform_ajax_message') ) {
          if ( $mailing == 'true' ) {
	   	   $errors_query['error'] = FALSE;
	       $errors_query['redirect'] = $redirect;  
	       $errors_query['redirect_url'] = $redirect_url;  
           $errors_query['notice'] = $thank_you_message;
	      } 
	      else {
	   	   $errors_query['error'] = TRUE;
           $errors_query['notice'] = $server_error;
           $errors_query['showerror'] = TRUE;
          } 	  
	   }
	   else { do_action( 'sform_ajax_message', $form_id, $mailing, $redirect, $redirect_url, $thank_you_message, $server_error ); }
       } 
      
      else  {
		$errors_query['error'] = TRUE;
        $errors_query['notice'] = $server_error;
        $errors_query['showerror'] = TRUE;
      }

	} 
	  }      
	          
      echo json_encode($errors_query);	 
      wp_die();
      
    } 

    }
 
	/**
	 * Force "From Name" in alert email 
	 *
	 * @since    1.0
	 */
   
    public function alert_sender_name() {
	    
     $id = isset( $_POST['form-id'] ) ? absint($_POST['form-id']) : '1';
	  
	 if ( $id == '1' ) { 
          $settings = get_option('sform_settings');
          $attribute = 'simpleform';
          $shortcode_values = apply_filters( 'sform_form', $attribute );
          $form_name = ! empty($shortcode_values['name']) ? stripslashes(esc_attr($shortcode_values['name'])) : esc_attr__( 'Contact Us Page','simpleform'); 
     } else { 
          $settings_option = get_option('sform_'.$id.'_settings');
          $settings = $settings_option != false ? $settings_option : get_option('sform_settings');
          $attribute = 'simpleform id="'.$id.'"';
          $shortcode_values = apply_filters( 'sform_form', $attribute );
          $form_name = ! empty($shortcode_values['name']) ? stripslashes(esc_attr($shortcode_values['name'])) : esc_attr__( 'Contact Form','simpleform'); 
     }
     
     $sender = ! empty( $settings['notification_name'] ) ? esc_attr($settings['notification_name']) : 'requester';
     $custom_sender = ! empty( $settings['custom_sender'] ) ? esc_attr($settings['custom_sender']) : esc_attr( get_bloginfo( 'name' ) ); 

     if ( $sender == 'requester') { 
	     $name = isset($_POST['sform-name']) ? sanitize_text_field($_POST['sform-name']) : ''; 
	     $lastname = isset($_POST['sform-lastname']) ? ' ' . sanitize_text_field($_POST['sform-lastname']) : '';
         $full_name = $name . $lastname;
	     if ( !empty(trim($full_name)) ) {
		    $alert_sender_name = $full_name;
		 }
         else { 
		    if ( is_user_logged_in() ) {
		      global $current_user;
		      $name = ! empty($current_user->user_name) ? $current_user->user_name : $current_user->display_name;
		      $lastname = ! empty($current_user->user_lastname) ? ' ' . $current_user->user_lastname : '';
              $alert_sender_name = trim($name . $lastname);
	        }
	        else {
              $alert_sender_name = esc_attr__( 'Anonymous', 'simpleform' );
	        }
          }
	     $sender_name = $alert_sender_name;
	     }
      
     if ( $sender == 'custom') { $sender_name = $custom_sender; }
    
     if ( $sender == 'form') { $sender_name = $form_name; }
     
     return $sender_name;
     
    }
  
	/**
	 * Force "From Email" in alert email
	 *
	 * @since    1.0
	 */
   
    public function alert_sender_email() {

     $id = isset( $_POST['form-id'] ) ? absint($_POST['form-id']) : '1';
	  
	 if ( $id == '1' ) { 
          $settings = get_option('sform_settings');
     } else { 
          $settings_option = get_option('sform_'.$id.'_settings');
          $settings = $settings_option != false ? $settings_option : get_option('sform_settings');
	 }
     
     $notification_email = ! empty( $settings['notification_email'] ) ? esc_attr($settings['notification_email']) : esc_attr( get_option( 'admin_email' ) );
      
     return $notification_email;
      
    }

	/**
	 * Force "From Name" in auto-reply email 
	 *
	 * @since    1.0
	 */
   
    public function autoreply_sender_name() {
	    
      $id = isset( $_POST['form-id'] ) ? absint($_POST['form-id']) : '1';
	  
	  if ( $id == '1' ) { 
          $settings = get_option('sform_settings');
      } else { 
          $settings_option = get_option('sform_'.$id.'_settings');
          $settings = $settings_option != false ? $settings_option : get_option('sform_settings');
	  }
	    
	  $sender_name = ! empty( $settings['autoresponder_name'] ) ? esc_attr($settings['autoresponder_name']) : esc_attr( get_bloginfo( 'name' ) ); 
	  
	  return $sender_name;
	  
    }

	/**
	 * Force "From Email" in auto-reply email 
	 *
	 * @since    1.0
	 */
   
    public function autoreply_sender_email() {
	
      $id = isset( $_POST['form-id'] ) ? absint($_POST['form-id']) : '1';
	  
	  if ( $id == '1' ) { 
          $settings = get_option('sform_settings');
      } else { 
          $settings_option = get_option('sform_'.$id.'_settings');
          $settings = $settings_option != false ? $settings_option : get_option('sform_settings');
	  }
	    
	  $from = ! empty( $settings['autoresponder_email'] ) ? esc_attr($settings['autoresponder_email']) : esc_attr( get_option( 'admin_email' ) );
	  
      return $from;
      
    }

} 