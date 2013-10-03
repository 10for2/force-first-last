<?php
/*
Plugin Name: 10For2 Force Last, First Name as Display Name
Plugin URI: https://github.com/10for2/force-first-last
Description: Forces all users' display name to be "Last, First".
Version: .1
Author: 10For2 Web Development
Author URI: http://10for2.com
*/
/*
 * Forked from https://github.com/strangerstudios/force-first-last 
 * Plugin URI http://www.strangerstudios.com/wordpress-plugins/force-first-last/
 */

/*
	Hide Display Name field on profile page.
*/
function flf_show_user_profile($user)
{
?>
<script>
	jQuery(document).ready(function() {
		jQuery('#display_name').parent().parent().hide();
	});
</script>
<?php
}
add_action( 'show_user_profile', 'flf_show_user_profile' );
add_action( 'edit_user_profile', 'flf_show_user_profile' );

/*
	Fix first last on profile saves.
*/
function flf_save_extra_profile_fields( $user_id ) 
{
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	//set the display name
	$display_name = trim($_POST['last_name'] . ", " . $_POST['first_name']);
	if(!$display_name)
		$display_name = $_POST['user_login'];
		
	$_POST['display_name'] = $display_name;
	
	$args = array(
			'ID' => $user_id,
			'display_name' => $display_name
	);   
	wp_update_user( $args ) ;
}
add_action( 'personal_options_update', 'flf_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'flf_save_extra_profile_fields' );

/*
	Fix first last on register.
*/
function flf_fix_user_display_name($user_id)
{
	//set the display name
	$info = get_userdata( $user_id );
               
	$display_name = trim($info->last_name . ', ' . $info->first_name);
	if(!$display_name)
		$display_name = $info->user_login;
			   
	$args = array(
			'ID' => $user_id,
			'display_name' => $display_name
	);
   
	wp_update_user( $args ) ;
}
add_action("user_register", "flf_fix_user_display_name");

/*
	Settings Page
*/
function flf_settings_menu_item()
{
	add_options_page('Force Last First', 'Force Last First', 'manage_options', 'flf_settings', 'flf_settings_page');
}
add_action('admin_menu', 'flf_settings_menu_item', 20);

//affiliates page (add new)
function flf_settings_page()
{
	if(!empty($_REQUEST['updateusers']) && current_user_can("manage_options"))
	{
		global $wpdb;
		$user_ids = $wpdb->get_col("SELECT ID FROM $wpdb->users");
		
		foreach($user_ids as $user_id)
		{
			flf_fix_user_display_name($user_id);		 
			set_time_limit(30);			
		}
		
		?>
		<p><?php echo count($user_ids);?> users(s) fixed.</p>
		<?php
	}
	
	?>
	<p>The <em>Force Last, First Name as Display Name</em> plugin will only fix display names at registration or when a profile is updated.</p>
	<p>If you just activated this plugin, please click on the button below to update the display names of your existing users.</p>
	<p><a href="?page=flf_settings&updateusers=1" class="button-primary">Update Existing Users</a></p>
	<p><strong>WARNING:</strong> This may take a while! If you have a bunch of users or a slow server, <strong>this may hang up or cause other issues with your site</strong>. Use at your own risk.</p>	
	<?php
}
