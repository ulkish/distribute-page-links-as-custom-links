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


    if ( $post_meta_keys ) {

        foreach( $post_meta_keys as $key => $value) {

            // Check if the meta_key ends with 'page_link' and doesn't start with an underscore
            if (  preg_match( "/^(?!_).+(page_link)$/", $key ) ) {

                // Go to origin blog to get the post ID inside the meta_key
                restore_current_blog();
                $meta_key_value = get_post_meta( $original_post_id, $key );
                // If its a post ID get the guid value from the post ID
                if ( is_numeric( $meta_key_value[0] ) ) {

                    $post_guid = get_the_guid( $meta_key_value[0] );
                    echo '$post_guid:' . $post_guid . ' ';
                    switch_to_blog( $destination_blog_id );
                    // Delete page_link and add custom_link
                    $changed_meta_key = preg_replace( "/(page_link)$/", "custom_link", $key );
                    echo '$changed_meta_key:' . $changed_meta_key . ' ';
                    delete_post_meta( $new_post_id, $key );
                    update_post_meta( $new_post_id, $changed_meta_key, $post_guid );
                    // Change link_type
                    $changed_link_type = preg_replace( "/(page_link)$/", "link_type", $key );
                    echo '$changed_link_type:' . $changed_link_type . ' ';
                    update_post_meta( $new_post_id, $changed_link_type, 'custom', 'page' );

                }
                switch_to_blog( $destination_blog_id );
            }
        }
    }

    return false;
}
add_action( 'dt_push_post', 'distribute_acf_page_link', 10, 4 );



function pull_acf_page_link( $new_post_id, $args, $post_array ) {

    $destination_blog_id = get_current_blog_id();
    distribute_acf_page_link( $new_post_id, $original_post_id, $args, $destination_blog_id );
}
// add_action( 'dt_pull_post', 'pull_acf_page_link', 10, 3 );


//Page Builder Page link
add_action( 'dt_push_post', 'update_link_types', 11, 4 );

function update_link_types ($new_post_id, $original_post_id, $args, $site){
    global $wpdb;
    $destination_blog_id = (is_numeric($site)) ? $site : $site->site->blog_id;

    // Switch to origin to get id
    restore_current_blog();
    $origin_blog_id = get_current_blog_id();
    $origin_blog_id = ($origin_blog_id===$destination_blog_id) ? $args->site->blog_id : $origin_blog_id;

    // Go back
    switch_to_blog( $destination_blog_id );

    $meta = get_post_meta($new_post_id);
    foreach ($meta as $key => $value) {
        if(strrpos($key, 'link_type')){

            if ($value[0]=='page') {

                $page_link = str_replace('link_type', 'page_link', $key);

                update_post_meta($new_post_id, $key, 'custom');
                $ob_id = get_post_meta($new_post_id, $page_link, true);
                delete_post_meta($new_post_id, $page_link);

                switch_to_blog( $origin_blog_id );
                $post_destination_link = get_permalink($ob_id);
                switch_to_blog( $destination_blog_id );


                $custom_link = str_replace('link_type', 'custom_link', $key);

                update_post_meta($new_post_id, $custom_link, $post_destination_link);

            }

        }
    }

}
