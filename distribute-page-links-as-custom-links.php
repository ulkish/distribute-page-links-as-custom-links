<?php
/*
Plugin Name: Distribute Page Links as Custom Links
Plugin URI:
Description: Allows you to distribute page links as custom links using the Distributor plugin.
Version: 1.0.0
Author: hugomoran
Author URI: https://convistaalmar.com.ar/
License: GPL2+
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

// REPLACE PAGE LINKS FOR CUSTOM LINKS:
// Get post ID, search for page links inside said post, get the page IDs being
// referenced inside them, get the 'guid' value.
// Create a custom link inside the new post, place the 'guid' inside.
// Erase page links.

add_action( 'dt_push_post', 'push_acf_page_link', 10, 4 );
add_action( 'dt_pull_post', 'pull_acf_page_link', 10, 3 );

function push_acf_page_link( $new_post_id, $original_post_id, $args, $site ) {

    echo 'Push_acf_page_link function.';
    $destination_blog_id = (is_numeric($site)) ? $site : $site->site->blog_id;

    // Switch to original blog to get id
    restore_current_blog();
    $origin_blog_id = get_current_blog_id();
    $origin_blog_id = ( $origin_blog_id === $destination_blog_id ) ? $args->site->blog_id : $origin_blog_id;
    switch_to_blog( $destination_blog_id );
    // Get meta keys from new post
    $post_meta_keys = get_post_meta( $new_post_id );


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


                $custom_link = str_replace( 'link_type', 'custom_link', $key );

                update_post_meta( $new_post_id, $custom_link, $post_destination_link );

            }

        }
    }

    return false;
}



function pull_acf_page_link( $new_post_id, $args, $post_array ) {

    $destination_blog_id = get_current_blog_id();
    push_acf_page_link( $new_post_id, $original_post_id, $args, $destination_blog_id );

}
