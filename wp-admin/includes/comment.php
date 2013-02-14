<?php
/**
 * WordPress Comment Administration API.
 *
 * @package WordPress
 * @subpackage Administration
 */

/**
 * Determine if a comment exists based on author and date.
 *
 * @since 2.0.0
 * @uses $wpdb
 *
 * @param string $comment_author Author of the comment
 * @param string $comment_date Date of the comment
 * @return mixed Comment ID on success.
 */
function comment_exists($comment_author, $comment_date) {
	global $wpdb;

	return $wpdb->get_var( $wpdb->prepare("SELECT comment_post_ID FROM $wpdb->comments
			WHERE comment_author = %s AND comment_date = %s", $comment_author, $comment_date) );
}

/**
 * Update a comment with values provided in $_POST.
 *
 * @since 2.0.0
 */
function edit_comment() {

	$post_data = wp_unslash( $_POST );

	if ( ! current_user_can( 'edit_comment', (int) $post_data['comment_ID'] ) )
		wp_die ( __( 'You are not allowed to edit comments on this post.' ) );

	$post_data['comment_author'] = $post_data['newcomment_author'];
	$post_data['comment_author_email'] = $post_data['newcomment_author_email'];
	$post_data['comment_author_url'] = $post_data['newcomment_author_url'];
	$post_data['comment_approved'] = $post_data['comment_status'];
	$post_data['comment_content'] = $post_data['content'];
	$post_data['comment_ID'] = (int) $post_data['comment_ID'];

	foreach ( array ('aa', 'mm', 'jj', 'hh', 'mn') as $timeunit ) {
		if ( !empty( $post_data['hidden_' . $timeunit] ) && $post_data['hidden_' . $timeunit] != $post_data[$timeunit] ) {
			$_POST['edit_date'] = '1';
			break;
		}
	}

	if ( !empty ( $post_data['edit_date'] ) ) {
		$aa = $post_data['aa'];
		$mm = $post_data['mm'];
		$jj = $post_data['jj'];
		$hh = $post_data['hh'];
		$mn = $post_data['mn'];
		$ss = $post_data['ss'];
		$jj = ($jj > 31 ) ? 31 : $jj;
		$hh = ($hh > 23 ) ? $hh -24 : $hh;
		$mn = ($mn > 59 ) ? $mn -60 : $mn;
		$ss = ($ss > 59 ) ? $ss -60 : $ss;
		$post_data['comment_date'] = "$aa-$mm-$jj $hh:$mn:$ss";
	}

	wp_update_comment( $post_data );
}

/**
 * Returns a comment object based on comment ID.
 *
 * @since 2.0.0
 *
 * @param int $id ID of comment to retrieve.
 * @return bool|object Comment if found. False on failure.
 */
function get_comment_to_edit( $id ) {
	if ( !$comment = get_comment($id) )
		return false;

	$comment->comment_ID = (int) $comment->comment_ID;
	$comment->comment_post_ID = (int) $comment->comment_post_ID;

	$comment->comment_content = format_to_edit( $comment->comment_content );
	$comment->comment_content = apply_filters( 'comment_edit_pre', $comment->comment_content);

	$comment->comment_author = format_to_edit( $comment->comment_author );
	$comment->comment_author_email = format_to_edit( $comment->comment_author_email );
	$comment->comment_author_url = format_to_edit( $comment->comment_author_url );
	$comment->comment_author_url = esc_url($comment->comment_author_url);

	return $comment;
}

/**
 * Get the number of pending comments on a post or posts
 *
 * @since 2.3.0
 * @uses $wpdb
 *
 * @param int|array $post_id Either a single Post ID or an array of Post IDs
 * @return int|array Either a single Posts pending comments as an int or an array of ints keyed on the Post IDs
 */
function get_pending_comments_num( $post_id ) {
	global $wpdb;

	$single = false;
	if ( !is_array($post_id) ) {
		$post_id_array = (array) $post_id;
		$single = true;
	} else {
		$post_id_array = $post_id;
	}
	$post_id_array = array_map('intval', $post_id_array);
	$post_id_in = "'" . implode("', '", $post_id_array) . "'";

	$pending = $wpdb->get_results( "SELECT comment_post_ID, COUNT(comment_ID) as num_comments FROM $wpdb->comments WHERE comment_post_ID IN ( $post_id_in ) AND comment_approved = '0' GROUP BY comment_post_ID", ARRAY_A );

	if ( $single ) {
		if ( empty($pending) )
			return 0;
		else
			return absint($pending[0]['num_comments']);
	}

	$pending_keyed = array();

	// Default to zero pending for all posts in request
	foreach ( $post_id_array as $id )
		$pending_keyed[$id] = 0;

	if ( !empty($pending) )
		foreach ( $pending as $pend )
			$pending_keyed[$pend['comment_post_ID']] = absint($pend['num_comments']);

	return $pending_keyed;
}

/**
 * Add avatars to relevant places in admin, or try to.
 *
 * @since 2.5.0
 * @uses $comment
 *
 * @param string $name User name.
 * @return string Avatar with Admin name.
 */
function floated_admin_avatar( $name ) {
	global $comment;
	$avatar = get_avatar( $comment, 32 );
	return "$avatar $name";
}

function enqueue_comment_hotkeys_js() {
	if ( 'true' == get_user_option( 'comment_shortcuts' ) )
		wp_enqueue_script( 'jquery-table-hotkeys' );
}
