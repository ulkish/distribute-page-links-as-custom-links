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
    switch_to_blog( $destination_blog_id );
    // Get meta keys from new post
    $post_meta_keys = get_post_meta( $new_post_id );



    // METHOD 1
    // Check if any meta_key has a value of 'page' and is followed by a post ID
    // Get the guid from the post ID inside of *page_link
    // Update the *page_link to *custom_link, then place the guid inside.
    if ( $post_meta_keys ) {

        foreach( $post_meta_keys as $key => $value) {
            // If link_type is found
            if ( $value[0] === 'page') {

                $meta_id = get_mid_by_key( $new_post_id, $key );
                $meta_id += 2;
                $post_id = get_value_by_mid( $new_post_id, $meta_id );

                // If it's a post ID get its 'guid' value
                if ( is_numeric( $post_id ) ) {
                    restore_current_blog();
                    $post_guid = get_the_guid( $post_id );
                    switch_to_blog( $destination_blog_id );
                }

                $meta_key = get_key_by_mid( $new_post_id, $meta_id );
                // Not working!
                $changed_meta_key = preg_replace( "/(page_link)$/", "custom_link", $meta_key );

                // NOTE: Sometimes there's an unused custom link with a post in PDF form,
                // it'll be updated if found.

                if ( $key && $post_guid ) {
                    echo '$meta_key:' . $meta_key . ' ';
                    echo '$changed_meta_key:' . $meta_key . ' ';
                    echo '$key:' . $key . ' ';
                    echo '$post_guid:' . $post_guid . ' ';
                    // $delete_post_meta( $new_post_id, $meta_key );
                    // $update_post_meta( $new_post_id, $changed_meta_key, $post_guid );
                    // $update_post_meta( $new_post_id, $key, 'custom', 'page' );
                }
            }
        }
    }


    // Testing with hardcoded values
    // delete_post_meta( $new_post_id, 'sections_3_media_items_1_action_link_type' );
    // delete_post_meta( $new_post_id, 'sections_3_media_items_1_action_page_link' );
    // delete_post_meta( $new_post_id, 'sections_3_media_items_1_action_custom_link' );
    // add_post_meta( $new_post_id, 'sections_3_media_items_1_action_link_type', 'custom');
    // add_post_meta( $new_post_id, 'sections_3_media_items_1_action_custom_link', 'http://propane.local/pagetocustomagain/');



    // if ( $post_meta_keys ) {

    //     foreach( $post_meta_keys as $key => $value ) {




    //         // METHOD 2
    //         // Check if the meta_key ends with 'page_link' and doesn't start with an underscore
    //         // Check if the content is a post ID (with is_numeric())
    //         if ( preg_match( "/^(?!_).+(page_link)$/", $key ) ) {

    //             restore_current_blog();
    //             $post_meta_value = get_post_meta( $original_post_id, $key );

    //             // TODO: Get guid
    //             switch_to_blog( $destination_blog_id );



    //             // delete_post_meta( $new_post_id, $key );
    //             // $changed_meta_key = preg_replace( "/(page_link)$/", "custom_link", $key );
    //             // $add_post_meta( $new_post_id, $changed_meta_key, $changed_meta_value );
    //         }
    //     }
    // }


    return false;
}
add_action( 'dt_push_post', 'distribute_acf_page_link', 10, 4 );



function pull_acf_page_link( $new_post_id, $args, $post_array ) {

    $destination_blog_id = get_current_blog_id();
    distribute_acf_page_link( $new_post_id, $original_post_id, $args, $destination_blog_id );
}
// add_action( 'dt_pull_post', 'pull_acf_page_link', 10, 3 );

function get_mid_by_key( $post_id, $meta_key ) {
    global $wpdb;

    $mid = $wpdb->get_var( $wpdb->prepare("
        SELECT meta_id FROM $wpdb->postmeta
        WHERE post_id = %d
        AND meta_key = %s", $post_id, $meta_key) );

    if( $mid != '' )
    return (int)$mid;

  return false;
}

function get_value_by_mid( $post_id, $meta_id ) {
    global $wpdb;

    $meta_value = $wpdb->get_var( $wpdb->prepare("
        SELECT meta_value FROM $wpdb->postmeta
        WHERE post_id = %d
        AND meta_id = %d", $post_id, $meta_id) );

    return $meta_value;
}

function get_key_by_mid( $post_id, $meta_id ) {
    global $wpdb;

    $meta_key = $wpdb->get_var( $wpdb->prepare("
        SELECT meta_key FROM $wpdb->postmeta
        WHERE post_id = %d
        AND meta_id = %d", $post_id, $meta_id) );

    return $meta_key;
}
