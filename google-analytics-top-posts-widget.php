<?php
/**
 * Google Analytics Top Content Widget
 *
 * An easy way to see which templates your pages are using without having to view the page editor.
 *
 * @package   Google_Analytics_Top_Content_Widget
 * @author    Justin Sternberg <me@jtsternberg.com>
 * @license   GPL-2.0+
 * @link      http://j.ustin.co/rYL89n
 *
 * Plugin Name: Google Analytics Top Content Widget
 * Description: Widget and shortcode to display top content according to Google Analytics. ("Google Analytics by Yoast" plugin required)
 * Plugin URI:  http://j.ustin.co/yWTtmy
 * Author:      Jtsternberg
 * Author URI:  http://dsgnwrks.pro
 * Donate link: http://j.ustin.co/rYL89n
 * Version:     1.6.7
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'GATC_DIR', plugin_dir_path( __FILE__ ) );
define( 'GATC_INCLUDES_DIR', GATC_DIR .'includes/' );

require_once( GATC_INCLUDES_DIR . 'class-ga-top-content.php' );

/**
 * Register Top Content widgets
 */
function dsgnwrks_register_google_top_posts_widget() {
	require_once( GATC_INCLUDES_DIR . 'class-dsgnwrks-google-top-posts-widgets.php' );
	register_widget( 'Dsgnwrks_Google_Top_Posts_Widgets' );
}
add_action( 'widgets_init', 'dsgnwrks_register_google_top_posts_widget' );
