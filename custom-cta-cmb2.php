<?php
/*
 * Plugin Name: Custom CTAs (CMB2)
 * Version: 1.0
 * Description: Allows you to create and add custom Calls-To-Action to the bottom of selected blog posts.
 * Author: Andrew Atieh
 * Author URI: http://www.andrewatieh.com/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: custom-cta-cmb2
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Andrew Atieh
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;


// Load CMB2
require_once( "includes/lib/CMB2/init.php" );

// Load CMB2 Attached Posts
require_once ('includes/lib/cmb2-attached-posts/cmb2-attached-posts-field.php');

// Add style.css to admin section to override cmb2 styles when neccessary
function admin_register_head() {
    $siteurl = get_option('siteurl');
    $url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/style.css';
    $url2 = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/js/plugin.js';
    echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
    echo "<script src=" . $url2 ."></script>\n";
}
add_action('admin_head', 'admin_register_head');

function register_plugin_styles() {
    wp_register_style( 'frontends', plugins_url( 'Custom-CTA-CMB2/frontend.css' ) );
    wp_enqueue_style( 'frontends' );
}
add_action( 'wp_enqueue_scripts', 'register_plugin_styles' );


// Creates Custom CTA Custom Post Type
function custom_cta_init() {
    $args = array(
        'label' => 'Custom CTAs',
        'public' => true,
        'show_ui' => true,
        'capability_type' => 'page',
        'hierarchical' => false,
        'rewrite' => array('slug'=>'custom_cta'),
        'query_var' => true,
        'menu_icon' => 'dashicons-megaphone',
        'supports' => array(
            'title',
            'editor'
        )
    );
    register_post_type( 'custom_cta', $args );
}
add_action( 'init', 'custom_cta_init' );


// hook into the init action and call custom_cta_taxonomies when it fires
add_action( 'init', 'custom_cta_taxonomies', 0 );

// create a generic taxonomy 'Custom CTA Tags'
function custom_cta_taxonomies() {


    // Add new taxonomy, NOT hierarchical (like tags)
    $labels = array(
        'name'                       => _x( 'Custom CTA Tags', 'taxonomy general name' ),
        'singular_name'              => _x( 'Custom CTA Tag', 'taxonomy singular name' ),
        'search_items'               => __( 'Search Tags' ),
        'popular_items'              => __( 'Popular Tags' ),
        'all_items'                  => __( 'All Tags' ),
        'parent_item'                => null,
        'parent_item_colon'          => null,
        'edit_item'                  => __( 'Edit Tag' ),
        'update_item'                => __( 'Update Tag' ),
        'add_new_item'               => __( 'Add New Tag' ),
        'new_item_name'              => __( 'New Tag Name' ),
        'separate_items_with_commas' => __( 'Separate Tags with commas' ),
        'add_or_remove_items'        => __( 'Add or remove tag' ),
        'choose_from_most_used'      => __( 'Choose from the most used tag' ),
        'not_found'                  => __( 'No tag found.' ),
        'menu_name'                  => __( 'Custom CTA Tags' ),
    );

    $args = array(
        'hierarchical'          => true,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'custom_cta_tag' ),
    );

    register_taxonomy( 'custom_cta_tag', 'custom_cta', $args );
}



// create a function for the attached post to be added to the bottom of 'the_content'
function custom_cta_below( $content ) {
    global $post;
    $attached = get_post_meta( get_the_ID(), '_attached_cmb2_attached_posts', true );

    if(empty($attached))
    { }
    else
    {
        foreach ($attached as $attached_post)  {
            $post = get_post($attached_post);
            $extras = '<div class="custom-cta-cmb2">' . $post->post_content . '</div>';
            $extras = do_shortcode($extras);
        }
    }

    $filteredcontent = $content . $extras;

    return $filteredcontent;
}
add_filter( 'the_content', 'custom_cta_below' );

function cmb2_attached_posts_field_metaboxes_example() {
    $example_meta = new_cmb2_box( array(
        'id'           => 'cmb2_attached_posts_field',
        'title'        => __( 'Attached Posts', 'cmb2' ),
        'object_types' => array( 'post' ), // Post type
        'context'      => 'normal',
        'priority'     => 'high',
        'show_names'   => false, // Show field names on the left
    ) );
    $example_meta->add_field( array(
        'name'    => __( 'Attached Posts', 'cmb2' ),
        'desc'    => __( 'Drag posts from the left column to the right column to attach them to this page.<br />You may rearrange the order of the posts in the right column by dragging and dropping.', 'cmb2' ),
        'id'      => 'attached_cmb2_attached_posts',
        'type'    => 'custom_attached_posts',
        'options' => array(
            'show_thumbnails' => true, // Show thumbnails on the left
            'filter_boxes'    => true, // Show a text box for filtering the results
            'query_args'      => array( 'posts_per_page' => 10 ), // override the get_posts args
        )
    ) );
}
add_action( 'cmb2_init', 'cmb2_attached_posts_field_metaboxes_example' );