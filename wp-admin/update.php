<?php

require_once('admin.php');

if ( !current_user_can('edit_plugins') )
                wp_die('<p>'.__('You do not have sufficient permissions to update plugins for this blog.').'</p>');

function request_filesystem_credentials($form_post, $type = '', $error = false) {
	if ( empty($type) )
		$type = get_filesystem_method();

	if ( 'direct' == $type )
		return array();
		
	if( ! $credentials = get_option('ftp_credentials') )
		$credentials = array();
	// If defined, set it to that, Else, If POST'd, set it to that, If not, Set it to whatever it previously was(saved details in option)
	$credentials['hostname'] = defined('FTP_HOST') ? FTP_HOST : (!empty($_POST['hostname']) ? $_POST['hostname'] : $credentials['hostname']);
	$credentials['username'] = defined('FTP_USER') ? FTP_USER : (!empty($_POST['username']) ? $_POST['username'] : $credentials['username']);
	$credentials['password'] = defined('FTP_PASS') ? FTP_PASS : (!empty($_POST['password']) ? $_POST['password'] : $credentials['password']);
	$credentials['ssl']      = defined('FTP_SSL')  ? FTP_SSL  : (!empty($_POST['ssl'])      ? $_POST['ssl']      : $credentials['ssl']);

	if ( ! $error && !empty($credentials['password']) && !empty($credentials['username']) && !empty($credentials['hostname']) ) {
		$stored_credentials = $credentials;
		unset($stored_credentials['password']);
		update_option('ftp_credentials', $stored_credentials);
		return $credentials;
	}
	$hostname = '';
	$username = '';
	$password = '';
	$ssl = '';
	if ( !empty($credentials) )
		extract($credentials, EXTR_OVERWRITE);
	if( $error )
		echo '<div id="message" class="error"><p>' . __('<strong>Error:</strong> There was an error connecting to the server, Please verify the settings are correct.') . '</p></div>';
?>
<form action="<?php echo $form_post ?>" method="post">
<div class="wrap">
<h2><?php _e('FTP Connection Information') ?></h2>
<p><?php _e('To perform the requested update, FTP connection information is required.') ?></p>
<table class="form-table">
<tr valign="top">
<th scope="row"><?php _e('Hostname:') ?></th>
<td><input name="hostname" type="text" id="hostname" value="<?php echo attribute_escape($hostname) ?>"<?php if( defined('FTP_HOST') ) echo ' disabled="disabled"' ?> size="40" /></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Username:') ?></th>
<td><input name="username" type="text" id="username" value="<?php echo attribute_escape($username) ?>"<?php if( defined('FTP_USER') ) echo ' disabled="disabled"' ?> size="40" /></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Password:') ?></th>
<td><input name="password" type="password" id="password" value=""<?php if( defined('FTP_PASS') ) echo ' disabled="disabled"' ?> size="40" /><?php if( defined('FTP_PASS') && !empty($password) ) _e('<em>(Password not shown)</em>'); ?></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Use SSL:') ?></th>
<td>
<select name="ssl" id="ssl"<?php if( defined('FTP_SSL') ) echo ' disabled="disabled"' ?>>
<?php
foreach ( array(0 => __('No'), 1 => __('Yes')) as $key => $value ) :
	$selected = ($ssl == $value) ? 'selected="selected"' : '';
	echo "\n\t<option value='$key' $selected>" . $value . '</option>';
endforeach;
?>
</select>
</td>
</tr>
</table>
<p class="submit">
<input type="submit" name="submit" value="<?php _e('Proceed'); ?>" />
</p>
</div>
</form>
<?php
	return false;
}

function show_message($message) {
	if( is_wp_error($message) ){
		if( $message->get_error_data() )
			$message = $message->get_error_message() . ': ' . $message->get_error_data();
		else 
			$message = $message->get_error_message();
	}
	echo "<p>$message</p>";
}

function do_plugin_upgrade($plugin) {
	global $wp_filesystem;

	$url = wp_nonce_url("update.php?action=upgrade-plugin&plugin=$plugin", "upgrade-plugin_$plugin");
	if ( false === ($credentials = request_filesystem_credentials($url)) )
		return;
		
	if( ! WP_Filesystem($credentials) ){
		request_filesystem_credentials($url, '', true); //Failed to connect, Error and request again
		return;
	}
		
	echo '<div class="wrap">';
	echo '<h2>' . __('Upgrade Plugin') . '</h2>';
	if ( $wp_filesystem->errors->get_error_code() ) {
		foreach ( $wp_filesystem->errors->get_error_messages() as $message )
			show_message($message);
		echo '</div>';
		return;
	}

	$result = wp_update_plugin($plugin, 'show_message');

	if ( is_wp_error($result) )
		show_message($result);
	else
		echo __('Plugin upgraded successfully');
	echo '</div>';
}

if ( isset($_GET['action']) ) {
	if ( isset($_GET['plugin']) )
		$plugin = trim($_GET['plugin']);

	if ( 'upgrade-plugin' == $_GET['action'] ) {
		check_admin_referer('upgrade-plugin_' . $plugin);
		$title = __('Upgrade Plugin');
		$parent_file = 'plugins.php';
		require_once('admin-header.php');
		do_plugin_upgrade($plugin);
		include('admin-footer.php');
	}
}

?>
