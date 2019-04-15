<?php
/**
 * Distribute ACF Page Links as Custom Links
 *
 * Plugin Name: Distribute ACF Page Links as Custom Links
 * Plugin URI:
 * Description: This is an addon for Distributor plugin.
 * Version:     1.0
 * Author:      Tipit.net
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}
// REPLACE PAGE LINKS FOR CUSTOM LINKS:
// Get post ID, search for page links inside said post, get the page IDs being
// referenced inside them, get the 'guid' value.
// Create a custom link inside the new post, place the 'guid' inside.
// Erase page links.


function distribute_acf_page_link( $new_post_id, $original_post_id, $args, $site ) {

    $destination_blog_id = (is_numeric($site)) ? $site : $site->site->blog_id;

    // Switch to original blog to get id
    restore_current_blog();
    $origin_blog_id = get_current_blog_id();
    $origin_blog_id = ( $origin_blog_id === $destination_blog_id ) ? $args->site->blog_id : $origin_blog_id;




    // METHOD 1
    // Check if meta_key has a value of 'page' and is followed by a post ID
    $args = array(
        'order'      => 'DESC',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'value' => 'page',
                'compare' => '=',
            )
        )
    );

    $query = new WP_Query($args);






    // Testing with hardcoded values
    // delete_post_meta( $new_post_id, 'sections_3_media_items_1_action_link_type' );
    // delete_post_meta( $new_post_id, 'sections_3_media_items_1_action_page_link' );
    // delete_post_meta( $new_post_id, 'sections_3_media_items_1_action_custom_link' );
    // add_post_meta( $new_post_id, 'sections_3_media_items_1_action_link_type', 'custom');
    // add_post_meta( $new_post_id, 'sections_3_media_items_1_action_custom_link', 'http://propane.local/pagetocustomagain/');

    // Get meta keys from origin blog
    $post_meta_keys = get_post_meta( $original_post_id );
    if ( $post_meta_keys ) {

        foreach( $post_meta_keys as $key => $value ) {




            // METHOD 2
            // Check if the meta_key ends with 'page_link' and doesn't start with an underscore
            // Check if the content is a post ID (with is_numeric())
            if ( preg_match( "/^(?!_).+(page_link)$/", $key ) ) {

                restore_current_blog();
                $post_meta_value = get_post_meta( $original_post_id, $key );

                // TODO: Get guid
                switch_to_blog( $destination_blog_id );



                // delete_post_meta( $new_post_id, $key );
                // $changed_meta_key = preg_replace( "/(page_link)$/", "custom_link", $key );
                // $add_post_meta( $new_post_id, $changed_meta_key, $changed_meta_value );
            }








            // foreach ( $page_link_matches as $match ) {
            //     $match_key_content = get_post_meta( $original_post_id, $match );
            //     echo "This is what the content looks like: " . $match_key_content . " ";

            // }
            // if ( is_numeric( $match ) ) {

            //     echo "This key matched: " . $key . " ";

            // }



        }

    }

    // Go back
    switch_to_blog( $destination_blog_id );

    return false;
}
add_action( 'dt_push_post', 'distribute_acf_page_link', 10, 4 );



function pull_acf_page_link( $new_post_id, $args, $post_array ) {

    $destination_blog_id = get_current_blog_id();
    distribute_acf_page_link( $new_post_id, $original_post_id, $args, $destination_blog_id );
}
// add_action( 'dt_pull_post', 'pull_acf_page_link', 10, 3 );
