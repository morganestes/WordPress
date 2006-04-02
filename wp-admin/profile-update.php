<?php

require_once('admin.php');

check_admin_referer();

if ( !$_POST )
	die( __('No post?') );

$errors = edit_user($user_ID);

if ( is_wp_error( $errors ) ) {
	foreach( $errors->get_error_codes() as $code)
		foreach( $errors->get_error_messages($code) as $message )
			echo "$message<br />";
	exit;
}

if ( !isset( $_POST['rich_editing'] ) )
	$_POST['rich_editing'] = 'false';
update_user_option( $current_user->id, 'rich_editing', $_POST['rich_editing'], true );

do_action('personal_options_update');

if ( 'profile' == $_POST['from'] )
	$to = 'profile.php?updated=true';
else
	$to = 'profile.php?updated=true';

wp_redirect( $to );
exit;

?>
