<?php
/*
Plugin Name: PMC Disable Comments
Plugin URI: https://github.com/vickybiswas/pmc-disable-comments
Description: PMC Disable allows you to switch default commenting on/off for individual post types.
Version: 1.1.0
Author: Vicky Biswas
Author URI: http://www.pmc.com/
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/
defined( 'ABSPATH' ) or die();

class PMC_Disable_Comments {

	private static $blocked_types;

	public function __construct(){
		add_action( 'admin_init', array('PMC_Disable_Comments','admin') );
		add_action( 'wp_loaded', array( $this, 'setup_filters' ) );

		self::$blocked_types = (array) get_option( 'pmc-disable-comments-toggle' );
	}

	public function setup_filters(){
		if( !is_admin() ) {
			add_action( 'template_redirect', array( $this, 'load_comment_template' ) );
		}
	}

	/**
	 * Checks if current post type is in out black list
	 */
	function load_comment_template() {

		if( !is_singular() ){
			return;
		}

		if( in_array( get_post_type(), self::$blocked_types ) ) {
			add_filter( 'comments_template', array( $this, 'dummy_comments_template' ), 20 );
			// Remove comment-reply script for themes that include it indiscriminately
			wp_deregister_script( 'comment-reply' );
			// feed_links_extra inserts a comments RSS link
			remove_action( 'wp_head', 'feed_links_extra', 3 );
		}

	}

	/**
	 * Returns dummy comment template path
	 * @return string
	 */
	function dummy_comments_template() {
		return dirname( __FILE__ ) . '/comments-template.php';
	}

	/**
	 * Register admin option
	 */
	public function admin() {

		add_settings_section(
			'pmc-disable-comments',
			'Disable/Enable comments for the below:',
			'',
			'discussion'
		);

		add_settings_field(
			'pmc-disable-comments-toggle',
			'Choose the Post Types for which you want to switch comments off:',
			array('PMC_Disable_Comments', 'admin_settings'),
			'discussion',
			'pmc-disable-comments'
		);

		register_setting(
			'discussion',
			'pmc-disable-comments-toggle'
		);
	}

	/**
	 * Register admin form fields
	 *
	 * @param $args
	 */
	function admin_settings( $args ) {
		$post_types=get_post_types('','objects');
		echo '<fieldset>';
		foreach ($post_types  as $post_type_slug=>$post_type ) {
			if ( post_type_supports( $post_type_slug, 'comments' ) ) {
				echo "
				<label for='pmc-disable-comments-$post_type_slug'>
					<input type='checkbox' id='pmc-disable-comments-$post_type_slug' name='pmc-disable-comments-toggle[]' value='$post_type_slug' " . checked(in_array($post_type_slug,self::$blocked_types),true,false) . " />{$post_type->labels->singular_name}
					<br />
				</label>";
			}
		}
		echo '</fieldset>';
	}
}

new PMC_Disable_Comments();