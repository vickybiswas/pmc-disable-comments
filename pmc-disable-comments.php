<?php
/*
Plugin Name: PMC Disable Comments
Plugin URI: https://github.com/vickybiswas/pmc-disable-comments
Description: PMC Disable allows you to switch default commenting on/off for individual post types.
Version: 1.0
Author: Vicky Biswas
Author URI: http://www.pmc.com/
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

abstract class PMC_Disable_Comments {
	private static $blocked_types;
	public function admin() {
		self::$blocked_types = (array) get_option( 'pmc-disable-comments-toggle' );
		add_filter( 'default_content', array('PMC_Disable_Comments','default_comment'), 10, 2 );
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
	function default_comment( $content, $post ) {
		if ( in_array( $post->post_type, self::$blocked_types ) ) {
			$post->comment_status = false;
			$post->ping_status = false;
		}
		return $content;
	}
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
add_action( 'admin_init', array('PMC_Disable_Comments','admin') );
