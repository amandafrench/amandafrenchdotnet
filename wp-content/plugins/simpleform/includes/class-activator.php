<?php

/**
 * The class instantiated during the plugin activation.
 *
 * @since      1.0
 */
 
class SimpleForm_Activator {

	/**
     * Run default functionality during plugin activation.
     *
     * @since    1.0
     */

    public static function activate($network_wide) {
	    
    if ( function_exists('is_multisite') && is_multisite() ) {
	  if($network_wide) {
        global $wpdb;
        $blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

        foreach ( $blog_ids as $blog_id ) {
         switch_to_blog( $blog_id );
         self::create_db();
         self::default_data_entry();
         self::sform_settings();
         self::sform_fields();         
         restore_current_blog();
        }
      } else {
         self::create_db();
    	 self::default_data_entry();
         self::sform_settings();
         self::sform_fields();
      }
    } else {
        self::create_db();
    	self::default_data_entry();
        self::sform_settings();
        self::sform_fields();
    }
    
    }

    /**
     * Create custom tables.
     *
     * @since    1.0
     */
 
    public static function create_db() {

        $current_db_version = SIMPLEFORM_DB_VERSION; 

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $installed_version = get_option('sform_db_version');
        $prefix = $wpdb->prefix;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        if ( $installed_version != $current_db_version ) {
        
          $shortcodes_table = $prefix . 'sform_shortcodes';
          $sql = "CREATE TABLE " . $shortcodes_table . " (
            id int(11) NOT NULL AUTO_INCREMENT,
            shortcode tinytext NOT NULL,
            area varchar(250) NOT NULL DEFAULT 'page',
            name tinytext NOT NULL,
            shortcode_pages text NOT NULL,
            block_pages text NOT NULL,
            widget_id text NOT NULL,
            widget smallint(5) UNSIGNED NOT NULL DEFAULT 0,
            target tinytext NOT NULL,
            creation datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id) 
          ) ". $charset_collate .";";
          dbDelta($sql);

          $submissions_table = $prefix . 'sform_submissions';
          $sql = "CREATE TABLE " . $submissions_table . " (
            id int(11) NOT NULL AUTO_INCREMENT,
            form int(7) NOT NULL DEFAULT 1,
            requester_type tinytext NOT NULL,
            requester_id int(15) NOT NULL,
            date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            notes text NOT NULL,
            PRIMARY KEY  (id)
          ) ". $charset_collate .";";
          dbDelta($sql);
          
          update_option('sform_db_version', $current_db_version);
          
        }
   
    }
    /**
     * Save default properties.
     *
     * @since    1.0
     */

    public static function default_data_entry() {
	  
        global $wpdb;
        $prefix = $wpdb->prefix;
        $shortcodes_table = $prefix . 'sform_shortcodes';
        $shortcode = 'simpleform';
        $name = __( 'Contact Us Page','simpleform');
        $shortcode_data = $wpdb->get_results("SELECT * FROM {$shortcodes_table}");
        if(count($shortcode_data) == 0) { $wpdb->insert( $shortcodes_table, array( 'shortcode' => $shortcode, 'name' => $name ) ); }
	    	    
    }
    
    /**
     *  Create a table whenever a new blog is created in a WordPress Multisite installation.
     *
     * @since    1.2
     */

    public static function on_create_blog($params) {
       
       if ( is_plugin_active_for_network( 'simpleform/simpleform.php' ) ) {
       switch_to_blog( $params->blog_id );
       self::create_db();
       self::default_data_entry();
       restore_current_blog();
       }

    }
    
    /**
     *  Specify the initial settings.
     *
     * @since    1.8.4
     */

    public static function sform_settings() {
       
 	   $settings = get_option( 'sform_settings' );
       
       if ( !$settings ) {
	       
       $form_page = array( 'post_type' => 'page', 'post_content' => '[simpleform]', 'post_title' => __( 'Contact Us', 'simpleform' ), 'post_status' => 'draft' );
       $thank_string1 = __( 'Thank you for contacting us.', 'simpleform' );
       $thank_string2 = __( 'Your message will be reviewed soon, and we\'ll get back to you as quickly as possible.', 'simpleform' );
       $confirmation_img = SIMPLEFORM_URL . 'public/img/confirmation.png';
       $thank_you_message = '<div style="text-align:center;padding-top:50px;"><h4>' . $thank_string1 . '</h4><br>' . $thank_string2 . '</br><img src="'.$confirmation_img.'" alt="message received" style="margin:30px auto;width:300px;"></div>'; 
       $confirmation_page = array( 'post_type' => 'page', 'post_content' => $thank_you_message, 'post_title' => __( 'Thanks!', 'simpleform' ), 'post_status' => 'draft' );
       $form_page_ID = wp_insert_post ($form_page);
       $confirmation_page_ID = wp_insert_post ($confirmation_page);
       $admin_email = get_option( 'admin_email' );
       $website_name = get_bloginfo( 'name' );
       $code_name = '[name]';
       $autoresponder_message = sprintf(__( 'Hi %s', 'simpleform' ),$code_name) . ',<p>' . __( 'We have received your request. It will be reviewed soon and we\'ll get back to you as quickly as possible.', 'simpleform' ) . '<p>' . __( 'Thanks,', 'simpleform' ) . '<br>' . __( 'The Support Team', 'simpleform' );          

       $settings = array(
	             'admin_notices' => 'false',
                 'admin_limits' => 'false',
                 'widget' => 'true',
                 'admin_color' => 'default',
                 'ajax_submission' => 'false',
                 'spinner' => 'false',
	             'html5_validation' => 'false',
	             'focus' => 'field',
                 'form_template' => 'default',
                 'form_borders' => 'dark',
                 'stylesheet' => 'false',
                 'stylesheet_file' => 'false', 
                 'javascript' => 'false',
                 'deletion_data' => 'false',
                 'multiple_spaces' => 'false',
                 'outside_error' => 'bottom',
                 'empty_fields' => __( 'There were some errors that need to be fixed', 'simpleform' ),
                 'characters_length' => 'false',
                 'empty_name' => __( 'Please provide your name', 'simpleform' ),
                 'incomplete_name' => __( 'Please type your full name', 'simpleform' ), 
                 'invalid_name' => __( 'The name contains invalid characters', 'simpleform' ), 
                 'name_error' => __( 'Error occurred validating the name', 'simpleform' ),                       
                 'empty_lastname' => __( 'Please provide your last name', 'simpleform' ),                 
                 'incomplete_lastname' => __( 'Please type your full last name', 'simpleform' ),                  
                 'invalid_lastname' => __( 'The last name contains invalid characters', 'simpleform' ), 
                 'lastname_error' => __( 'Error occurred validating the last name', 'simpleform' ),   
                 'empty_email' => __( 'Please provide your email address', 'simpleform' ),
                 'invalid_email' => __( 'Please enter a valid email', 'simpleform' ),  
                 'email_error' => __( 'Error occurred validating the email', 'simpleform' ),  
                 'empty_phone' => __( 'Please provide your phone number', 'simpleform' ),
                 'invalid_phone' => __( 'The phone number contains invalid characters', 'simpleform' ), 
                 'phone_error' => __( 'Error occurred validating the phone number', 'simpleform' ),      
                 'empty_subject' => __( 'Please enter the request subject', 'simpleform' ),
                 'incomplete_subject' => __( 'Please type a short and specific subject', 'simpleform' ), 
                 'invalid_subject' => __( 'Enter only alphanumeric characters and punctuation marks', 'simpleform' ),  
                 'subject_error' => __( 'Error occurred validating the subject', 'simpleform' ),                    
                 'empty_message' => __( 'Please enter your message', 'simpleform' ),
                 'incomplete_message' => __( 'Please type a clearer message so we can respond appropriately', 'simpleform' ),    
                 'invalid_message' => __( 'Enter only alphanumeric characters and punctuation marks', 'simpleform' ),
                 'message_error' => __( 'Error occurred validating the message', 'simpleform' ),
                 'consent_error' => __( 'Please accept our privacy policy before submitting form', 'simpleform' ),
                 'empty_captcha' => __( 'Please enter an answer', 'simpleform' ),
                 'invalid_captcha' => __( 'Please enter a valid captcha value', 'simpleform' ), 
                 'captcha_error' => __( 'Error occurred validating the captcha', 'simpleform' ), 
                 'honeypot_error' => __( 'Failed honeypot validation', 'simpleform' ), 
                 'server_error' => __( 'Error occurred during processing data. Please try again!', 'simpleform' ),
                 'duplicate_error' => __( 'The form has already been submitted. Thanks!', 'simpleform' ),
                 'ajax_error' => __( 'Error occurred during AJAX request. Please contact support!', 'simpleform' ),     
                 'success_action' => 'message',         
                 'success_message' => $thank_you_message, 
                 'confirmation_page' => '',        
                 'thanks_url' => '',
                 'server_smtp' => 'false',
                 'smtp_host' => '',
                 'smtp_encryption' => 'ssl',
                 'smtp_port' => '465',
                 'smtp_authentication' => 'true',
                 'smtp_username' => '',
                 'smtp_password' => '',
                 'notification' => 'true',
                 'notification_recipient' => $admin_email,
                 'bcc' => '',
                 'notification_email' => $admin_email,
                 'notification_name' => 'requester',
                 'custom_sender' => $website_name,
                 'notification_subject' => 'request',
                 'custom_subject' => __( 'New Contact Request', 'simpleform' ),
                 'notification_reply' => 'true',
                 'submission_number' => 'visible',
                 'autoresponder' => 'false', 
                 'autoresponder_email' => $admin_email,
                 'autoresponder_name' => $website_name,
                 'autoresponder_subject' => __( 'Your request has been received. Thanks!', 'simpleform' ),
                 'autoresponder_message' => $autoresponder_message,
                 'autoresponder_reply' => $admin_email,
                 'duplicate' => 'true',              
	             'form_pageid' => $form_page_ID,
	             'confirmation_pageid' => $confirmation_page_ID,
                 ); 
 
           add_option('sform_settings', $settings);

       }
       
       else { 
	       
	    $form_page_ID = ! empty( $settings['form_pageid'] ) ? esc_attr($settings['form_pageid']) : '';  
	    $confirmation_page_ID = ! empty( $settings['confirmation_pageid'] ) ? esc_attr($settings['confirmation_pageid']) : '';	  
	        
        if ( ! empty($form_page_ID) && get_post_status($form_page_ID) == 'trash' ) { 
	    wp_update_post(array( 'ID' => $form_page_ID, 'post_status' => 'draft' ));; 
	    }
	    
        if ( ! empty($confirmation_page_ID) && get_post_status($confirmation_page_ID) == 'trash' ) { 
	    wp_update_post(array( 'ID' => $confirmation_page_ID, 'post_status' => 'draft' ));; 
	    }
	    
       }
       
    }

    /**
     *  Specify the initial form fields.
     *
     * @since    1.8.4
     */

    public static function sform_fields() {
       
       $attributes = get_option('sform_attributes');
             
       if ( !$attributes ) {
	   
       $attributes = array(
	             'form_name' => __( 'Contact Us Page','simpleform'),
                 'show_for' => 'all', 
                 'user_role' => 'any',	             
	             'introduction_text' => '<p>' . __( 'Please fill out the form below and we will get back to you as soon as possible. Mandatory fields are marked with (*).', 'simpleform' ) . '</p>',
                 'bottom_text' => '',
                 'name_field' => 'visible',
	             'name_visibility' => 'visible',
	             'name_label' => __( 'Name','simpleform'),
                 'name_placeholder' => '',
                 'name_minlength' => '2',
                 'name_maxlength' => '0',
                 'name_requirement' => 'required',
                 'lastname_field' => 'hidden',
                 'lastname_visibility' => 'visible',
                 'lastname_label' => __( 'Last Name','simpleform'),
                 'lastname_placeholder' => '',
                 'lastname_minlength' => '2',
                 'lastname_maxlength' => '0',
                 'lastname_requirement' => 'optional',
                 'email_field' => 'visible',
                 'email_visibility' => 'visible',
                 'email_label' => __( 'Email','simpleform'),
                 'email_placeholder' => '',
                 'email_requirement' => 'required',
                 'phone_field' => 'hidden',
                 'phone_visibility' => 'visible',
                 'phone_label' => __( 'Phone','simpleform'),
                 'phone_placeholder' => '',
                 'phone_requirement' => 'optional',
                 'subject_field' => 'visible',
                 'subject_visibility' => 'visible',
                 'subject_label' => __( 'Subject','simpleform'),
                 'subject_placeholder' => '',
                 'subject_minlength' => '5',
                 'subject_maxlength' => '0',
                 'subject_requirement' => 'required',
                 'message_visibility' => 'visible',
                 'message_label' => __( 'Message','simpleform'),
                 'message_placeholder' => '',
                 'message_minlength' => '10',
                 'message_maxlength' => '0',
                 'consent_field' => 'visible',                 
                 'consent_label' => __( 'I have read and consent to the privacy policy','simpleform'),
                 'privacy_link' => 'false', 
                 'privacy_page' => '0',
                 'consent_requirement' => 'required',
                 'captcha_field' => 'hidden',
                 'captcha_label' => __( 'I\'m not a robot','simpleform'),
                 'submit_label' => __( 'Submit','simpleform'),
                 'label_position' => 'top',
                 'lastname_alignment' => 'name',
                 'phone_alignment' => 'email',
                 'submit_position' => 'centred',
                 'required_sign' => 'true',
                 'required_word' => __( '(required)','simpleform'),
                 'word_position' => 'required',
                 'form_direction' => 'ltr',
                 'additional_css' => '',
                 ); 
 
           add_option('sform_attributes', $attributes); 
	       
       }
       
    }
    
}