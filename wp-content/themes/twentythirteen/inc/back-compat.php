<?php
/**
 * Twenty Thirteen back compat functionality.
 *
 * Prevents Twenty Thirteen from running on WordPress versions prior to 3.6,
 * since this theme is not meant to be backwards compatible and relies on
 * many new functions and markup changes introduced in 3.6.
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */

/**
 * Prevent switching to Twenty Thirteen on old versions of WordPress. Switches
 * to the previously activated theme or the default theme.
 *
 * @since Twenty Thirteen 1.0
 *
 * @param string $theme_name
 * @param WP_Theme $theme
 * @return void
 */
function twentythirteen_switch_theme( $theme_name, $theme ) {
	if ( 'twentythirteen' != $theme->get_template() )
		switch_theme( $theme->get_template(), $theme->get_stylesheet() );
	elseif ( 'twentythirteen' != WP_DEFAULT_THEME )
		switch_theme( WP_DEFAULT_THEME );

	unset( $_GET['activated'] );
	add_action( 'admin_notices', 'twentythirteen_upgrade_notice' );
}
add_action( 'after_switch_theme', 'twentythirteen_switch_theme', 10, 2 );

/**
 * Prints an update nag after an unsuccessful attempt to switch to
 * Twenty Thirteen on WordPress versions prior to 3.6.
 *
 * @since Twenty Thirteen 1.0
 *
 * @return void
 */
function twentythirteen_upgrade_notice() {
	$message = sprintf( __( 'Twenty Thirteen requires at least WordPress version 3.6. You are running version %s. Please upgrade and try again.', 'twentythirteen' ), $GLOBALS['wp_version'] );
	printf( '<div class="error"><p>%s</p></div>', $message );
}

/**
 * Prevents the Customizer from being loaded on WordPress versions prior to 3.6.
 *
 * @since Twenty Thirteen 1.0
 *
 * @return void
 */
function twentythirteen_customize() {
	wp_die( sprintf( __( 'Twenty Thirteen requires at least WordPress version 3.6. You are running version %s. Please upgrade and try again.', 'twentythirteen' ), $GLOBALS['wp_version'] ) . sprintf( ' <a href="javascript:history.go(-1);">%s</a>', __( 'Go back.', 'twentythirteen' ) ) );
}
add_action( 'load-customize.php', 'twentythirteen_customize' );
