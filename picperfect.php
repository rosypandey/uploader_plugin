<?php
/*
 * Plugin Name:      picperfect
 * Version:          1.0.0
 * Plugin URI:       https://example.com/plugins/the-basics/
 * Description:      create visually appealing and organized galleries on your website!                
 * Author:           Rosy Pandey
 * License:          GPL v2 or later
 * License URI:      https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:       https://example.com/mytestimonials/
 * TextDomain:       picperfect
 */
        
 function create_custom_post_type() {
 $labels = array(
        'name' => 'picperfects',
        'singular_name' => 'picperfects',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
    );

    register_post_type('picperfects', $args);
}
add_action('init', 'create_custom_post_type');

// Add media uploader to custom post type
function picperfects_enqueue_scripts() {
    wp_enqueue_media();
    // Enqueue your JavaScript file
    wp_register_script( 'pic_script', plugins_url('assets/pic_script.js', __FILE__), array( 'jquery' ), NULL, true );
}
// Hook the function to the 'wp_enqueue_scripts' action
add_action('admin_enqueue_scripts', 'picperfects_enqueue_scripts');


//  Add Shortcode column header
function picperfect_shortcode_column_header($columns) {
    $columns['shortcode'] = 'Shortcode';
    return $columns;
}
add_filter('manage_picperfects_posts_columns', 'picperfect_shortcode_column_header');


// Add shortcode column content
function picperfect_shortcode_column_content($column_name, $post_id) {

    if ($column_name == 'shortcode') {
        $picperfect_shortcode = '[picperfect_display_images id="'.$post_id.'"]';
        echo esc_html($picperfect_shortcode);
    }
}
add_action('manage_picperfects_posts_custom_column', 'picperfect_shortcode_column_content', 10, 2);


// Add the metabox
function my_image_metabox() {
    add_meta_box('my-image-metabox', 'Image Uploader', 'my_image_metabox_callback', 'picperfects', 'normal', 'high');
}
add_action('add_meta_boxes', 'my_image_metabox');

// Metabox callback function
function my_image_metabox_callback($post) {
    wp_enqueue_script('pic_script');
    // Retrieve saved metadata
    $images = get_post_meta($post->ID, 'my_image_metabox_images', true);
 
    // Display the uploaded images
    if ($images) {
        foreach ($images as $image) {
            echo '<div class="image-preview">';
            echo '<img src="' . $image . '" alt="Image Preview" />';
            echo '</div>';
        }
    }

    ?>
        <!-- Add the Upload button -->
        <input type="button" class="button button-secondary" id="my-image-upload-button" value="Upload Image" />
        <!-- Add the Remove button -->
        <input type="button" class="button button-secondary" id="my-image-remove-button" value="Remove Image" />
        <!-- Add a hidden field to store the image URLs -->
        <input type="hidden" id="my-image-urls" name="my_image_metabox_images" value=<?php echo esc_attr($images) ?> />
    <?php
}

// Save the metadata
function my_image_metabox_save($post_id) {
    if (isset($_POST['my_image_metabox_images'])) {
        $images = $_POST['my_image_metabox_images'];
        update_post_meta($post_id, 'my_image_metabox_images', $images);
    }}
/**
 * Regiter shortcode
 */
    function display_images_shortcode() {
        // Query attachments from the database
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'posts_per_page' => 10, 
        );
        
        $attachments = get_posts($args);
        // echo '<pre>';
        // print_r( $attachments);
        // Output the images
        $output = '';
        foreach ($attachments as $attachment) {
            $image_src = wp_get_attachment_image_src($attachment->ID, 'full');
            $image_url = $image_src[0];
            $image_alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
            $output .= '<img src="' . $image_url . '" alt="' . $image_alt . '">';
        }
    
        return $output;
    }
    add_shortcode('picperfect_display_images', 'display_images_shortcode');
    


