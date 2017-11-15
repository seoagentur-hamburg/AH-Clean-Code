<?php
/*
* Plugin Name: AH Clean Code
* Description: Removes unnecessary code parts from the WordPress source code
* Version: 1.0.0
* Author: Andreas Hecht
* Author URI: https://andreas-hecht.com
*/

 /**
* Clean up wp-head()
*/ 
add_action('init', 'remheadlink');
function remheadlink()
{
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
remove_action('wp_head', 'wp_shortlink_header', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
}


/* =============================================================================
### Disable WordPress Embeds
============================================================================= */

/**
* Disable embeds on init.
*
* - Removes the needed query vars.
* - Disables oEmbed discovery.
* - Completely removes the related JavaScript.
*
* @since 1.0.0
*/
function evolution_disable_embeds_init() {
/* @var WP $wp */
global $wp;

// Remove the embed query var.
$wp->public_query_vars = array_diff( $wp->public_query_vars, array(
'embed',
) );

// Remove the REST API endpoint.
remove_action( 'rest_api_init', 'wp_oembed_register_route' );

// Turn off oEmbed auto discovery.
add_filter( 'embed_oembed_discover', '__return_false' );

// Don't filter oEmbed results.
remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

// Remove oEmbed discovery links.
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

// Remove oEmbed-specific JavaScript from the front-end and back-end.
remove_action( 'wp_head', 'wp_oembed_add_host_js' );
add_filter( 'tiny_mce_plugins', 'evolution_disable_embeds_tiny_mce_plugin' );

// Remove filter of the oEmbed result before any HTTP requests are made.
remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
}

add_action( 'init', 'evolution_disable_embeds_init', 9999 );

/**
* Removes the 'wpembed' TinyMCE plugin.
*
* @since 1.0.0
*
* @param array $plugins List of TinyMCE plugins.
* @return array The modified list.
*/
function evolution_disable_embeds_tiny_mce_plugin( $plugins ) {
return array_diff( $plugins, array( 'wpembed' ) );
}



/**
* Disable the emoji's
*/
function disable_emojis() {
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' ); 
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' ); 
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
add_filter( 'wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2 );
}
add_action( 'init', 'disable_emojis' );

/**
* Filter function used to remove the tinymce emoji plugin.
* 
* @param array $plugins 
* @return array Difference betwen the two arrays
*/
function disable_emojis_tinymce( $plugins ) {
if ( is_array( $plugins ) ) {
return array_diff( $plugins, array( 'wpemoji' ) );
} else {
return array();
}
}

/**
* Remove emoji CDN hostname from DNS prefetching hints.
*
* @param array $urls URLs to print for resource hints.
* @param string $relation_type The relation type the URLs are printed for.
* @return array Difference betwen the two arrays.
*/
function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
if ( 'dns-prefetch' == $relation_type ) {
/** This filter is documented in wp-includes/formatting.php */
$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );

$urls = array_diff( $urls, array( $emoji_svg_url ) );
}

return $urls;
}


/**
* Remove Query Strings for better caching
* 
* @author Andreas Hecht
*/
function evolution_remove_wp_ver_css_js( $src ) {

if ( strpos( $src, 'ver=' ) )

$src = remove_query_arg( 'ver', $src );

return $src;
}
add_filter( 'style_loader_src', 'evolution_remove_wp_ver_css_js', 9999 );
add_filter( 'script_loader_src', 'evolution_remove_wp_ver_css_js', 9999 );



/**
 * Disables the heartbeat interface, except for the posts
 */
function ah_stop_heartbeat() {
global $pagenow;
if ($pagenow != 'post.php' && $pagenow != 'post-new.php') wp_deregister_script('heartbeat');
}
add_action('init', 'ah_stop_heartbeat', 1);



/**
* Disable responsive image support (test!)
*/
// Clean the up the image from wp_get_attachment_image()
add_filter( 'wp_get_attachment_image_attributes', function( $attr )
{
if( isset( $attr['sizes'] ) )
unset( $attr['sizes'] );

if( isset( $attr['srcset'] ) )
unset( $attr['srcset'] );

return $attr;

}, PHP_INT_MAX );

// Override the calculated image sizes
add_filter( 'wp_calculate_image_sizes', '__return_false',  PHP_INT_MAX );

// Override the calculated image sources
add_filter( 'wp_calculate_image_srcset', '__return_false', PHP_INT_MAX );

// Remove the reponsive stuff from the content
remove_filter( 'the_content', 'wp_make_content_images_responsive' );