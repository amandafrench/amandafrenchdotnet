<?php

/**
 * Defines the admin-specific functionality of the plugin.
 *
 * @since      1.0
 */
	 
class SimpleForm_Admin {

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
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0
     */  
     
    public function sform_admin_menu() {
	    
      $contacts = __('Contacts', 'simpleform');           
      $contacts_bubble = apply_filters( 'sform_notification_bubble', $contacts );
      $hook = add_menu_page($contacts, $contacts_bubble,'manage_options','sform-submissions', array($this,'display_submissions'),'dashicons-email-alt', 24 );      
   
      global $sform_submissions;
      $submissions = __('Submissions','simpleform');
      $sform_submissions = add_submenu_page('sform-submissions', $submissions, $submissions, 'manage_options', 'sform-submissions', array($this,'display_submissions'));

      // global $sform_forms;
      $forms = __('Forms', 'simpleform');
      // $sform_forms = add_submenu_page('sform-submissions', $forms, $forms, 'manage_options', 'sform-forms', array($this,'display_forms'));
	  // Add screen option tab
      // add_action("load-$sform_forms", array ($this, 'forms_list_options') );

      // global $sform_form_page;
      $form = __('Form Management', 'simpleform');
      // $sform_form_page = add_submenu_page(null, $form, $form, 'manage_options', 'sform-form', array($this,'form_page'));

      global $sform_new;
      $new = __('Add New', 'simpleform');
      $sform_new = add_submenu_page('sform-submissions', $new, $new, 'manage_options', 'sform-new', array($this,'display_new'));
      
      global $sform_editor;
      /* translators: Used to indicate the form editor not user role */
      $editor = __('Editor', 'simpleform');
      $sform_editor = add_submenu_page('sform-submissions', $editor, $editor, 'manage_options', 'sform-editor', array($this,'display_editor'));

      global $sform_settings;
      $settings = __('Settings', 'simpleform');
      $sform_settings = add_submenu_page('sform-submissions', $settings, $settings, 'manage_options', 'sform-settings', array($this,'display_settings'));

	  global $sform_support;
	  $support = __('Support','simpleform-contact-form-submissions');
      $sform_support = add_submenu_page('sform-submissions', $support, $support, 'manage_options', 'sform-support', array ($this, 'support_page') );

      do_action('load_submissions_table_options');
      do_action('sform_submissions_submenu');

   }
  
    /**
     * Render the submissions page for this plugin.
     *
     * @since    1.0
     */
     
    public function display_submissions() {
      
      include_once('partials/submissions.php');
   
    }

    /**
     * Render the editing page for forms.
     *
     * @since    1.0
     */
    
    public function display_editor() {
     
      include_once('partials/editor.php');
    
    }
    
    /**
     * Render the page for a new form.
     *
     * @since    2.0
     */
    
    public function display_new() {
     
      include_once('partials/new.php');
    
    }
    
    /**
     * Render the forms page for this plugin.
     *
     * @since    2.1
     * /
     
    public function display_forms() {
      
      include_once('partials/forms.php');
   
    }

    /**
     * Render the form management page for this plugin.
     *
     * @since    2.1
     * /
     
    public function form_page() {
      
      include_once('partials/form.php');
   
    }

    /**
     * Render the settings page for forms.
     * @since    1.0
     */
    
    public function display_settings() {
      
      include_once('partials/settings.php');
    
    }

    /**
     * Render the submitted message page for this plugin.
     *
     * @since    2.0.1
     */
     
    public function support_page() {
      
      include_once( 'partials/support.php' );
    }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0
	 */
    
    public function enqueue_styles($hook) {
	    		
	 wp_register_style('sform-style', plugins_url( 'css/admin-min.css', __FILE__ ),[], filemtime( plugin_dir_path( __FILE__ ) . 'css/admin-min.css' ) );
	 
     global $sform_submissions;
     // global $sform_forms;
     // global $sform_form_page;
     global $sform_editor;
     global $sform_new;
     global $sform_settings;
	 global $sform_support;
     global $pagenow;
	   
     if( $hook != $sform_submissions /* && $hook != $sform_forms && $hook != $sform_form_page */ && $hook != $sform_editor && $hook != $sform_settings && $hook != $sform_new && $hook != $sform_support && $pagenow != 'widgets.php' ) 
     return;

	 wp_enqueue_style('sform-style'); 
	      
	}
	
	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0
	 */
	
	public function enqueue_scripts($hook){
	    		
     global $sform_submissions;
     // global $sform_forms;
     // global $sform_form_page;
     global $sform_editor;
     global $sform_settings;
     global $sform_new;
     global $pagenow;

     if( $hook != $sform_submissions /* && $hook != $sform_forms && $hook != $sform_form_page */ && $hook != $sform_editor && $hook != $sform_settings && $hook != $sform_new && $pagenow != 'widgets.php' ) 
     return;     
     
     $settings = get_option('sform_settings'); 
     $attributes = get_option('sform_attributes');
     $name_length = isset( $attributes['name_minlength'] ) ? esc_attr($attributes['name_minlength']) : '2';
     $name_numeric_error = ! empty( $settings['incomplete_name'] ) && preg_replace('/[^0-9]/', '', $settings['incomplete_name']) == $name_length ? stripslashes(esc_attr($settings['incomplete_name'])) : sprintf( __('Please enter at least %d characters', 'simpleform' ), $name_length );
     $name_generic_error = ! empty( $settings['incomplete_name'] ) && preg_replace('/[^0-9]/', '', $settings['incomplete_name']) == '' ? stripslashes(esc_attr($settings['incomplete_name'])) : __('Please type your full name', 'simpleform' );
     $lastname_length = isset( $attributes['lastname_minlength'] ) ? esc_attr($attributes['lastname_minlength']) : '2';
     $lastname_numeric_error = ! empty( $settings['incomplete_lastname'] ) && preg_replace('/[^0-9]/', '', $settings['incomplete_lastname']) == $lastname_length ? stripslashes(esc_attr($settings['incomplete_lastname'])) : sprintf( __('Please enter at least %d characters', 'simpleform' ), $lastname_length );
     $lastname_generic_error = ! empty( $settings['incomplete_lastname'] ) && preg_replace('/[^0-9]/', '', $settings['incomplete_lastname']) == '' ? stripslashes(esc_attr($settings['incomplete_lastname'])) : __('Please type your full last name', 'simpleform' );
     $subject_length = isset( $attributes['subject_minlength'] ) ? esc_attr($attributes['subject_minlength']) : '5';
     $subject_numeric_error = ! empty( $settings['incomplete_subject'] ) && preg_replace('/[^0-9]/', '', $settings['incomplete_subject']) == $subject_length ? stripslashes(esc_attr($settings['incomplete_subject'])) : sprintf( __('Please enter a subject at least %d characters long', 'simpleform' ), $subject_length );
     $subject_generic_error = ! empty( $settings['incomplete_subject'] ) && preg_replace('/[^0-9]/', '', $settings['incomplete_subject']) == '' ? stripslashes(esc_attr($settings['incomplete_subject'])) : __('Please type a short and specific subject', 'simpleform' );
     $message_length = isset( $attributes['message_minlength'] ) ? esc_attr($attributes['message_minlength']) : '10';
     $message_numeric_error = ! empty( $settings['incomplete_message'] ) && preg_replace('/[^0-9]/', '', $settings['incomplete_message']) == $message_length ? stripslashes(esc_attr($settings['incomplete_message'])) : sprintf( __('Please enter a message at least %d characters long', 'simpleform' ), $message_length );
     $message_generic_error = ! empty( $settings['incomplete_message'] ) && preg_replace('/[^0-9]/', '', $settings['incomplete_message']) == '' ? stripslashes(esc_attr($settings['incomplete_message'])) : __('Please type a clearer message so we can respond appropriately', 'simpleform' );
     $privacy_string = __( 'privacy policy','simpleform');
     /* translators: Used in place of %s in the string: "Please enter an error message to be displayed on %s of the form" */
     $top_position = __('top', 'simpleform');
     /* translators: Used in place of %s in the string: "Please enter an error message to be displayed on %s of the form" */
     $bottom_position = __('bottom', 'simpleform');
     /* translators: Used in place of %1$s in the string: "%1$s or %2$s the page content" */
     $edit = __( 'Edit','simpleform');
     /* translators: Used in place of %2$s in the string: "%1$s or %2$s the page content" */
     $view = __( 'view','simpleform');
     $page_links = sprintf( __('%1$s or %2$s the page content', 'simpleform'), $edit, $view); 
     $smtp_notes = __('Uncheck if you want to use a dedicated plugin to take care of outgoing email', 'simpleform' );
         
 	 wp_enqueue_script('sform_saving_options', plugins_url( 'js/admin-min.js', __FILE__ ), array( 'jquery' ), filemtime( plugin_dir_path( __FILE__ ) . 'js/admin-min.js' ) );
     wp_localize_script( 'sform_saving_options', 'ajax_sform_settings_options_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 	'copy' => __( 'Copy shortcode', 'simpleform' ), 'copied' => __( 'Shortcode copied', 'simpleform' ), 'saving' => __( 'Saving data in progress', 'simpleform' ), 'loading' => __( 'Saving settings in progress', 'simpleform' ), 'notes' => __( 'Create a directory inside your active theme\'s directory, name it "simpleform", copy one of the template files, and name it "custom-template.php"', 'simpleform' ), 'bottomnotes' => __( 'Display an error message on bottom of the form in case of one or more errors in the fields','simpleform'), 'topnotes' => __( 'Display an error message above the form in case of one or more errors in the fields','simpleform'), 'nofocus' => __( 'Do not move focus','simpleform'), 'focusout' => __( 'Set focus to error message outside','simpleform'), 'builder' => __( 'Change easily the way your contact form is displayed. Choose which fields to use and who should see them:', 'simpleform' ), 'appearance' => __( 'Tweak the appearance of your contact form to match it better to your site.', 'simpleform' ), 'adminurl' => admin_url(), 'pageurl' => site_url(), 'status' => __( 'Page in draft status not yet published','simpleform'), 'publish' =>  __( 'Publish now','simpleform'), 'edit' => $edit, 'view' => $view, 'pagelinks' => $page_links, 'show' => __( 'Show Configuration Warnings', 'simpleform' ), 'hide' => esc_html__( 'Hide Configuration Warnings', 'simpleform' ), 'cssenabled' => __( 'Create a directory inside your active theme\'s directory, name it "simpleform", add your CSS stylesheet file, and name it "custom-style.css"', 'simpleform' ), 'cssdisabled' => __( 'Keep unchecked if you want to use your personal CSS code and include it somewhere in your theme\'s code without using an additional file', 'simpleform' ), 'jsenabled' => __( 'Create a directory inside your active theme\'s directory, name it "simpleform", add your JavaScript file, and name it "custom-script.js"', 'simpleform' ), 'jsdisabled' => __( 'Keep unchecked if you want to use your personal JavaScript code and include it somewhere in your theme\'s code without using an additional file', 'simpleform' ), 'showcharacters' => __('Keep unchecked if you want to use a generic error message without showing the minimum number of required characters', 'simpleform' ), 'hidecharacters' => __('Keep checked if you want to show the minimum number of required characters and you want to make sure that\'s exactly the number you set for that specific field', 'simpleform' ), 'numnamer' => $name_numeric_error, 'gennamer' => $name_generic_error, 'numlster' => $lastname_numeric_error, 'genlster' => $lastname_generic_error, 'numsuber' => $subject_numeric_error, 'gensuber' => $subject_generic_error, 'nummsger' => $message_numeric_error, 'genmsger' => $message_generic_error, 'privacy' => $privacy_string, 'top' => $top_position, 'bottom' => $bottom_position, 'smtpnotes' => $smtp_notes, 'required' =>  __( '(required)','simpleform'), 'optional' =>  __( '(optional)','simpleform') )); 
	      
	}
	
	/**
	 * Enable SMTP server for outgoing emails
	 *
	 * @since    1.0
	 */

	public function check_smtp_server() {
		
       $settings = get_option('sform_settings');
       $server_smtp = ! empty( $settings['server_smtp'] ) ? esc_attr($settings['server_smtp']) : 'false';
       if ( $server_smtp == 'true' ) { add_action( 'phpmailer_init', array($this,'sform_enable_smtp_server') ); }
       else { remove_action( 'phpmailer_init', 'sform_enable_smtp_server' ); }
   
   }

	/**
	 * Save SMTP server configuration.
	 *
	 * @since    1.0
	 */
	
    public function sform_enable_smtp_server( $phpmailer ) {
   
      $settings = get_option('sform_settings');
      $smtp_host = ! empty( $settings['smtp_host'] ) ? esc_attr($settings['smtp_host']) : '';
      $smtp_encryption = ! empty( $settings['smtp_encryption'] ) ? esc_attr($settings['smtp_encryption']) : '';
      $smtp_port = ! empty( $settings['smtp_port'] ) ? esc_attr($settings['smtp_port']) : '';
      $smtp_authentication = isset( $settings['smtp_authentication'] ) ? esc_attr($settings['smtp_authentication']) : '';
      $smtp_username = ! empty( $settings['smtp_username'] ) ? esc_attr($settings['smtp_username']) : '';
      $smtp_password = ! empty( $settings['smtp_password'] ) ? esc_attr($settings['smtp_password']) : '';
      $username = defined( 'SFORM_SMTP_USERNAME' ) ? SFORM_SMTP_USERNAME : $smtp_username;
      $password = defined( 'SFORM_SMTP_PASSWORD' ) ? SFORM_SMTP_PASSWORD : $smtp_password;
      $phpmailer->isSMTP();
      $phpmailer->Host       = $smtp_host;
      $phpmailer->SMTPAuth   = $smtp_authentication;
      $phpmailer->Port       = $smtp_port;
      $phpmailer->SMTPSecure = $smtp_encryption;
      $phpmailer->Username   = $username;
      $phpmailer->Password   = $password;

    }

	/**
	 * Edit the contact form fields.
	 *
	 * @since    1.0
	 */
	
    public function shortcode_costruction() {

      if( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {	die ( 'Security checked!'); }
      if ( ! wp_verify_nonce( $_POST['verification_nonce'], "ajax-verification-nonce")) { exit("Security checked!"); }      
      if ( ! current_user_can('manage_options')) { exit("Security checked!"); }   
   
      else { 
       global $wpdb; 
       $table_shortcodes = $wpdb->prefix . 'sform_shortcodes';
       $form_id = isset( $_POST['form-id'] ) ? absint($_POST['form-id']) : '1';
       $form_attributes = get_option("sform_{$form_id}_attributes") != false ? get_option("sform_{$form_id}_attributes") : get_option("sform_attributes");
       $contact_form_name = ! empty( $form_attributes['form_name'] ) ? esc_attr($form_attributes['form_name']) : '';
       $target = ! empty( $form_attributes['show_for'] ) ? esc_attr($form_attributes['show_for']) : 'all';
       $form_name_value = isset($_POST['form-name']) ? sanitize_text_field($_POST['form-name']) : '';
       $default_name_value = $form_id == '1' ? __( 'Contact Us Page','simpleform') : '';
       $form_name = $form_name_value == '' ? $default_name_value : $form_name_value;
       $form_name_list = $wpdb->get_col( "SELECT name FROM $table_shortcodes WHERE id != $form_id" );
       $newform = isset($_POST['newform']) && $_POST['newform'] == 'true' ? 'true' : 'false';     
       $embed_in = isset($_POST['embed-in']) ? absint($_POST['embed-in']) : '';     
       $widget_id = isset( $_POST['widget-id'] ) ? absint($_POST['widget-id']) : '';
       
       if ( empty($widget_id) ) {
       $show_for = isset($_POST['show-for']) ? sanitize_text_field($_POST['show-for']) : 'all';
       $user_role = isset($_POST['user-role']) && $show_for == 'in' ? sanitize_text_field($_POST['user-role']) : 'any';
       }
       else {
	   $sform_widget = get_option('widget_sform_widget');
       if ( in_array($widget_id, array_keys($sform_widget)) ) { 
       $show_for = ! empty($sform_widget[$widget_id]['sform_widget_audience']) ? $sform_widget[$widget_id]['sform_widget_audience'] : 'all';
       $user_role = ! empty($sform_widget[$widget_id]['sform_widget_role']) ? $sform_widget[$widget_id]['sform_widget_role'] : 'any';
       }
       }
 
       if ( $show_for == 'out' ) {
	      $name_field = isset($_POST['name-field']) ? 'hidden' : 'anonymous';
	      $lastname_field = isset($_POST['lastname-field']) ? 'hidden' : 'anonymous';
          $email_field = isset($_POST['email-field']) ? 'hidden' : 'anonymous';
          $phone_field = isset($_POST['phone-field']) ? 'hidden' : 'anonymous';
          $subject_field = isset($_POST['subject-field']) ? 'hidden' : 'anonymous';
          $consent_field = isset($_POST['consent-field']) ? 'hidden' : 'anonymous';
          $captcha_field = isset($_POST['captcha-field']) ? 'hidden' : 'anonymous';
       }
       elseif ( $show_for == 'in' ) {
	      $name_field = isset($_POST['name-field']) ? 'hidden' : 'registered';
	      $lastname_field = isset($_POST['lastname-field']) ? 'hidden' : 'registered';
          $email_field = isset($_POST['email-field']) ? 'hidden' : 'registered';
          $phone_field = isset($_POST['phone-field']) ? 'hidden' : 'registered';
          $subject_field = isset($_POST['subject-field']) ? 'hidden' : 'registered';
          $consent_field = isset($_POST['consent-field']) ? 'hidden' : 'registered';
          $captcha_field = isset($_POST['captcha-field']) ? 'hidden' : 'registered';
       }
       else {
          $name_field = isset($_POST['name-field']) ? sanitize_text_field($_POST['name-field']) : 'visible';
          $lastname_field = isset($_POST['lastname-field']) ? sanitize_text_field($_POST['lastname-field']) : 'visible';
          $email_field = isset($_POST['email-field']) ? sanitize_text_field($_POST['email-field']) : 'visible';
          $phone_field = isset($_POST['phone-field']) ? sanitize_text_field($_POST['phone-field']) : 'visible';
          $subject_field = isset($_POST['subject-field']) ? sanitize_text_field($_POST['subject-field']) : 'visible';
          $consent_field = isset($_POST['consent-field']) ? sanitize_text_field($_POST['consent-field']) : 'visible';
          $captcha_field = isset($_POST['captcha-field']) ? sanitize_text_field($_POST['captcha-field']) : 'hidden';
       }
       
       $form = empty($widget_id) ? $form_id : '0';
       $introduction_text = isset($_POST['introduction-text']) ? wp_kses_post(trim($_POST['introduction-text'])) : '';
       $bottom_text = isset($_POST['bottom-text']) ? wp_kses_post(trim($_POST['bottom-text'])) : '';    
       $name_visibility = isset($_POST['name-visibility']) ? 'hidden' : 'visible';
       $name_label = isset($_POST['name-label']) ? sanitize_text_field(trim($_POST['name-label'])) : '';
       $name_placeholder = isset($_POST['name-placeholder']) ? sanitize_text_field($_POST['name-placeholder']) : '';
       $name_minlength = isset($_POST['name-minlength']) ? intval($_POST['name-minlength']) : '2';
       $name_maxlength = isset($_POST['name-maxlength']) ? intval($_POST['name-maxlength']) : '0';       
       $name_requirement = isset($_POST['name-requirement']) ? 'required' : 'optional';
       $lastname_visibility = isset($_POST['lastname-visibility']) ? 'hidden' : 'visible';
       $lastname_label = isset($_POST['lastname-label']) ? sanitize_text_field(trim($_POST['lastname-label'])) : '';
       $lastname_placeholder = isset($_POST['lastname-placeholder']) ? sanitize_text_field($_POST['lastname-placeholder']) : '';
       $lastname_minlength = isset($_POST['lastname-minlength']) ? intval($_POST['lastname-minlength']) : '2';
       $lastname_maxlength = isset($_POST['lastname-maxlength']) ? intval($_POST['lastname-maxlength']) : '0';       
       $lastname_requirement = isset($_POST['lastname-requirement']) ? 'required' : 'optional';
       $email_visibility = isset($_POST['email-visibility']) ? 'hidden' : 'visible';
       $email_label = isset($_POST['email-label']) ? sanitize_text_field(trim($_POST['email-label'])) : '';
       $email_placeholder = isset($_POST['email-placeholder']) ? sanitize_text_field($_POST['email-placeholder']) : '';
       $email_requirement = isset($_POST['email-requirement']) ? 'required' : 'optional';
       $phone_visibility = isset($_POST['phone-visibility']) ? 'hidden' : 'visible';
       $phone_label = isset($_POST['phone-label']) ? sanitize_text_field(trim($_POST['phone-label'])) : '';
       $phone_placeholder = isset($_POST['phone-placeholder']) ? sanitize_text_field($_POST['phone-placeholder']) : '';
       $phone_requirement = isset($_POST['phone-requirement']) ? 'required' : 'optional';
       $subject_visibility = isset($_POST['subject-visibility']) ? 'hidden' : 'visible';
       $subject_label = isset($_POST['subject-label']) ? sanitize_text_field(trim($_POST['subject-label'])) : '';
       $subject_placeholder = isset($_POST['subject-placeholder']) ? sanitize_text_field($_POST['subject-placeholder']) : '';
       $subject_minlength = isset($_POST['subject-minlength']) ? intval($_POST['subject-minlength']) : '5';
       $subject_maxlength = isset($_POST['subject-maxlength']) ? intval($_POST['subject-maxlength']) : '0';       
       $subject_requirement = isset($_POST['subject-requirement']) ? 'required' : 'optional';
       $message_visibility = isset($_POST['message-visibility']) ? 'hidden' : 'visible';
       $message_label = isset($_POST['message-label']) ? sanitize_text_field(trim($_POST['message-label'])) : '';
       $message_placeholder = isset($_POST['message-placeholder']) ? sanitize_text_field($_POST['message-placeholder']) : '';
       $message_minlength = isset($_POST['message-minlength']) ? intval($_POST['message-minlength']) : '10';
       $message_maxlength = isset($_POST['message-maxlength']) ? intval($_POST['message-maxlength']) : '0';  
       $consent_label = isset($_POST['consent-label']) ? wp_kses_post(trim($_POST['consent-label'])) : '';    
       $privacy_url = isset($_POST['privacy-page']) && intval($_POST['privacy-page']) > 0 ? get_page_link($_POST['privacy-page']) : '';
       /* translators: Used within the string "I have read and consent to the %s". It can be replaced with the hyperlink to the privacy policy page */       
       $privacy_string = __( 'privacy policy','simpleform');
       $link = $privacy_url != '' ? '<a href="' . $privacy_url . '" target="_blank">' . $privacy_string . '</a>' : '';
       $privacy_page = isset($_POST['privacy-page']) ? intval($_POST['privacy-page']) : '0';         
       $privacy_link = isset($_POST['privacy-link']) && $privacy_page != '0' && strpos($consent_label, $link) !== false ? 'true' : 'false';
       $consent_requirement = isset($_POST['consent-requirement']) ? 'required' : 'optional';
       $captcha_label = isset($_POST['captcha-label']) ? sanitize_text_field(trim($_POST['captcha-label'])) : '';
       $submit_label = isset($_POST['submit-label']) ? sanitize_text_field(trim($_POST['submit-label'])) : '';
       $label_position = isset($_POST['label-position']) ? sanitize_key($_POST['label-position']) : 'top';
       $label_size = isset($_POST['label-size']) ? sanitize_text_field($_POST['label-size']) : 'default';
       $required_sign = isset($_POST['required-sign']) ? 'true' : 'false';
       $required_word = isset($_POST['required-word']) ? sanitize_text_field(trim($_POST['required-word'])) : '';
       $word_position = isset($_POST['word-position']) ? sanitize_key($_POST['word-position']) : 'required';
       $lastname_alignment = isset($_POST['lastname-alignment']) ? sanitize_key($_POST['lastname-alignment']) : 'name';
       $phone_alignment = isset($_POST['phone-alignment']) ? sanitize_key($_POST['phone-alignment']) : 'alone';
       $submit_position = isset($_POST['submit-position']) ? sanitize_text_field($_POST['submit-position']) : 'centred';
       $form_direction = isset($_POST['form-direction']) ? sanitize_key($_POST['form-direction']) : 'ltr';
       $css_code = isset($_POST['additional-css']) ? strip_tags($_POST['additional-css']) : '';
       $additional_css = htmlspecialchars($css_code, ENT_HTML5 | ENT_NOQUOTES | ENT_SUBSTITUTE, 'utf-8');

       if ( !empty($newform) && $form_name_value == '' ) {
            echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'Enter a name for this form', 'simpleform' ) ));
	        exit;
       }
       
       if ( in_array($form_name, $form_name_list) )  { 
            $message = __('The name has already been used for another form, please use another one', 'simpleform' );
	        echo json_encode(array('error' => true, 'update' => false, 'message' => $message ));
	        exit; 
       }
       
       $update_result = '';
       
       if( $newform == 'false' ) {
         // Detects a modification of form name
         if ( $form_name != $contact_form_name || $show_for != $target ) {
           if ( $form_id == '1' ) {
             $update_shortcode = $wpdb->update($table_shortcodes, array('name' => $form_name, 'target' => $show_for ), array('shortcode' => 'simpleform'));
             $update_result = $update_shortcode ? 'done' : '';
           }
           else {
             $update_shortcode = $wpdb->update($table_shortcodes, array('name' => $form_name, 'target' => $show_for ), array('shortcode' => 'simpleform id="'.$form_id.'"' ));
             $update_result = $update_shortcode ? 'done' : '';
           }
         }     
       }
       
       else {
         $rows = $wpdb->get_row(" SHOW TABLE STATUS LIKE '$table_shortcodes' ");
         $shortcode_id = $rows->Auto_increment;		  
         $update_shortcode = $wpdb->insert($table_shortcodes, array('name' => $form_name_value, 'shortcode' => 'simpleform id="'.$shortcode_id.'"', 'target' => $show_for ));
         $update_result = $update_shortcode ? 'done' : '';
         
      }

       if ( $privacy_link == 'false' ) { 
	       $privacy_page = '0'; 
           $pattern = '/<a [^>]*>'.$privacy_string.'<\/a>/i';              
           $consent_label = preg_replace($pattern,$privacy_string,html_entity_decode($consent_label));
       }

       if ( $name_maxlength <= $name_minlength && $name_maxlength != 0 ) {
       echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'The maximum name length must not be less than the minimum name length', 'simpleform' ) ));
	   exit;
       }
       
       if ( $name_minlength == 0 && $name_requirement == 'required' ) {
       echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'You cannot set up a minimum length equal to 0 if the name field is required', 'simpleform' ) ));
	   exit;
       }
       
       if ( $lastname_maxlength <= $lastname_minlength && $lastname_maxlength != 0 ) {
       echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'The maximum last name length must not be less than the minimum last name length', 'simpleform' ) ));
	   exit;
       }

       if ( $lastname_minlength == 0 && $lastname_requirement == 'required' ) {
       echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'You cannot set up a minimum length equal to 0 if the last name field is required', 'simpleform' ) ));
	   exit;
       }

       if ( $subject_maxlength <= $subject_minlength && $subject_maxlength != 0 ) {
       echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'The maximum subject length must not be less than the minimum subject length', 'simpleform' ) ));
	   exit;
       }

       if ( $subject_minlength == 0 && $subject_requirement == 'required' ) {
       echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'You cannot set up a minimum length equal to 0 if the subject field is required', 'simpleform' ) ));
	   exit;
       }

       if ( $message_maxlength <= $message_minlength && $message_maxlength != 0 ) {
       echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'The maximum message length must not be less than the minimum message length', 'simpleform' ) ));
	   exit;
       }

       if ( ( $name_visibility == 'hidden' ||  $lastname_visibility == 'hidden' || $email_visibility == 'hidden' || $phone_visibility == 'hidden' || $subject_visibility == 'hidden' || $message_visibility == 'hidden' ) && $label_position == 'inline' ) {	       
	   $message = $form_direction == 'ltr' ? __( 'Labels cannot be left aligned if you have set a field label as hidden', 'simpleform' ) : __( 'Labels cannot be right aligned if you have set a field label as hidden', 'simpleform' );    
       echo json_encode(array('error' => true, 'update' => false, 'message' => $message ));
	   exit;
       }
       
       $attributes = array( 'form' => $form, 'form_name' => $form_name, 'show_for' => $show_for, 'user_role' => $user_role, 'introduction_text' => $introduction_text, 'bottom_text' => $bottom_text, 'name_field' => $name_field, 'name_visibility' => $name_visibility, 'name_label' => $name_label, 'name_placeholder' => $name_placeholder, 'name_minlength' => $name_minlength, 'name_maxlength' => $name_maxlength, 'name_requirement' => $name_requirement, 'lastname_field' => $lastname_field, 'lastname_visibility' => $lastname_visibility, 'lastname_label' => $lastname_label, 'lastname_placeholder' => $lastname_placeholder, 'lastname_minlength' => $lastname_minlength, 'lastname_maxlength' => $lastname_maxlength, 'lastname_requirement' => $lastname_requirement, 'email_field' => $email_field, 'email_visibility' => $email_visibility, 'email_label' => $email_label, 'email_placeholder' => $email_placeholder, 'email_requirement' => $email_requirement, 'phone_field' => $phone_field, 'phone_visibility' => $phone_visibility, 'phone_label' => $phone_label, 'phone_placeholder' => $phone_placeholder, 'phone_requirement' => $phone_requirement, 'subject_field' => $subject_field, 'subject_visibility' => $subject_visibility, 'subject_label' => $subject_label, 'subject_placeholder' => $subject_placeholder, 'subject_minlength' => $subject_minlength, 'subject_maxlength' => $subject_maxlength, 'subject_requirement' => $subject_requirement, 'message_visibility' => $message_visibility, 'message_label' => $message_label, 'message_placeholder' => $message_placeholder, 'message_minlength' => $message_minlength, 'message_maxlength' => $message_maxlength, 'consent_field' => $consent_field, 'consent_label' => $consent_label, 'privacy_link' => $privacy_link, 'privacy_page' => $privacy_page, 'consent_requirement' => $consent_requirement, 'captcha_field' => $captcha_field, 'captcha_label' => $captcha_label, 'submit_label' => $submit_label, 'label_position' => $label_position, 'lastname_alignment' => $lastname_alignment, 'phone_alignment' => $phone_alignment, 'submit_position' => $submit_position, 'label_size' => $label_size, 'required_sign' => $required_sign, 'required_word' => $required_word, 'word_position' => $word_position,  'form_direction' => $form_direction, 'additional_css' => $additional_css );     

       $extra_fields = array('extra_fields' => '');
       $sform_attributes = array_merge($attributes, apply_filters( 'sform_recaptcha_attributes', $extra_fields ));
          
       if ( $newform == 'false' ) {          
            if ( $form_id == '1' ) {
                 $update_attributes = update_option('sform_attributes', $sform_attributes);   
            }
            else {
	             $update_attributes = update_option('sform_'.$form_id.'_attributes', $sform_attributes);   
            }
       }
       else {
                  $update_attributes = update_option('sform_'.$shortcode_id.'_attributes', $sform_attributes);   
       }           
       
       if ($update_attributes) { $update_result .= 'done'; }

       if ( $update_result ) {

	     if ( $newform == 'false' ) {
              echo json_encode(array('error' => false, 'update' => true, 'message' => __( 'The contact form has been updated', 'simpleform' ) ));
	          exit;
         }
         else {
	          $post = !empty($embed_in) ? '&post='.$embed_in : '';     
	          $url = admin_url('admin.php?page=sform-settings&form=').$shortcode_id.'&status=new'.$post;
	          set_transient( 'sform_action_newform', $shortcode_id, 30 );
              echo json_encode(array('error' => false, 'update' => true, 'redirect' => true, 'url' => $url, 'message' => __( 'The contact form has been created', 'simpleform' ) ));
	          exit;
         }
	    
       }
   
       else {
        echo json_encode(array('error' => false, 'update' => false, 'message' => __( 'The contact form has already been updated', 'simpleform' ) ));
	    exit;
       }
      
       die();
       
      }

    }
   
	/**
	 * Edit settings
	 *
	 * @since    1.0
	 */
	
    public function sform_edit_options() {

      if( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {	die ( 'Security checked!'); }
      if ( ! wp_verify_nonce( $_POST['verification_nonce'], "ajax-verification-nonce")) { exit("Security checked!"); }   
      if ( ! current_user_can('manage_options')) { exit("Security checked!"); }   
   
      else {
       $form_id = isset( $_POST['form-id'] ) ? absint($_POST['form-id']) : '1';
	   $main_settings = get_option('sform_settings'); 
	   $admin_notices = isset($_POST['admin-notices']) ? 'true' : 'false';
	   $admin_limits = isset($_POST['admin-limits']) ? 'true' : 'false';
       $widget_editor = isset($_POST['widget-editor']) ? 'true' : 'false';
       $widget_options = isset($_POST['widget-options']) ? 'true' : 'false';
       $admin_color = isset($_POST['admin-color']) ? sanitize_text_field($_POST['admin-color']) : 'default';       
       $ajax_submission = isset($_POST['ajax-submission']) ? 'true' : 'false';
       $html5_validation = isset($_POST['html5-validation']) ? 'true' : 'false';
       $focus = isset($_POST['focus']) ? sanitize_key($_POST['focus']) : 'field';
       $spinner = isset($_POST['spinner']) ? 'true' : 'false';
       $template = isset($_POST['form-template']) ? sanitize_text_field($_POST['form-template']) : 'default';
       $form_borders = isset($_POST['form-borders']) ? sanitize_text_field($_POST['form-borders']) : 'dark';       
       $stylesheet = isset($_POST['stylesheet']) ? 'true' : 'false';
       $cssfile = isset($_POST['stylesheet-file']) ? 'true' : 'false';
       $javascript = isset($_POST['javascript']) ? 'true' : 'false';
       $uninstall = isset($_POST['deletion']) ? 'true' : 'false';
       $multiple_spaces = isset($_POST['multiple-spaces']) ? 'true' : 'false';
       $outside_error = isset($_POST['outside-error']) ? sanitize_text_field($_POST['outside-error']) : 'bottom';
       $characters_length = isset($_POST['characters-length']) ? 'true' : 'false';
       $empty_fields = isset($_POST['empty-fields']) ? sanitize_text_field(trim($_POST['empty-fields'])) : '';
       $empty_name = isset($_POST['empty-name']) ? sanitize_text_field(trim($_POST['empty-name'])) : '';
       $empty_lastname = isset($_POST['empty-lastname']) ? sanitize_text_field(trim($_POST['empty-lastname'])) : '';
       $empty_phone = isset($_POST['empty-phone']) ? sanitize_text_field(trim($_POST['empty-phone'])) : '';
       $empty_email = isset($_POST['empty-email']) ? sanitize_text_field(trim($_POST['empty-email'])) : '';
       $empty_subject = isset($_POST['empty-subject']) ? sanitize_text_field(trim($_POST['empty-subject'])) : '';
       $empty_message = isset($_POST['empty-message']) ? sanitize_text_field(trim($_POST['empty-message'])) : '';
       $empty_captcha = isset($_POST['empty-captcha']) ? sanitize_text_field(trim($_POST['empty-captcha'])) : '';
       $incomplete_name = isset($_POST['incomplete-name']) ? sanitize_text_field(trim($_POST['incomplete-name'])) : '';
       $invalid_name = isset($_POST['invalid-name']) ? sanitize_text_field(trim($_POST['invalid-name'])) : '';
       $name_error = isset($_POST['name-error']) ? sanitize_text_field(trim($_POST['name-error'])) : '';
       $incomplete_lastname = isset($_POST['incomplete-lastname']) ? sanitize_text_field(trim($_POST['incomplete-lastname'])) : '';
       $invalid_lastname = isset($_POST['invalid-lastname']) ? sanitize_text_field(trim($_POST['invalid-lastname'])) : '';
       $lastname_error = isset($_POST['lastname-error']) ? sanitize_text_field(trim($_POST['lastname-error'])) : '';
       $invalid_email = isset($_POST['invalid-email']) ? sanitize_text_field(trim($_POST['invalid-email'])) : '';
       $email_error = isset($_POST['email-error']) ? sanitize_text_field(trim($_POST['email-error'])) : '';       
       $invalid_phone = isset($_POST['invalid-phone']) ? sanitize_text_field(trim($_POST['invalid-phone'])) : '';
       $phone_error = isset($_POST['phone-error']) ? sanitize_text_field(trim($_POST['phone-error'])) : '';
       $incomplete_subject = isset($_POST['incomplete-subject']) ? sanitize_text_field(trim($_POST['incomplete-subject'])) : '';
       $invalid_subject = isset($_POST['invalid-subject']) ? sanitize_text_field(trim($_POST['invalid-subject'])) : '';
       $subject_error = isset($_POST['subject-error']) ? sanitize_text_field(trim($_POST['subject-error'])) : '';
       $incomplete_message = isset($_POST['incomplete-message']) ? sanitize_text_field(trim($_POST['incomplete-message'])) : '';
       $invalid_message = isset($_POST['invalid-message']) ? sanitize_text_field(trim($_POST['invalid-message'])) : '';
       $message_error = isset($_POST['message-error']) ? sanitize_text_field(trim($_POST['message-error'])) : '';
       $consent_error = isset($_POST['consent-error']) ? sanitize_text_field(trim($_POST['consent-error'])) : '';
       $invalid_captcha = isset($_POST['invalid-captcha']) ? sanitize_text_field(trim($_POST['invalid-captcha'])) : '';
       $captcha_error = isset($_POST['captcha-error']) ? sanitize_text_field(trim($_POST['captcha-error'])) : '';
       $honeypot_error = isset($_POST['honeypot-error']) ? sanitize_text_field(trim($_POST['honeypot-error'])) : '';
       $server_error = isset($_POST['server-error']) ? sanitize_text_field(trim($_POST['server-error'])) : '';
       $duplicate_error = isset($_POST['duplicate-error']) ? sanitize_text_field(trim($_POST['duplicate-error'])) : '';
       $ajax_error = isset($_POST['ajax-error']) ? sanitize_text_field(trim($_POST['ajax-error'])) : '';
       $success_action =  isset($_POST['success-action']) ? sanitize_key($_POST['success-action']) : '';
       $success_message = isset($_POST['success-message']) ? wp_kses_post(trim($_POST['success-message'])) : '';
       $confirmation_page = isset($_POST['confirmation-page']) ? sanitize_text_field($_POST['confirmation-page']) : '';
       $thanks_url = ! empty($confirmation_page) ? esc_url_raw(get_the_guid( $confirmation_page )) : ''; 
       $server_smtp = isset($_POST['server-smtp']) ? 'true' : 'false';
       $smtp_host = isset($_POST['smtp-host']) ? sanitize_text_field(trim($_POST['smtp-host'])) : '';
       $smtp_encryption = isset($_POST['smtp-encryption']) ? sanitize_key($_POST['smtp-encryption']) : '';
       $smtp_port = isset($_POST['smtp-port']) ? sanitize_text_field(trim($_POST['smtp-port'])) : '';
       $smtp_authentication = isset($_POST['smtp-authentication']) ? 'true' : 'false';
       $smtp_username = isset($_POST['smtp-username']) ? sanitize_text_field(trim($_POST['smtp-username'])) : '';
       $smtp_password = isset($_POST['smtp-password']) ? sanitize_text_field(trim($_POST['smtp-password'])) : '';
       $username = defined( 'SFORM_SMTP_USERNAME' ) ? SFORM_SMTP_USERNAME : $smtp_username;
       $password = defined( 'SFORM_SMTP_PASSWORD' ) ? SFORM_SMTP_PASSWORD : $smtp_password;
       $notification = isset($_POST['notification']) ? 'true' : 'false';       
       $notification_recipient = isset($_POST['notification-recipient']) ? sanitize_text_field(trim($_POST['notification-recipient'])) : '';
       $notification_recipients = str_replace(' ', '', $notification_recipient);
       $bcc = isset($_POST['bcc']) ? sanitize_text_field(trim($_POST['bcc'])) : '';      
       $notification_bcc = str_replace(' ', '', $bcc);
       $notification_email = isset($_POST['notification-email']) ? sanitize_text_field(trim($_POST['notification-email'])) : '';
       $notification_name = isset($_POST['notification-name']) ? sanitize_key($_POST['notification-name']) : '';
       $custom_sender = isset($_POST['custom-sender']) ? sanitize_text_field(trim($_POST['custom-sender'])) : '';
       $notification_subject = isset($_POST['notification-subject']) ? sanitize_key($_POST['notification-subject']) : '';
       $custom_subject = isset($_POST['custom-subject']) ? sanitize_text_field(trim($_POST['custom-subject'])) : '';
       // $notification_message = isset($_POST['notification-message']) ? wp_kses_post(trim($_POST['notification-message'])) : '';
       $notification_reply = isset($_POST['notification-reply']) ? 'true' : 'false';       
       $submission_number = isset($_POST['submission-number']) ? 'hidden' : 'visible';
       $autoresponder = isset($_POST['autoresponder']) ? 'true' : 'false';
       $autoresponder_email = isset($_POST['autoresponder-email']) ? sanitize_text_field(trim($_POST['autoresponder-email'])) : '';
       $autoresponder_name = isset($_POST['autoresponder-name']) ? sanitize_text_field(trim($_POST['autoresponder-name'])) : '';
       $autoresponder_subject = isset($_POST['autoresponder-subject']) ? sanitize_text_field(trim($_POST['autoresponder-subject'])) : '';
       $autoresponder_message = isset($_POST['autoresponder-message']) ? wp_kses_post(trim($_POST['autoresponder-message'])) : '';
       $autoresponder_reply = isset($_POST['autoresponder-reply']) ? sanitize_text_field(trim($_POST['autoresponder-reply'])) : '';
	   $form_pageid = ! empty( $main_settings['form_pageid'] ) && get_post_status($main_settings['form_pageid']) ? absint($main_settings['form_pageid']) : '';  
	   $confirmation_pageid = ! empty( $main_settings['confirmation_pageid'] ) && get_post_status($main_settings['confirmation_pageid']) ? absint($main_settings['confirmation_pageid']) : '';	 
	   $duplicate = isset($_POST['duplicate']) ? 'true' : 'false';	
 
       if ( $stylesheet != 'true' )  { $cssfile = 'false'; }
       if ( $template != 'transparent' )  { $form_borders = 'dark'; }
       if ( $ajax_submission != 'true' )  { $spinner = 'false'; }
       if ( $success_action == 'message' )  { $confirmation_page = ''; }

       if (has_action('sform_validate_akismet_settings')):
	       do_action('sform_validate_akismet_settings');	
	   endif;

       if (has_action('sform_validate_recaptcha_settings')):
	       do_action('sform_validate_recaptcha_settings');	
	   endif;

       if ( $html5_validation == 'false' && $focus == 'alert' )  { 
	        echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'Focus is automatically set to first invalid field if HTML5 validation is not disabled', 'simpleform' ) ));
	        exit; 
       }

       if ( $server_smtp == 'true' && $notification == 'false' && $autoresponder == 'false' )  { 
	        echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'The SMTP server for outgoing email cannot be enabled if the notification or confirmation email is not enabled', 'simpleform' ) ));
	        exit; 
       }
        
	   if (  $server_smtp == 'true' && empty($smtp_host) ) {
            echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'Please enter the SMTP address', 'simpleform' ) ));
	        exit; 
       }

	   if (  $server_smtp == 'true' && empty($smtp_encryption) ) {
            echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'Please enter the encryption type to relay outgoing email to the SMTP server', 'simpleform' )  ));
	        exit; 
       }

	   if (  $server_smtp == 'true' && empty($smtp_port) ) {
            echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'Please enter the port to relay outgoing email to the SMTP server', 'simpleform' )  ));
	        exit; 
       }
        
	   if (  $server_smtp == 'true' && ! ctype_digit(strval($smtp_port)) ) {
            echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'Please enter a valid port to relay outgoing email to the SMTP server', 'simpleform' ) ));
	        exit; 
       }

	   if (  $server_smtp == 'true' && $smtp_authentication == 'true' && empty( $username ) ) { 
            echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'Please enter username to log in to SMTP server', 'simpleform' )  ));
	        exit; 
       }
	
	   if (  $server_smtp == 'true' && $smtp_authentication == 'true' &&  ! empty($username) && ! is_email( $username ) ) {
            echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'Please enter a valid email address to log in to SMTP server', 'simpleform' )  ));
	        exit; 
       }
        
	   if (  $server_smtp == 'true' && $smtp_authentication == 'true' && empty( $password ) ) {
            echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'Please enter password to log in to SMTP server', 'simpleform' )  ));
	        exit; 
       }
 
       if (has_action('sforms_validate_submissions_settings')):
	       do_action('sforms_validate_submissions_settings');	
	   else:
       if ( $notification == 'false' )  { 
 	        echo json_encode(array('error' => true, 'update' => false, 'message' => __( 'You need to enable the notification email', 'simpleform' ) ));
	        exit; 
       }
	   endif;
	   
	   if ( $form_id == '1' ) { 

         $settings = array(
	             'admin_notices' => $admin_notices,
	             'admin_limits' => $admin_limits,
                 'widget_editor' => $widget_editor,
                 'widget' => $widget_options,
                 'admin_color' => $admin_color,
                 'ajax_submission' => $ajax_submission,
                 'spinner' => $spinner,
	             'html5_validation' => $html5_validation,
	             'focus' => $focus,
                 'form_template' => $template,
                 'form_borders' => $form_borders,
                 'stylesheet' => $stylesheet,
                 'stylesheet_file' => $cssfile, 
                 'javascript' => $javascript,
                 'deletion_data' => $uninstall,
                 'multiple_spaces' => $multiple_spaces,
                 'outside_error' => $outside_error,
                 'empty_fields' => $empty_fields,
                 'characters_length' => $characters_length,
                 'empty_name' => $empty_name,
                 'incomplete_name' => $incomplete_name, 
                 'invalid_name' => $invalid_name, 
                 'name_error' => $name_error,      
                 'empty_lastname' => $empty_lastname,
                 'incomplete_lastname' => $incomplete_lastname, 
                 'invalid_lastname' => $invalid_lastname, 
                 'lastname_error' => $lastname_error,      
                 'empty_email' => $empty_email,
                 'invalid_email' => $invalid_email,  
                 'email_error' => $email_error,  
                 'empty_phone' => $empty_phone,
                 'invalid_phone' => $invalid_phone, 
                 'phone_error' => $phone_error,      
                 'empty_subject' => $empty_subject,
                 'incomplete_subject' => $incomplete_subject, 
                 'invalid_subject' => $invalid_subject,  
                 'subject_error' => $subject_error,                    
                 'empty_message' => $empty_message,
                 'incomplete_message' => $incomplete_message,    
                 'invalid_message' => $invalid_message,
                 'message_error' => $message_error,
                 'consent_error' => $consent_error,
                 'empty_captcha' => $empty_captcha,
                 'invalid_captcha' => $invalid_captcha,    
                 'captcha_error' => $captcha_error,
                 'honeypot_error' => $honeypot_error,    
                 'duplicate_error' => $duplicate_error,
                 'ajax_error' => $ajax_error,        
                 'server_error' => $server_error,
                 'success_action' => $success_action,         
                 'success_message' => $success_message, 
                 'confirmation_page' => $confirmation_page,        
                 'thanks_url' => $thanks_url,
                 'server_smtp' => $server_smtp,
                 'smtp_host' => $smtp_host,
                 'smtp_encryption' => $smtp_encryption,
                 'smtp_port' => $smtp_port,
                 'smtp_authentication' => $smtp_authentication,
                 'smtp_username' => $smtp_username,
                 'smtp_password' => $smtp_password,
                 'notification' => $notification,
                 'notification_recipient' => $notification_recipients,
                 'bcc' => $notification_bcc,
                 'notification_email' => $notification_email,
                 'notification_name' => $notification_name,
                 'custom_sender' => $custom_sender,
                 'notification_subject' => $notification_subject,
                 'custom_subject' => $custom_subject,
                 // 'notification_message' => $notification_message,
                 'notification_reply' => $notification_reply,
                 'submission_number' => $submission_number,  
                 'autoresponder' => $autoresponder, 
                 'autoresponder_email' => $autoresponder_email,
                 'autoresponder_name' => $autoresponder_name,
                 'autoresponder_subject' => $autoresponder_subject,
                 'autoresponder_message' => $autoresponder_message,
                 'autoresponder_reply' => $autoresponder_reply,
                 'duplicate' => $duplicate,             
	             'form_pageid' => $form_pageid,
	             'confirmation_pageid' => $confirmation_pageid,	
                 ); 
 
         $extra_fields = array('additional_fields' => '');
         $submissions_sform_settings = array_merge($settings, apply_filters( 'sform_submissions_settings_filter', $extra_fields ));
         $additional_sform_settings = array_merge($submissions_sform_settings, apply_filters( 'sform_akismet_settings_filter', $extra_fields ));
         $extra_sform_settings = array_merge($additional_sform_settings, apply_filters( 'sform_recaptcha_settings', $extra_fields ));
         $update_result = update_option('sform_settings', $extra_sform_settings); 
                  
         global $wpdb; 
         $table = "{$wpdb->prefix}sform_shortcodes"; 
         $forms = $wpdb->get_col( "SELECT id FROM $table" );
         foreach($forms as $form) { 
	       if ( $form != '1' ) {
	         $settings_option = get_option('sform_'. $form .'_settings');
             $form_settings = $settings_option != false ? $settings_option : '';
	         if ( $form_settings != '' ) {
			 $form_settings['admin_notices'] = $admin_notices;
			 $form_settings['admin_limits'] = $admin_limits;
			 $form_settings['widget_editor'] = $widget_editor;
			 $form_settings['widget'] = $widget_options;
			 $form_settings['admin_color'] = $admin_color;
			 $form_settings['deletion_data'] = $uninstall;
             $form_settings['multiple_spaces'] = $multiple_spaces;
			 $form_settings['server_smtp'] = $server_smtp;
			 $form_settings['smtp_host'] = $smtp_host;
			 $form_settings['smtp_encryption'] = $smtp_encryption;
			 $form_settings['smtp_port'] = $smtp_port;
			 $form_settings['smtp_authentication'] = $smtp_authentication;
			 $form_settings['smtp_username'] = $smtp_username;
			 $form_settings['smtp_password'] = $smtp_password;
			 $form_settings['duplicate'] = $duplicate;
             update_option('sform_'. $form .'_settings', $form_settings); 
	         }
	       }
         }
      
	   }
      
       else {
	       
	     $admin_notices = ! empty($main_settings['admin_notices']) ? esc_attr($main_settings['admin_notices']) : 'false';
	     $admin_limits = ! empty($main_settings['admin_limits']) ? esc_attr($main_settings['admin_limits']) : 'false';
	     $widget_options = ! empty($main_settings['widget']) ? esc_attr($main_settings['widget']) : 'false';       
	     $widget_editor = ! empty($main_settings['widget_editor']) ? esc_attr($main_settings['widget_editor']) : 'false';       
	     $admin_color = ! empty($main_settings['admin_color']) ? esc_attr($main_settings['admin_color']) : 'default';       
	     $uninstall = ! empty($main_settings['deletion_data']) ? esc_attr($main_settings['deletion_data']) : 'false'; 
	     $multiple_spaces = ! empty($main_settings['multiple_spaces']) ? esc_attr($main_settings['multiple_spaces']) : 'false';
	     $server_smtp = ! empty($main_settings['server_smtp']) ? esc_attr($main_settings['server_smtp']) : 'false';       
	     $smtp_host = ! empty($main_settings['smtp_host']) ? esc_attr($main_settings['smtp_host']) : '';
	     $smtp_encryption = ! empty($main_settings['smtp_encryption']) ? esc_attr($main_settings['smtp_encryption']) : '';       
	     $smtp_port = ! empty($main_settings['smtp_port']) ? esc_attr($main_settings['smtp_port']) : '';       
	     $smtp_authentication = ! empty($main_settings['smtp_authentication']) ? esc_attr($main_settings['smtp_authentication']) : 'false';
	     $smtp_username = ! empty($main_settings['smtp_username']) ? esc_attr($main_settings['smtp_username']) : '';       
	     $smtp_password = ! empty($main_settings['smtp_password']) ? esc_attr($main_settings['smtp_password']) : '';       
	     $duplicate = ! empty($main_settings['duplicate']) ? esc_attr($main_settings['duplicate']) : 'false';       
	       
         $settings = array(
	             'form_pageid' => $form_pageid,
	             'confirmation_pageid' => $confirmation_pageid,	
	             'admin_notices' => $admin_notices,
	             'admin_limits' => $admin_limits,
                 'widget_editor' => $widget_editor,
                 'widget' => $widget_options,
                 'admin_color' => $admin_color,
	             'html5_validation' => $html5_validation,
	             'focus' => $focus,
                 'ajax_submission' => $ajax_submission,
                 'spinner' => $spinner,
                 'form_template' => $template,
                 'form_borders' => $form_borders,
                 'stylesheet' => $stylesheet,
                 'stylesheet_file' => $cssfile, 
                 'javascript' => $javascript,
                 'deletion_data' => $uninstall,
                 'multiple_spaces' => $multiple_spaces,
                 'outside_error' => $outside_error,
                 'characters_length' => $characters_length,
                 'empty_fields' => $empty_fields,
                 'empty_name' => $empty_name,
                 'empty_lastname' => $empty_lastname,
                 'empty_phone' => $empty_phone,
                 'empty_email' => $empty_email,
                 'empty_subject' => $empty_subject,
                 'empty_message' => $empty_message,
                 'empty_captcha' => $empty_captcha,
                 'incomplete_name' => $incomplete_name, 
                 'invalid_name' => $invalid_name, 
                 'name_error' => $name_error,      
                 'incomplete_lastname' => $incomplete_lastname, 
                 'invalid_lastname' => $invalid_lastname, 
                 'lastname_error' => $lastname_error,      
                 'invalid_email' => $invalid_email,  
                 'email_error' => $email_error,  
                 'invalid_phone' => $invalid_phone, 
                 'phone_error' => $phone_error,      
                 'incomplete_subject' => $incomplete_subject, 
                 'invalid_subject' => $invalid_subject,  
                 'subject_error' => $subject_error,                    
                 'incomplete_message' => $incomplete_message,    
                 'invalid_message' => $invalid_message,
                 'message_error' => $message_error,
                 'consent_error' => $consent_error,
                 'invalid_captcha' => $invalid_captcha,    
                 'captcha_error' => $captcha_error,    
                 'honeypot_error' => $honeypot_error,    
                 'server_error' => $server_error, 
                 'duplicate_error' => $duplicate_error,
                 'ajax_error' => $ajax_error,        
                 'success_action' => $success_action,         
                 'success_message' => $success_message, 
                 'confirmation_page' => $confirmation_page,        
                 'thanks_url' => $thanks_url,
                 'server_smtp' => $server_smtp,
                 'smtp_host' => $smtp_host,
                 'smtp_encryption' => $smtp_encryption,
                 'smtp_port' => $smtp_port,
                 'smtp_authentication' => $smtp_authentication,
                 'smtp_username' => $smtp_username,
                 'smtp_password' => $smtp_password,
                 'notification' => $notification,
                 'notification_recipient' => $notification_recipients,
                 'bcc' => $notification_bcc,
                 'notification_email' => $notification_email,
                 'notification_name' => $notification_name,
                 'custom_sender' => $custom_sender,
                 'notification_subject' => $notification_subject,
                 'custom_subject' => $custom_subject,
                 // 'notification_message' => $notification_message,
                 'notification_reply' => $notification_reply,
                 'submission_number' => $submission_number,  
                 'autoresponder' => $autoresponder, 
                 'autoresponder_email' => $autoresponder_email,
                 'autoresponder_name' => $autoresponder_name,
                 'autoresponder_subject' => $autoresponder_subject,
                 'autoresponder_message' => $autoresponder_message,
                 'autoresponder_reply' => $autoresponder_reply,
                 'duplicate' => $duplicate,             
                 ); 
                 
         $extra_fields = array('additional_fields' => '');
         $submissions_sform_settings = array_merge($settings, apply_filters( 'sform_submissions_settings_filter', $extra_fields ));
         $additional_sform_settings = array_merge($submissions_sform_settings, apply_filters( 'sform_akismet_settings_filter', $extra_fields ));
         $extra_sform_settings = array_merge($additional_sform_settings, apply_filters( 'sform_recaptcha_settings', $extra_fields ));
         $update_result = update_option("sform_{$form_id}_settings", $extra_sform_settings); 
     
	   }
       
       if ( $update_result ) {
	       
	     if ( $widget_editor == 'true' ):
         global $wpdb; 
         $table_name = "{$wpdb->prefix}sform_shortcodes"; 
         $widget_forms = $wpdb->get_results( "SELECT id, widget FROM $table_name WHERE area != 'page' AND area != 'draft'", 'ARRAY_A' );
         $shortcodes_ids = array_column($widget_forms, 'id');
         $widget_ids = array_column($widget_forms, 'widget');
         $sidebars_widgets = get_option('sidebars_widgets');

         if ( $widget_ids ) {
           foreach($widget_ids as $widget_id) {
           $sform_widget = 'sform_widget-' . $widget_id;	
           $sform_widget_array = array($sform_widget);
           foreach ( $sidebars_widgets as $sidebar => $widgets ) {
	       if ( is_array( $widgets ) && in_array($sform_widget, $widgets)) {
		   $sidebars_widgets[$sidebar] = array_diff($widgets,$sform_widget_array);
           update_option( 'sidebars_widgets', $sidebars_widgets );
           }
           }
           }
           $simpleform_widgets = array();  
           update_option( 'widget_sform_widget', $simpleform_widgets );
         }

         if ( $shortcodes_ids ) {
           foreach($shortcodes_ids as $shortcode_id) {
           $wpdb->update($table_name, array('area' => 'page', 'widget' => '0'), array('id' => $shortcode_id ));
           }
         }
	     endif;   
	       
	     echo json_encode( array( 'error' => false, 'update' => true, 'message' => __( 'Settings were successfully saved', 'simpleform' ) ) ); 
	     exit; 
       }
      
       else {
	     echo json_encode( array( 'error' => false, 'update' => false, 'message' => __( 'Settings have already been saved', 'simpleform' ) ) );
	     exit; 	   
       }
  	         
      die();
      
      }

    }  
    
	/**
	 * Return shortcode properties
	 *
	 * @since    1.0
	 */
	
    public function sform_form_filter($attribute) { 
		
     global $wpdb;
     $table_name = $wpdb->prefix . 'sform_shortcodes';
     
     if ($attribute == '') {
     $form_values = $wpdb->get_row( "SELECT * FROM {$table_name}", ARRAY_A );  
     }
     else {
     $form_values = $wpdb->get_row( "SELECT * FROM {$table_name} WHERE shortcode = '{$attribute}'", ARRAY_A );  
     }
     
     return $form_values;
     
    } 

    /**
     * Deleting the table whenever a single site into a network is deleted.
     *
     * @since    1.2
     */

    public function on_delete_blog($tables) {
      
      global $wpdb;
      $tables[] = $wpdb->prefix . 'sform_submissions';
      return $tables;
			
    }
    
	/**
	 * Add the link to the Privacy Policy page in the consent label.
	 *
	 * @since    1.9.2
	 */
	
    public function setting_privacy() {

      if( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {	die ( 'Security checked!'); }
      if ( ! wp_verify_nonce( $_POST['verification_nonce'], "ajax-verification-nonce")) { exit("Security checked!"); }   
      if ( ! current_user_can('manage_options')) { exit("Security checked!"); }   
      else { 
        $page = isset($_POST['page-id']) ? absint($_POST['page-id']) : 0;   
        $privacy_label = isset($_POST['consent-label']) ? wp_kses_post(trim($_POST['consent-label'])): ''; 
        /* translators: Used within the string "I have read and consent to the %s". It can be replaced with the hyperlink to the privacy policy page */       
        $privacy_string = __( 'privacy policy','simpleform');
        if ( $page > 0 ) {
    	   $link = '<a href="' . get_page_link($page) . '" target="_blank">' . $privacy_string . '</a>';
    	   $url = get_page_link($page);
	       // If the consent label still contains the original string
	       if (  strpos($privacy_label, $privacy_string) !== false ) { 
              // Check if a link to privacy policy page already exists, and remove it:
              $pattern = '/<a [^>]*>'.$privacy_string.'<\/a>/i';              
              if( preg_match($pattern,$privacy_label) ) {
	          $label = preg_replace($pattern,$link,html_entity_decode($privacy_label));
              } else {
              // If a link to privacy policy page not exists:
	          $label = str_replace($privacy_string,$link,html_entity_decode($privacy_label));	    
              }
              echo json_encode( array( 'error' => false, 'label' => $label, 'url' => $url ) );
	          exit;
           } 
           // If the consent label not contains the original string
           else {
              /* translators: %s: privacy policy, it can contain the hyperlink to the page */
    	      $label = sprintf( __( 'I have read and consent to the %s', 'simpleform' ), $link );	
              echo json_encode( array( 'error' => false, 'label' => $label, 'url' => $url ) );
	          exit;
           }
        }   
        else {
           echo json_encode( array( 'error' => false ));
	       exit;
        }
        die();
      }

    }
    
	/**
	 * Return an update message if there's a release waiting
	 *
	 * @since    1.9.2
	 */
	
    public function update_message() { 
		
     $updates = (array) get_option( '_site_transient_update_plugins' );
     if ( isset( $updates['response'] ) && array_key_exists( SIMPLEFORM_BASENAME, $updates['response'] ) ) {
            $update_message = '<span class="admin-notices update"><a href="'.self_admin_url('plugins.php').'" target="_blank">'. __('There is a new version of SimpleForm available. Get the latest features and improvements!', 'simpleform') .'</a></span>';
	 } 
	 else { $update_message = ''; } 
	 	 
     return $update_message;
     
    } 
    	
    /**
	 * Add support links in the plugin meta row
	 *
	 * @since    1.10
	 */
	
    public function plugin_meta( $plugin_meta, $file ) {

     /* translators: %1$s: native language name, %2$s: URL to translate.wordpress.org */
      $message = __('SimpleForm is not translated into %1$s yet. <a href="%2$s">Help translate it!</a>', 'simpleform' );
      $translation_message = __('Help improve the translation', 'simpleform' );
      $claim = __('Contact form made simple', 'simpleform' );
      $claim2 = __('Amazingly easy, surprisingly powerful', 'simpleform' );

      if ( strpos( $file, SIMPLEFORM_BASENAME ) !== false ) {
		$plugin_meta[] = '<a href="https://wordpress.org/support/plugin/simpleform/" target="_blank">'.__('Support', 'simpleform').'</a>';
		}
		
	  return $plugin_meta;

	}
	
    /**
	 * Display additional action links in the plugins list table  
	 *
	 * @since    1.10
	 */
	
    public function plugin_links( $plugin_actions, $plugin_file ){ 
    
      $new_actions = array();
	  if ( SIMPLEFORM_BASENAME === $plugin_file ) { 
		
		if ( is_multisite() ) {   
		  $url = network_admin_url('plugin-install.php?tab=search&type=tag&s=simpleform-addon');
		} else {
		  $url = admin_url('plugin-install.php?tab=search&type=tag&s=simpleform-addon');
		} 
		  
      $new_actions['sform_settings'] = '<a href="' . menu_page_url( 'sform-submissions', false ) . '">' . __('Dashboard', 'simpleform') . '</a> | <a href="' . menu_page_url( 'sform-editor', false ) . '">' . __('Editor', 'simpleform') . '</a> | <a href="' . menu_page_url( 'sform-settings', false ) . '">' . __('Settings', 'simpleform') . '</a> | <a href="'.$url.'" target="_blank">' . __('Addons', 'simpleform') . '</a>';
  	  }
     
      return array_merge( $new_actions, $plugin_actions );

    }

	/**
	 * Fallback for database table updating if plugin is already active.
	 *
	 * @since    1.10
	 */
    
    public function db_version_check() {
    
        $current_db_version = SIMPLEFORM_DB_VERSION; 
        $installed_version = get_option('sform_db_version');
    
        if ( $installed_version != $current_db_version ) {
          
          require_once SIMPLEFORM_PATH . 'includes/class-activator.php';
	      SimpleForm_Activator::create_db();
          
        }
                
    }
    
	/**
	 * Update pages list containing simpleform when a page is edited.
     *
	 * @since    2.0.5
	 */
	
	public function sform_pages_list( $post_id, $post ) {

      // Return if this is just a revision
      if ( wp_is_post_revision( $post_id ) ) {
        return;
      }

      $id = array($post_id);
      $util = new SimpleForm_Util();      
      // List of all forms IDs that have been created
      $form_ids = $util->sform_ids();
      // Retrieve all forms IDs used in the post content
      $used_forms = $util->used_forms($post->post_content,$type = 'all');
      // Retrieve all forms IDs used as shortcode in the post content
      $used_shortcodes = $util->used_forms($post->post_content,$type = 'shortcode');
      global $wpdb;
      $table_name = "{$wpdb->prefix}sform_shortcodes";
      // Retrieve the option with all pages that use simpleform
      $sform_pages = get_option('sform_pages') != false ? get_option('sform_pages') : $util->form_pages($form_id = '0');     

      // If the post content contains simpleform
      if ( ! empty($used_forms) ) {
	      
        if ( !in_array($post_id,$sform_pages) ) {
	       $updated_sform_pages = array_unique(array_merge($id,$sform_pages)); 
	       update_option('sform_pages',$updated_sform_pages);
        }

        // List of all forms IDs used in the post content
        foreach ($form_ids as $form_id) {	       
           
           $shortcode_used_in = $wpdb->get_var( "SELECT shortcode_pages FROM $table_name WHERE id = {$form_id}" );
           $shortcode_ids = ! empty($shortcode_used_in) ? explode(',', $shortcode_used_in) : array();           
           $block_used_in = $wpdb->get_var( "SELECT block_pages FROM $table_name WHERE id = {$form_id}" );
           $block_ids = ! empty($block_used_in) ? explode(',',$block_used_in) : array();
          
           // If a form ID is among those used in the post
           if ( in_array($form_id,$used_forms) ) {
	           
             if ( in_array($form_id,$used_shortcodes) ) {	           
             // Include the post ID in the shortcodes list if not yet inserted
               if ( !in_array($post_id,$shortcode_ids) ) {
                 // Turn it as string and update the value
                 $new_used_in = implode(',', array_unique(array_merge($id,$shortcode_ids))); 
                 $wpdb->query( $wpdb->prepare("UPDATE $table_name SET shortcode_pages = '$new_used_in' WHERE id = %d", $form_id) );
	           }
               // Exclude the post ID from the blocks list if inserted
               if ( in_array($post_id,$block_ids) ) {
                 $updated_used_in = array_diff($block_ids,$id);
                 $new_used_in = implode(",", $updated_used_in); 
                 $wpdb->query( $wpdb->prepare("UPDATE $table_name SET block_pages = '$new_used_in' WHERE id = %d", $form_id) );
	           }
	         }
	         
	         else {
               // Include the post ID in the blocks list if not yet inserted
               if ( ! in_array($post_id,$block_ids) ) {
	              $new_used_in = implode(',', array_unique(array_merge($id,$block_ids)));
                  $wpdb->update($table_name, array('block_pages' => $new_used_in), array('id' => $form_id ));
	           }
               // Exclude the post ID from the shortcodes list if inserted
               if ( in_array($post_id,$shortcode_ids) ) {
                 $updated_used_in = array_diff($shortcode_ids,$id);
                 $new_used_in = implode(",", $updated_used_in); 
                 $wpdb->query( $wpdb->prepare("UPDATE $table_name SET shortcode_pages = '$new_used_in' WHERE id = %d", $form_id) );
	           }
	         }
	         
	       }	

           // If a form ID is not among those used
		   else {
             // Exclude the post ID from all the lists
             if ( in_array($post_id,$shortcode_ids) ) {
               $updated_used_in = array_diff($shortcode_ids,$id);
               $new_used_in = implode(",", $updated_used_in); 
               $wpdb->query( $wpdb->prepare("UPDATE $table_name SET shortcode_pages = '$new_used_in' WHERE id = %d", $form_id) );
	         }
             if ( in_array($post_id,$block_ids) ) {
               $updated_used_in = array_diff($block_ids,$id);
               $new_used_in = implode(",", $updated_used_in); 
               $wpdb->query( $wpdb->prepare("UPDATE $table_name SET block_pages = '$new_used_in' WHERE id = %d", $form_id) );
	         }
	           
	       }
	       	
        }
         
      }
      
      // If the post content does not contains simpleform
      else { 
	      
        if ( in_array($post_id,$sform_pages) ) {
	       $updated_sform_pages = array_diff($sform_pages,$id); 
	       update_option('sform_pages',$updated_sform_pages);
        }
        
        foreach ($form_ids as $form_id) {
          $shortcode_used_in = $wpdb->get_var( "SELECT shortcode_pages FROM $table_name WHERE id = {$form_id}" );
          $shortcode_ids = ! empty($shortcode_used_in) ? explode(',', $shortcode_used_in) : array();           
          $block_used_in = $wpdb->get_var( "SELECT block_pages FROM $table_name WHERE id = {$form_id}" );
          $block_ids = ! empty($block_used_in) ? explode(',',$block_used_in) : array();
          if ( in_array($post_id,$shortcode_ids) ) {
            $updated_used_in = array_diff($shortcode_ids,$id);
            $new_used_in = implode(",", $updated_used_in); 
            $wpdb->query( $wpdb->prepare("UPDATE $table_name SET shortcode_pages = '$new_used_in' WHERE id = %d", $form_id) );
	      }
          if ( in_array($post_id,$block_ids) ) {
            $updated_used_in = array_diff($block_ids,$id);
            $new_used_in = implode(",", $updated_used_in); 
            $wpdb->query( $wpdb->prepare("UPDATE $table_name SET block_pages = '$new_used_in' WHERE id = %d", $form_id) );
	      }
 	    }  

      }
                
    }
            
	/**
	 * Clean up the post content of any non-existent and redundant form prior to saving it in the database
	 *
	 * @since    2.0.2
	 */
	/*
    public function clean_up_post_content($content) {
          
       $util = new SimpleForm_Util();
       $form_ids = $util->sform_ids();
       
       $used_forms = $util->used_forms($content, $type = 'all');
       // when saved id has slash, ex: "\2\"
       //  update_option('sform_used_forms',$used_forms);
       // Return the duplicate shortcodes
       $duplicates = array_unique(array_diff_key($used_forms,array_unique($used_forms)));
       // Search all simpleform blocks and place all matches in an array 
       $search_block = '/<!-- wp:simpleform(.*)\/-->/';
       preg_match_all($search_block, $content, $matches_block);     
       // Search all shortcode blocks and place all matches in an array 
	   $search_shortcode = '/<!-- wp:shortcode([^>]*)-->(.*?)<!-- \/wp:shortcode -->/s';
       preg_match_all($search_shortcode, $content, $matches_shortcode);

       if ( !empty($matches_block) ) {
		 foreach ( $matches_block[0] as $block ) {
           // Remove blocks that cannot be displayed
		   if ( strpos($block, '\"formDisplay\":false') !== false ) {
              $content = str_replace($block, '', $content);
           }
         }
       }
       if ( !empty($duplicates) ) {
         foreach ($duplicates as $duplicate ) {
	       $find = stripslashes($duplicate) != '1' ? '[simpleform id=\"'.stripslashes($duplicate).'\"]' : '[simpleform]';
             // If at least one shortcode block exists
             if ( !empty($matches_shortcode) ) {
		       foreach ( $matches_shortcode[0] as $shortcode ) {
	             // Check if the shortcode block is simpleform and it's using one of duplicate IDs
		         if ( strpos($shortcode,$find) !== false ) {
			       $splitted_content = explode($shortcode, $content, 2);
				   // Verify if there is also a classic shortcode and if it has been used before
		           if ( strpos($splitted_content[0],$find) !== false ) { 
			          $new_splitted_content = explode($find, $content, 2);
			          // Remove all subsequent blocks
			          $content = $new_splitted_content[0] . $find . str_replace($find, '', str_replace($shortcode, '', $new_splitted_content[1]));
		           }
		           else {
			       // Keep the first block and remove all subsequent blocks and all classic shortcodes where these exist 
			       $content = $splitted_content[0] . $shortcode . str_replace($find, '', str_replace($shortcode, '', $splitted_content[1]));
		           }
                  // break;
                 }
               }
             }
             // Verify if at least one classic shortcode exists
		       if ( strpos($content,$find) !== false ) {
			      $splitted_content = explode($find, $content, 2);
			      // Keep the first and remove all subsequent
			      $content = $splitted_content[0] . $find . str_replace($find, '', $splitted_content[1]);
                  // break;
               }
         }
       }
       // In the absence of duplicates remove shortcode that is using the same form id inserted after the block
       else {
	     if ( !empty($matches_block) ) { 		     
		   foreach ( $matches_block[0] as $block ) {
		     if ( strpos($block, '\"formDisplay\":true') !== false ) {
               $split_block = isset(explode('\"formId\":\"', $block)[1]) ? explode('\"formId\":\"', $block)[1] : '';
               $form_id = ! empty($split_block) ? explode('\"', $split_block)[0] : '';
               $find = $form_id != '1' ? '[simpleform id=\"'.$form_id.'\"]' : '[simpleform]';               
               // Remove any shortcode block that is using the same form id
               if ( !empty($matches_shortcode) ) {
		         foreach ( $matches_shortcode[0] as $shortcode ) {
		           if ( strpos($shortcode,$find) !== false ) {
                      $content = str_replace($shortcode, '', $content);
                      break;
                   }
                 }
               }
               // Remove any classic shortcode that is using the same form id
               $content = str_replace($find, '', $content);
             }
           }
         }
       }
       
       // Remove any shortcode using an inesistent form id
       if ( !empty($used_forms) ) {
       foreach ($used_forms as $shortcode_id) {       
          if ( ! in_array(stripslashes($shortcode_id),$form_ids) ) {    
	  	    $find = '[simpleform id=\"'.stripslashes($shortcode_id).'\"]';
            // Search all shortcode blocks and place all matches in an array 
	        $search_shortcode = '/<!-- wp:shortcode([^>]*)-->(.*?)<!-- \/wp:shortcode -->/s';
            preg_match_all($search_shortcode, $content, $matches_shortcode);
            // If at least one shortcode block exists
            if ( !empty($matches_shortcode) ) {
		      foreach ( $matches_shortcode[0] as $shortcode ) {
	            // Check if the shortcode block is simpleform and it's using an inesistent form id
		        if ( strpos($shortcode,$find) !== false ) {
			      // Remove all inesistent blocks
	    	      $content = str_replace($shortcode, '', $content);
                  break;
                }
              }
            }
            // Remove all classic shortcodes using an inesistent form id
            $content = str_replace( $find,'', $content );
	      }
       }
       }
       
       // Remove empty shortcodes
       $empty_shortcode_blocks = '<!-- wp:shortcode /-->';
       $content = str_replace($empty_shortcode_blocks,'',$content);
       
       return $content; 
  
    }
    */
	/**
	 * Change Admin Color Scheme.
	 *
	 * @since    2.0
	 */
	
    public function admin_color_scheme() {

      if( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {	die ( 'Security checked!'); }
      if ( ! wp_verify_nonce( $_POST['verification_nonce'], "ajax-verification-nonce")) { exit("Security checked!"); }   
      if ( ! current_user_can('manage_options')) { exit("Security checked!"); }   
      
      else { 
        $admin_color = isset($_POST['admin-color']) && in_array($_POST['admin-color'], array('default', 'light', 'modern', 'blue', 'coffee', 'ectoplasm', 'midnight', 'ocean', 'sunrise', 'foggy', 'polar' )) ? sanitize_text_field($_POST['admin-color']) : '';
        if ( !empty($admin_color) ) {
	       $main_settings = get_option('sform_settings');
	       $main_settings['admin_color'] = $admin_color;
           $update = update_option('sform_settings', $main_settings);                       
	       if ( $update ) {
              global $wpdb;
              $shortcodes_table = $wpdb->prefix . 'sform_shortcodes';
              $ids = $wpdb->get_col("SELECT id FROM `$shortcodes_table` WHERE id != '1'");	
              if ( $ids ) {
	           foreach ( $ids as $id ) {
	             $form_settings = get_option('sform_'.$id.'_settings');
                 if ( $form_settings != false ) {
	             $form_settings['admin_color'] = $admin_color;
                 update_option('sform_'.$id.'_settings', $form_settings); 
                 }
               }
              }
              echo json_encode( array( 'error' => false, 'color' => $admin_color ) );
	          exit;
           } 
           else {
              echo json_encode( array( 'error' => true ) );
	          exit;
           }
        } 
        else {
           echo json_encode( array( 'error' => true ));
	       exit;
        }
        die();
      }

    }
    
	/**
	 * Display an admin notice in case there are any SimpleForm widgets running on WordPress 5.8.
	 *
	 * @since    2.0.3
	 */
	
    public function general_admin_notice($hook){
	
      global $pagenow;
    
      if ( $pagenow == 'widgets.php' ) {
	    
        $settings = get_option("sform_settings");
        $widget_editor = ! empty( $settings['widget_editor'] ) ? esc_attr($settings['widget_editor']) : 'false';
        $sidebars_widgets = get_option('sidebars_widgets');
        $simpleform_widgets = '';
        foreach ( $sidebars_widgets as $sidebar => $widgets ) {
	      if ( is_array( $widgets ) ) {
		    foreach ( $widgets as $key => $widget_id ) {
			  if ( strpos($widget_id, 'sform_widget-' ) !== false ) {
			    $simpleform_widgets .= '1';
              }
            }
          }
        }

        if ( ! empty($simpleform_widgets) && $widget_editor == 'false' ) {
          echo '<div class="notice notice-warning is-dismissible"><p>'. __( 'To maintain the best site editing experience for you, SimpleForm, the plugin you are using, has disabled the widget screen introduced in WordPress 5.8.', 'simpleform' ) . ' ' . __('To use the new widgets editor, you have to check the related option.', 'simpleform' ) . ' ' . __('Navigate to Contacts > Settings page. You will find the <b>"Widgets Block Editor"</b> option in the management preferences section within the general tab.', 'simpleform' ) . ' ' .  __('By checking this option, all SimpleForm widgets used previously will be deleted. You can continue using the contact form as a widget, but you’ll have to manually insert it in widget areas as a block. You will not be able to choose where to display it by using the "Show/Hide on" and the "Selected pages" options.', 'simpleform' ) . '</p></div>';
        }
 
      }
   
    }
    
	/**
	 * Delete form.
	 *
	 * @since    2.0.4
	 */
	
    public function sform_delete_form() {

      if( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {	die ( 'Security checked!'); }
      if ( ! wp_verify_nonce( $_POST['sform_nonce'], "sform_nonce_deletion")) { exit("Security checked!"); }   
      if ( ! current_user_can('manage_options')) { exit("Security checked!"); }   
      
      else {
        $form_id = isset( $_POST['form-id'] ) ? absint($_POST['form-id']) : '1';
        global $wpdb; 
        $table = "{$wpdb->prefix}sform_shortcodes";
        $submission_table = "{$wpdb->prefix}sform_submissions"; 
        $table_post = $wpdb->prefix . 'posts';
	    $pages_list = $wpdb->get_var( "SELECT pages FROM {$table} WHERE id = {$form_id}" );
	    $shortcode_pages_list = $wpdb->get_var( "SELECT shortcode_pages FROM {$table} WHERE id = {$form_id}" );
	    $block_pages_list = $wpdb->get_var( "SELECT block_pages FROM {$table} WHERE id = {$form_id}" );
	    $widget = $wpdb->get_var( "SELECT widget FROM {$table} WHERE id = {$form_id} AND widget != 0" );
	    $widget_id = $wpdb->get_var( "SELECT widget_id FROM {$table} WHERE id = {$form_id}" );
    	$pages = $pages_list ? explode(',',$pages_list) : array();
  	    $shortcode_pages = $shortcode_pages_list ? explode(',',$shortcode_pages_list) : array();
	    $block_pages = $block_pages_list ? explode(',',$block_pages_list) : array();
        $form_pages = array_unique(array_merge($shortcode_pages,$block_pages,$pages));
        $deletion = $wpdb->query($wpdb->prepare("DELETE FROM {$table} WHERE id = '%d'", $form_id));
       
        if ( $deletion ) {
	      $post_cleaning = ''; 
	      $wpdb->delete( $submission_table, array( 'form' => $form_id ) );
          $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name = 'sform_{$form_id}_settings' OR option_name = 'sform_{$form_id}_attributes' OR option_name = '_transient_sform_last_{$form_id}_message'" );
          if ( $form_pages ) {
            foreach ($form_pages as $postID) {
	          $post = get_post($postID);
	          $content = $post->post_content;
	          $search_shortcode = $form_id != '1' ? '[simpleform id="'.$form_id.'"]' : '[simpleform]';
	          if ( has_blocks($content) ) {
               $plugin_block = '/<!-- wp:simpleform(.*)\/-->/';
               preg_match_all($plugin_block, $content, $matches_block);     
               if ( $matches_block ) {
      		    foreach ( $matches_block[0] as $block ) {
       		      if ( strpos($block, '"formId":"'.$form_id.'"') !== false ) {
                     $content = str_replace($block, '', $content);
                  }
                }
               }
	           $shortcode_block = '/<!-- wp:shortcode([^>]*)-->(.*?)<!-- \/wp:shortcode -->/s';
               preg_match_all($shortcode_block, $content, $matches_shortcode);
               if ( $matches_shortcode ) {
		        foreach ( $matches_shortcode[0] as $shortcode ) {
		          if ( strpos($shortcode,$search_shortcode) !== false ) { 
	    	        $content = str_replace($shortcode, '', $content);
                    break;
                  }
                }
               }
              }
              // Remove shortcode not included in a block
		      if ( strpos($content, $search_shortcode) !== false ) {
                $content = str_replace($search_shortcode, '', $content);
              } 
              $cleaning = $wpdb->update( $table_post, array( 'post_content' => $content ), array( 'ID' => $postID ) );
              if ( $cleaning ) { $post_cleaning .= 'done'; }
            }  
          }
          
          if ( $widget ) { 
            $sform_widget = get_option('widget_sform_widget');         
            unset($sform_widget[$widget]);
            update_option('widget_sform_widget', $sform_widget);
            $sidebars_widgets = get_option('sidebars_widgets');
            foreach ( $sidebars_widgets as $sidebar => $widgets ) {
	          if ( is_array( $widgets ) ) {
		        foreach ( $widgets as $index => $widget_id ) {
			      if ( $widget_id == 'sform_widget-'.$widget ) {
                    unset($sidebars_widgets[$sidebar][$index]);
                    update_option('sidebars_widgets', $sidebars_widgets);
                  }
                }
              }
            }
          }
          
          if ( $widget_id ) { 
            $widget_block = get_option('widget_block');         
            if ( !empty($widget_block) ) {
              foreach ($widget_block as $key => $value ) {
                if ( is_array($value) ) {   
	               $string = implode('',$value);
                   if ( strpos($string, 'wp:simpleform/form-selector' ) !== false ) { 
	                  $split_id = ! empty($string) ? explode('formId":"', $string) : '';
	                  $id = isset($split_id[1]) ? explode('"', $split_id[1])[0] : '';
	                  if ( $id == $form_id ) {
			            unset($widget_block[$key]);
			            update_option('widget_block', $widget_block);
                      }
                   }
                   if ( ( strpos($string,'wp:shortcode') && strpos($string,'[simpleform') ) !== false ) { 
	                 $split_shortcode = ! empty($string) ? explode('[simpleform', $string) : '';
	                 $split_id = isset($split_shortcode[1]) ? explode(']', $split_shortcode[1])[0] : '';
	                 $id = empty($split_id) ? '1' : filter_var($split_id, FILTER_SANITIZE_NUMBER_INT);
	                 if ( $id == $form_id ) {
			           unset($widget_block[$key]);
			           update_option('widget_block', $widget_block);
	                 }
                   }
                }
              }
            }
            $sidebars_widgets = get_option('sidebars_widgets');
            foreach ( $sidebars_widgets as $sidebar => $widgets ) {
	          if ( is_array( $widgets ) ) {
		        foreach ( $widgets as $index => $id ) {
			      if ( $id == $widget_id ) {
                    unset($sidebars_widgets[$sidebar][$index]);
                    update_option('sidebars_widgets', $sidebars_widgets);
                  }
                }
              }
            }
          }
	        
          if ( ! empty($post_cleaning) ) {
	        $message = sprintf( __( 'Form with ID: %s permanently deleted', 'simpleform' ), $form_id ) . '.&nbsp;' . __( 'All the pages containing the form have been cleaned up', 'simpleform' );
	        echo json_encode(array('error' => false, 'message' => $message, 'redirect_url' => admin_url('admin.php?page=sform-submissions') ));
	        exit;
	      }
	      else {
	        echo json_encode(array('error' => false, 'message' => sprintf( __( 'Form with ID: %s permanently deleted', 'simpleform' ), $form_id ), 'redirect_url' => admin_url('admin.php?page=sform-submissions') ));
	        exit;
	      }

        }
        else {
	        echo json_encode(array('error' => true, 'message' => __( 'Error occurred deleting the form. Try again!', 'simpleform' ) ));
	        exit; 
        }
        die();
      }

    }  
    
	/**
	 * Setup function that registers the screen option.
	 *
	 * @since    1.0
	 * /

    public function forms_list_options() {
	    
      global $sform_forms;
      $screen = get_current_screen();      
           
      if(!is_object($screen) || $screen->id != $sform_forms)
      return;
      $option = 'per_page';
      $args = array( 'label' => esc_attr__('Number of forms per page', 'simpleform'),'default' => 10,'option' => 'edit_form_per_page');
      
      add_screen_option( $option, $args );
      $table = new SimpleForm_Forms_List(); 
        
    }

	/**
	 * Save screen options.
	 *
	 * @since    2.1
	 * /

    public function forms_screen_option($status, $option, $value) {
      
     if ( 'edit_form_per_page' == $option ) return $value;
     return $status;
    
    }
    
	/**
	 * Register a post type for change the pagination in Screen Options tab.
	 *
	 * @since    2.1
	 * /

    public function form_post_type() {
	
	    $args = array();
	    register_post_type( 'form', $args );
	    
    }
    
     /**
	 * Show the parent menu active for hidden sub-menu item
	 *
	 * @since    2.1
	 * /
	
    public function contacts_menu_open($parent_file) {

      global $plugin_page;

      if ( $plugin_page === 'sform-form' ) {
        $plugin_page = 'sform-forms';
      } 
    
      return $parent_file;
      
    }
    */	
       
}