<?php
/*
Plugin Name: Distribute Page Links as Custom Links
Plugin URI:
Description: Allows you to distribute page links as custom links using the Distributor plugin.
Version: 1.0.0
Author: Con Vista Al Mar
Author URI: https://convistaalmar.com.ar/
License: GPL2+
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

// Check if class already exists
if( ! class_exists( 'Distribute_Page_Links_As_Custom_Links' ) ) :


class Distribute_Page_Links_As_Custom_Links {


	/**
	 * Add actions.
	 */
	function __construct() {
		add_action( 'dt_push_post', array( $this, 'push_acf_page_link' ), 11, 4 );
		add_action( 'dt_pull_post', array( $this, 'pull_acf_page_link' ), 11, 3 );
	}



	/**
	 * Get post ID, search for page links inside said post, get the page IDs being
	 * referenced inside them, get the 'guid' value.
	 * Create a custom link inside the new post, place the 'guid' inside.
	 * Finally erase page links.
	 *
	 * @param  int    $new_post_id      Newly created post.
	 * @param  int    $original_post_id Original post ID.
	 * @param  array  $args             Not used (The arguments passed into wp_insert_post.)
	 * @param  object $site             The distributor connection being pushed to.
	 */
	function push_acf_page_link( $new_post_id, $original_post_id, $args, $site ) {

		$destination_blog_id = ( is_numeric( $site ) ) ? $site : $site->site->blog_id;

		// Switch to original blog to get id
		restore_current_blog();
		$origin_blog_id = get_current_blog_id();
		$origin_blog_id = ( $origin_blog_id === $destination_blog_id ) ? $args->site->blog_id : $origin_blog_id;
		switch_to_blog( $destination_blog_id );
		// Get meta keys from new post
		$post_meta_keys = get_post_meta( $new_post_id );


		$meta = get_post_meta( $new_post_id );
		foreach ( $meta as $key => $value ) {
			if(strrpos($key, 'link_type')){

				if ( $value[0] == 'page' ) {

					$page_link = str_replace( 'link_type', 'page_link', $key );

					update_post_meta( $new_post_id, $key, 'custom' );
					$ob_id = get_post_meta( $new_post_id, $page_link, true );
					delete_post_meta( $new_post_id, $page_link );

					switch_to_blog( $origin_blog_id );
					$post_destination_link = get_permalink( $ob_id );
					switch_to_blog( $destination_blog_id );


					$custom_link = str_replace( 'link_type', 'custom_link', $key );

					update_post_meta( $new_post_id, $custom_link, $post_destination_link );

				}

			}
		}

		return false;
	}


	/**
	 * Same functionality as main function, while pulling.
	 *
	 * @param  int   $new_post_id  Newly created post
	 * @param  array $args         The arguments passed into wp_insert_post.
	 * @param  array $post_array   (Not used)
	 */
	function pull_acf_page_link( $new_post_id, $args, $post_array ) {

		$destination_blog_id = get_current_blog_id();
		$this->push_acf_page_link( $new_post_id, $original_post_id, $args, $destination_blog_id );

	}

} // End of class.



new Distribute_Page_Links_As_Custom_Links;// Instantiate our class.


endif;// End of class_exists() check.
