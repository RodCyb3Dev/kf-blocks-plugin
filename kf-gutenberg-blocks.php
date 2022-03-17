<?php
/*
 Plugin Name: Kodeflash Blocks
 Plugin URI: 
 Description: Plugin adds Gutenberg Blocks Hero, Posts, Testimonial, and Image-Text.
 Version: 1.0
 Author: Rodney H
 Author URI: https://github.com/Rodcode47
 License: GPL2
 License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

 // Prevent the execution
 if(!defined('ABSPATH')) exit;

 /** Register The Gutenberg blocks and CSS */

 add_action('init', 'kf_register_gutenberg_blocks');

 function kf_register_gutenberg_blocks() {
    // Check if gutenberg is installed

    if( !function_exists('register_block_type')) {
        return;
    }

    // Register the Block editor script
    wp_register_script(
        'kf-editor-script', 
        plugins_url( 'build/index.js', __FILE__ ), // url to file
        array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'), // dependencies
        filemtime( plugin_dir_path( __FILE__ ) . 'build/index.js') // version
    ); 

    // Gutenberg Editor CSS (Backend)
    wp_register_style(
        'kf-editor-style', // name
        plugins_url( 'build/editor.css', __FILE__ ), // file
        array(), // dependencies
        filemtime( plugin_dir_path( __FILE__ ) . 'build/editor.css') // version
    );

    // Front end Stylesheet
    wp_register_style(
        'kf-front-end-styles', // name
        plugins_url( 'build/style.css', __FILE__ ), // file
        array(), // dependencies
        filemtime( plugin_dir_path( __FILE__ ) . 'build/style.css') // version
    );

    // An Array of Blocks
    $blocks = array(
        'kf/testimonial',
        'kf/hero', 
        'kf/imagetext'
    );

    // Add the blocks and register the Stylesheets
    foreach($blocks as $block) {
        register_block_type( $block, array(
            'editor_script' => 'kf-editor-script',
            'editor_style' => 'kf-editor-style', // backend CSS
            'style' => 'kf-front-end-styles', // front end css
        ));
    }


    // Enqueue the Dynamic Block (latest recipes)

    register_block_type('kf/latest', array(
        'editor_script' => 'kf-editor-script',
        'editor_style' => 'kf-editor-style',
        'style' =>  'kf-front-end-styles', 
        'render_callback' => 'kf_latest_recipes_block'
    ));
 }

 /** Custom Categories */
 add_filter('block_categories', 'kf_new_gutenberg_category', 10, 2);
 function kf_new_gutenberg_category( $categories, $post ) {
    return array_merge(
        $categories,
        array(
            array(
                'slug' => 'kf-cat', 
                'title' => 'Kodeflash Category', 
                'icon' => 'awards'
            ),
        )
    );
 }

 /** Callback that displays the 3 latest articles */
 function kf_latest_articles_block() {
     
    global $post;

    // Build a Query
    $articles = wp_get_recent_posts(array(
        'post_type' => 'post',
        'numberposts' => 3, 
        'post_status' => 'publish'
    ));

    // Check if any article is returned
    if( count($articles) === 0) {
        return "There're no articles";
    }

    // Response that is going to be rendered
    $body = '';
    $body .= '<h1 class="latest-articles-heading">Latest Articles</h1>';
    $body .= '<ul class="latest-articles container">';

    foreach($articles as $article) {
        // Get the post object
        $post = get_post($article['ID']);
        setup_postdata($post);

        // Build the template
        $body .= sprintf(
            '<li>   
                %1$s
                <div class="content">
                    <h2>%2$s</h2>
                    <p>%3$s</p>
                    <a href="%4$s" class="button">Read More</a>
                </div>
            </li>', 
            get_the_post_thumbnail($post), 
            esc_html(get_the_title($post)),
            esc_html( wp_trim_words(get_the_content($post), 30 ) ),
            esc_url( get_the_permalink($post) )
        );
        wp_reset_postdata();
    } // endforeach
    $body .= '</ul>';

    return $body;
 }



 /** Adds the Featured Image URL to the WP REST API Response */

 add_action('rest_api_init', 'kf_rest_api_image');
 function kf_rest_api_image() {
     register_rest_field( 'post', 'post_image', array(
        'get_callback' => 'kf_get_featured_image', 
        'update_callback' => null,
        'schema' => null
     ) );
 }

 function kf_get_featured_image( $object, $field_name, $request) {
     if($object['featured_media']) {
        $img = wp_get_attachment_image_src( $object['featured_media'], 'medium');
        return $img[0];
     }
     return false;
 }