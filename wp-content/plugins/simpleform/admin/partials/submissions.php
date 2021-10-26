<?php
if ( ! defined( 'WPINC' ) ) die;

$settings = get_option('sform_settings');
$admin_notices = ! empty( $settings['admin_notices'] ) ? esc_attr($settings['admin_notices']) : 'false';
$color = ! empty( $settings['admin_color'] ) ? esc_attr($settings['admin_color']) : 'default';
$notice = '';
?>

<div id="sform-wrap" class="sform">

<div id="new-release" class="<?php if ( $admin_notices == 'true' ) {echo 'invisible';} ?>"><?php echo apply_filters( 'sform_update', $notice ); ?>&nbsp;</div>
	
<div class="full-width-bar <?php echo $color ?>"><h1 class="title <?php echo $color ?>"><span class="dashicons dashicons-email-alt responsive"></span><?php _e( 'Submissions', 'simpleform' );
$id = isset( $_REQUEST['form'] ) ? absint($_REQUEST['form']) : ''; 
global $wpdb; 
$table_name = "{$wpdb->prefix}sform_shortcodes"; 
$page_forms = $wpdb->get_results( "SELECT id, name FROM $table_name WHERE widget = '0' ORDER BY name ASC", 'ARRAY_A' );
$widget_forms = $wpdb->get_results( "SELECT id, name FROM $table_name WHERE widget != '0' ORDER BY name ASC", 'ARRAY_A' );
$page_ids = array_column($page_forms, 'id');
$widget_ids = array_column($widget_forms, 'id');
$shortcode_ids = array_merge($page_ids, $widget_ids);
$all_forms = count($page_forms) + count($widget_forms);

if ( $all_forms > 1 ) { ?>
<div class="selector"><div id="wrap-selector" class="responsive"><?php echo _e( 'Select Form', 'simpleform' ) ?>:</div><div class="form-selector"><select name="form" id="form" class="<?php echo $color ?>"><option value="" <?php selected( $id, '' ); ?>><?php echo _e( 'All Forms', 'simpleform' ); ?></option><?php if ( $page_forms && $widget_forms ) {  echo '<optgroup label="'.esc_attr__( 'Embedded in page', 'simpleform' ).'">'; } foreach($page_forms as $form) { $form_id = $form['id']; $form_name = $form['name']; echo '<option value="'.$form_id.'" '.selected( $id, $form_id ) .'>'.$form_name.'</option>'; } if ( $page_forms && $widget_forms ) {  echo '</optgroup>'; } if ( $page_forms && $widget_forms ) {  echo '<optgroup label="'.esc_attr__( 'Embedded in widget area', 'simpleform' ).'">'; } foreach($widget_forms as $form) { $form_id = $form['id']; $form_name = $form['name']; echo '<option value="'.$form_id.'" '.selected( $id, $form_id ) .'>'.$form_name.'</option>'; } if ( $page_forms && $widget_forms ) {  echo '</optgroup>'; }?></select></div></div>
<?php } ?>
</h1></div>

<?php
if ( $id == '' ) {
$where_form = " WHERE form != '0'";
$last_message = stripslashes(get_transient('sform_last_message'));
} else {
$where_form = " WHERE form = '". $id ."'";
$last_message = stripslashes(get_transient('sform_last_'.$id.'_message'));
}	

echo '<div id="page-description" class="submissions-list overview">';
	
if ( has_action( 'submissions_list' ) ):
  do_action( 'submissions_list', $id, $shortcode_ids );
else:
 if ( $id == '' || in_array($id, $shortcode_ids) ) {
      global $wpdb;
      $table_name = $wpdb->prefix . 'sform_submissions'; 
      $where_day = 'AND date >= UTC_TIMESTAMP() - INTERVAL 24 HOUR';
      $where_week = 'AND date >= UTC_TIMESTAMP() - INTERVAL 7 DAY';
      $where_month = 'AND date >= UTC_TIMESTAMP() - INTERVAL 30 DAY';
      $where_year = 'AND date >= UTC_TIMESTAMP() - INTERVAL 1 YEAR';
      $count_all = $wpdb->get_var("SELECT COUNT(id) FROM $table_name $where_form");
      $count_last_day = $wpdb->get_var("SELECT COUNT(id) FROM $table_name $where_form $where_day ");
      $count_last_week = $wpdb->get_var("SELECT COUNT(id) FROM $table_name $where_form $where_week ");
      $count_last_month = $wpdb->get_var("SELECT COUNT(id) FROM $table_name $where_form $where_month ");
      $count_last_year = $wpdb->get_var("SELECT COUNT(id) FROM $table_name $where_form $where_year ");
      $total_received = $count_all;
      $string1 = __('Submissions data is not stored in the WordPress database by default.', 'simpleform' );
      $string2 = __('Submissions data is not stored in the WordPress database', 'simpleform' );
      $string3 = __('We have designed SimpleForm to be a minimal, lightweight, fast and privacy-respectful plugin, so that it does not interfere with your site performance and can be easily managed.', 'simpleform' );
      $string4 = __('You can enable this feature with the <b>SimpleForm Contact Form Submissions</b> addon activation.', 'simpleform' );
      $string5 = __('If you want to keep a copy of your messages, you can add this feature with the <b>SimpleForm Contact Form Submissions</b> addon.', 'simpleform' );
      $string6 = __('You can find it in the WordPress.org plugin repository.', 'simpleform' );
      $string7 = __('By default, only the last message is temporarily stored.', 'simpleform' );
      $string8 = __('Therefore, it is recommended to verify the correct SMTP server configuration in case of use, and always keep the notification email enabled, if you want to be sure to receive messages.', 'simpleform' );
      $string9 = __('You can enable this feature by activating the <b>SimpleForm Contact Form Submissions</b> addon.', 'simpleform' );
      $string10 = __(' Go to the Plugins page.', 'simpleform' );
?>
	 
<div><ul id="submissions-data"><li class="type"><span class="label"><?php _e( 'Received', 'simpleform' ); ?></span><span class="value"><?php echo $total_received; ?></span></li><li class="type"><span class="label"><?php _e( 'This Year', 'simpleform' ); ?></span><span class="value"><?php echo $count_last_year; ?></span></li><li class="type"><span class="label"><?php _e( 'Last Month', 'simpleform' ); ?></span><span class="value"><?php echo $count_last_month; ?></span></li><li class="type"><span class="label"><?php _e( 'Last Week', 'simpleform' ); ?></span><span class="value"><?php echo $count_last_week; ?></span></li><li><span class="label"><?php _e( 'Last Day', 'simpleform' ); ?></span><span class="value"><?php echo $count_last_day; ?></span></li></ul></div>

<?php
    $plugin_file = 'simpleform-contact-form-submissions/simpleform-submissions.php';
    $admin_url = is_network_admin() ? network_admin_url( 'plugins.php' ) : admin_url( 'plugins.php' );
	if ( $last_message ) {
	echo '<div id="last-submission"><h3><span class="dashicons dashicons-buddicons-pm"></span>'.__('Last Message Received', 'simpleform' ).'</h3>'.$last_message . '</div>';
    if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {		
	echo '<div id="submissions-notice" class="unseen"><h3><span class="dashicons dashicons-editor-help"></span>'. __('Before you go crazy looking for the received messages', 'simpleform' ).'</h3>'. __( 'Submissions data is not stored in the WordPress database. We have designed SimpleForm to be a minimal, lightweight, fast and privacy-respectful plugin, so that it does not interfere with your site performance and can be easily managed. If you want to keep a copy of your messages, you can add this feature with the <b>SimpleForm Contact Form Submissions</b> addon. You can find it in the WordPress.org plugin repository. By default, only the last message is temporarily stored. Therefore, it is recommended to verify the correct SMTP server configuration in case of use, and always keep the notification email enabled, if you want to be sure to receive messages.', 'simpleform' ) .'</div>'; 	
	}
	else {
    if ( ! class_exists( 'SimpleForm_Submissions' ) ) {	
	echo '<div id="submissions-notice" class="unseen"><h3><span class="dashicons dashicons-editor-help"></span>'. __('Before you go crazy looking for the received messages', 'simpleform' ).'</h3>'. __('Submissions data is not stored in the WordPress database by default. We have designed SimpleForm to be a minimal, lightweight, fast and privacy-respectful plugin, so that it does not interfere with your site performance and can be easily managed. You can enable this feature by activating the <b>SimpleForm Contact Form Submissions</b> addon. Go to the Plugins page.', 'simpleform' ) .'</div>';	
	}
	}
	}
	else  {
    if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {		
	echo '<div id="empty-submission"><h3><span class="dashicons dashicons-info"></span>'. __('Empty Inbox', 'simpleform' ).'</h3>'. __('So far, no message has been received yet!', 'simpleform' ).'<p>'. sprintf( __('Please note that submissions data is not stored in the WordPress database by default. We have designed SimpleForm to be a minimal, lightweight, fast and privacy-respectful plugin, so that it does not interfere with your site performance and can be easily managed. If you want to keep a copy of your messages, you can add this feature with the <a href="%s" target="_blank">SimpleForm Contact Form Submissions</a> addon. You can find it in the WordPress.org plugin repository.', 'simpleform' ), esc_url( 'https://wordpress.org/plugins/simpleform-contact-form-submissions/' ) ).'</div>';
	}
	else {
    if ( ! class_exists( 'SimpleForm_Submissions' ) ) {	
     echo '<div id="empty-submission"><h3><span class="dashicons dashicons-info"></span>'. __('Empty Inbox', 'simpleform' ).'</h3>'. __('So far, no message has been received yet!', 'simpleform' ).'<p>'.sprintf( __('Submissions data is not stored in the WordPress database by default. We have designed SimpleForm to be a minimal, lightweight, fast and privacy-respectful plugin, so that it does not interfere with your site performance and can be easily managed. You can enable this feature with the <b>SimpleForm Contact Form Submissions</b> addon activation. Go to the <a href="%s">Plugins</a> page.', 'simpleform' ), esc_url( $admin_url ) ) . '</div>';
	}
	}
	}
		
	if ( $id != '' ) { 
	global $wpdb; 
    $table_name = "{$wpdb->prefix}sform_shortcodes"; 
	$form_name = $wpdb->get_var( "SELECT name FROM $table_name WHERE id = {$id}" );
	?>
	<span id="deletion-toggle" class="deletion <?php echo $color ?>"><?php _e( 'Delete Form', 'simpleform' ) ?></span>
	<div id="deletion-notice" class="unseen">
	<form id="deletion" method="post">
	<input type="hidden" id="form-id" name="form-id" value="<?php echo $id ?>">
	<h3><span class="dashicons dashicons-trash"></span><?php _e( 'Delete Form', 'simpleform' ); echo ':&nbsp;' . $form_name; ?></h3><div class="disclaimer"><?php _e( 'Deleting a form is permanent. Once a form is deleted, it can\'t be restored. All submissions to that form are permanently deleted too.', 'simpleform' ) ?></div><div id="deletion-buttons"><div class="delete cancel"><?php _e( 'Cancel', 'simpleform' ) ?></div><input type="submit" class="delete" id="deletion-confirm" name="deletion-confirm" value="<?php esc_attr_e( 'Continue with deletion', 'simpleform' ) ?>"></div><?php wp_nonce_field( 'sform_nonce_deletion', 'sform_nonce'); ?>
    </form>	
    </div>	
    <?php }
	
  }
  
  else { ?>
  <span><?php _e('It seems the form is no longer available!', 'simpleform' ) ?></span><p><span class="wp-core-ui button unavailable <?php echo $color ?>"><a href="<?php echo menu_page_url( 'sform-submissions', false ); ?>"><?php _e('Reload the Submissions page','simpleform') ?></a></span><span class="wp-core-ui button unavailable <?php echo $color ?>"><a href="<?php echo menu_page_url( 'sform-creation', false ); ?>"><?php _e('Add New Form','simpleform') ?></a></span><span class="wp-core-ui button unavailable <?php echo $color ?>"><a href="<?php echo self_admin_url('widgets.php'); ?>"><?php _e('Activate SimpleForm Contact Form Widget','simpleform') ?></a></span></p>
  <?php	}
  endif;
  echo '</div>';
  ?>

</div>