<?php
if ( ! defined( 'WPINC' ) ) die;

$settings = get_option('sform_settings');
$admin_notices = ! empty( $settings['admin_notices'] ) ? esc_attr($settings['admin_notices']) : 'false';
$color = ! empty( $settings['admin_color'] ) ? esc_attr($settings['admin_color']) : 'default';
$notice = '';
$id = isset( $_REQUEST['form'] ) ? absint($_REQUEST['form']) : ''; 
global $wpdb;
$count_stored = $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}sform_shortcodes WHERE storing = '1' AND status != 'trash'");
$storing_notice = $count_stored == 1 && $id == '' ? '<span class="dashicons dashicons-warning" style="margin-left: 5px; opacity: 0.25; cursor: pointer; width: 30px; padding-right: 5px;"></span>' : '&nbsp;';
$update_notice = apply_filters( 'sform_update', $notice );
?>

<div id="sform-wrap" class="sform">

<div id="new-release"><?php 
  if ( !empty($update_notice) && $admin_notices == 'false' ) { echo $update_notice; } 	
  else { echo '&nbsp;'; }?>
</div>
	
<div class="full-width-bar <?php echo $color ?>">
<h1 class="title <?php echo $color ?>"><span class="dashicons dashicons-email-alt responsive"></span><?php _e( 'Entries', 'simpleform' );
$table_name = "{$wpdb->prefix}sform_shortcodes"; 
$page_forms = $wpdb->get_results( "SELECT id, name FROM $table_name WHERE widget = '0' AND status != 'trash' ORDER BY name ASC", 'ARRAY_A' );
$widget_forms = $wpdb->get_results( "SELECT id, name FROM $table_name WHERE widget != '0' AND status != 'trash' ORDER BY name ASC", 'ARRAY_A' );
$page_ids = array_column($page_forms, 'id');
$widget_ids = array_column($widget_forms, 'id');
$shortcode_ids = array_merge($page_ids, $widget_ids);
$all_forms = count($page_forms) + count($widget_forms);

if ( $all_forms > 1 ) { 
echo apply_filters( 'hidden_submissions', $notice, $id );?>
<div class="selector"><div id="wrap-selector" class="responsive"><?php echo _e( 'Select Form', 'simpleform' ) ?>:</div><div class="form-selector"><select name="form" id="form" class="<?php echo $color ?>"><option value="" <?php selected( $id, '' ); ?>><?php echo _e( 'All Forms', 'simpleform' ); ?></option><?php if ( $page_forms && $widget_forms ) {  echo '<optgroup label="'.esc_attr__( 'Embedded in page', 'simpleform' ).'">'; } foreach($page_forms as $form) { $form_id = $form['id']; $form_name = $form['name']; echo '<option value="'.$form_id.'" '.selected( $id, $form_id ) .'>'.$form_name.'</option>'; } if ( $page_forms && $widget_forms ) {  echo '</optgroup>'; } if ( $page_forms && $widget_forms ) {  echo '<optgroup label="'.esc_attr__( 'Embedded in widget area', 'simpleform' ).'">'; } foreach($widget_forms as $form) { $form_id = $form['id']; $form_name = $form['name']; echo '<option value="'.$form_id.'" '.selected( $id, $form_id ) .'>'.$form_name.'</option>'; } if ( $page_forms && $widget_forms ) {  echo '</optgroup>'; }?></select></div></div>
<?php } ?>

</h1>
</div>

<?php
if ( $id == '' ) {
$where_form = " WHERE form != '0'";
// $last_message = stripslashes(get_transient('sform_last_message'));
$last_message = get_option('sform_last_message');
} else {
$where_form = " WHERE form = '". $id ."'";

$form_entries = $wpdb->get_var( $wpdb->prepare( "SELECT entries FROM {$wpdb->prefix}sform_shortcodes WHERE id = %d", $id) );

// Option created after using the form for the first time
$last_form_message = get_option("sform_last_{$id}_message");
$last_message_timestamp = $last_form_message != false ? explode('#',$last_form_message)[0] : '';
$last_message_data = $last_form_message != false ? explode('#',$last_form_message)[1] : '';
// Search all last messages imported from other forms
$search_last_in = 'sform_last_in_'.$id.'_message_';
$results_in = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '%{$search_last_in}%'" );
$lasts_in = array($last_message_timestamp);
// $most_recent_message = $last_message_data;
foreach ( $results_in as $result_in ) { 
  $timestamp_in = explode('#', $result_in->option_value)[0];
  $lasts_in[] = $timestamp_in;
  if ( $timestamp_in == max($lasts_in) ) { 
    $last_message_in = explode('#', $result_in->option_value)[1];
	$last_message_data = $last_message_in;
  }
}

$last_date = $wpdb->get_var("SELECT date FROM {$wpdb->prefix}sform_submissions WHERE form = '$id' ORDER BY date DESC LIMIT 1");
$timestamp_last_date = strtotime($last_date);
$last_message = max($lasts_in) == $timestamp_last_date ? $last_message_data : '<div style="line-height:18px;">' . __('Data not available due to moving messages', 'simpleform') . '</div>';

}	

echo '<div id="page-description" class="submissions-list overview">';
	
if ( has_action( 'submissions_list' ) ):
  do_action( 'submissions_list', $id, $shortcode_ids, $last_message );
else:
 if ( $id == '' || in_array($id, $shortcode_ids) ) { 
      global $wpdb;
      $table_name = $wpdb->prefix . 'sform_submissions'; 
      $where_day = 'AND date >= UTC_TIMESTAMP() - INTERVAL 24 HOUR';
      $where_week = 'AND date >= UTC_TIMESTAMP() - INTERVAL 7 DAY';
      $where_month = 'AND date >= UTC_TIMESTAMP() - INTERVAL 30 DAY';
      $where_year = 'AND date >= UTC_TIMESTAMP() - INTERVAL 1 YEAR';
      $where_submissions = defined('SIMPLEFORM_SUBMISSIONS_NAME') ? "AND object != '' AND object != 'not stored'" : '';
      $count_all = $wpdb->get_var("SELECT COUNT(id) FROM $table_name $where_form $where_submissions AND hidden = '0'");
      $count_last_day = $wpdb->get_var("SELECT COUNT(id) FROM $table_name $where_form $where_day $where_submissions AND hidden = '0'");
      $count_last_week = $wpdb->get_var("SELECT COUNT(id) FROM $table_name $where_form $where_week $where_submissions AND hidden = '0'");
      $count_last_month = $wpdb->get_var("SELECT COUNT(id) FROM $table_name $where_form $where_month $where_submissions AND hidden = '0'");
      $count_last_year = $wpdb->get_var("SELECT COUNT(id) FROM $table_name $where_form $where_year $where_submissions AND hidden = '0'");
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
  }
  else { ?>
  <span><?php _e('It seems the form is no longer available!', 'simpleform' ) ?></span><p><span class="wp-core-ui button unavailable <?php echo $color ?>"><a href="<?php echo menu_page_url( 'sform-entries', false ); ?>"><?php _e('Reload the Submissions page','simpleform') ?></a></span><span class="wp-core-ui button unavailable <?php echo $color ?>"><a href="<?php echo menu_page_url( 'sform-creation', false ); ?>"><?php _e('Add New Form','simpleform') ?></a></span><span class="wp-core-ui button unavailable <?php echo $color ?>"><a href="<?php echo self_admin_url('widgets.php'); ?>"><?php _e('Activate SimpleForm Contact Form Widget','simpleform') ?></a></span></p>
  <?php	}
  endif;
  echo '</div>';
  ?>

</div>