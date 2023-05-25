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

 /*
  * Register custom post type.
  */
 function picperfect_create_custom_post_type() {
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
add_action('init', 'picperfect_create_custom_post_type');

/*
  * Enqueue custom js in my admin site.
  */
function picperfects_enqueue_scripts() {
    wp_enqueue_media();
    // Enqueue your JavaScript file
    wp_register_script( 'pic_script', plugins_url('assets/pic_script.js', __FILE__), array( 'jquery' ), NULL, true );
    
}
// Hook the function to the 'wp_enqueue_scripts' action
add_action('admin_enqueue_scripts', 'picperfects_enqueue_scripts');

/**
 * Enqueue the cdn links,customjs,custom css for client side
 */
function picperfects_enqueue_assets(){
    wp_register_style( 'swipercss-cdn', 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css' );
    wp_register_script( 'swiperjs_cdn', 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js', null, null, true );
    
    //custom css
    wp_register_style('pic_style', plugins_url('assets/pic_style.css', __FILE__), array( 'swipercss-cdn' ));
    // Swiper slider cdn.
    wp_register_script( 'pic_script_frontend', plugins_url('assets/pic_frontent.js', __FILE__), array( 'jquery', 'swiperjs_cdn' ), NULL, true );
}
add_action('wp_enqueue_scripts', 'picperfects_enqueue_assets');


/**
 * Add Shortcode column header.
 */  
function picperfect_shortcode_column_header($columns) {
    $columns['shortcode'] = 'Shortcode';
    return $columns;
}
add_filter('manage_picperfects_posts_columns', 'picperfect_shortcode_column_header');


/*
 * Add shortcode column content.
 */  
function picperfect_shortcode_column_content($column_name, $post_id) {
    if ($column_name == 'shortcode') {
        $picperfect_shortcode = '[picperfect_display_view id="'.$post_id.'"]';
        echo esc_html($picperfect_shortcode);
    }
}
add_action('manage_picperfects_posts_custom_column', 'picperfect_shortcode_column_content', 10, 2);

/**
 * Add the metabox.
 */ 
function my_image_metabox() {
    add_meta_box('my-image-metabox', 'Image Uploader', 'my_image_metabox_callback', 'picperfects', 'normal', 'high');
}
add_action('add_meta_boxes', 'my_image_metabox');

/*
  * Metabox callback function
 */
function my_image_metabox_callback($post) {
    wp_enqueue_script('pic_script');
    $args = array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'post_parent' => $post->ID,
    );
    // print_r($args);
    $attachments = get_posts($args);
    foreach ($attachments as $attachment) {
        $image_src = wp_get_attachment_image_src($attachment->ID, 'thumbnail');
        $image_url = $image_src[0];
        ?>
        <div class="image-preview">
            <img src=<?php echo $image_url; ?>  class="remove-image"></div>
        <?php
    }
    // Retrieve saved metadata
    $images = get_post_meta($post->ID, 'my_image_metabox_images', true);
    // Display the uploaded images
    if ($images) {
        foreach ($images as $image) {
            echo '<div class="image-preview ">';
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

/*
  * Save the metadata
 */
function my_image_metabox_save($post_id) {
    if (isset($_POST['my_image_metabox_images'])) {
        $images = $_POST['my_image_metabox_images'];
        update_post_meta($post_id, 'my_image_metabox_images', $images);
    }}


/**
 * Register shortcode to diplay the images in different view
 */
function picperfect_display_image_view($atts){

    wp_enqueue_style('pic_style');
    wp_enqueue_script( 'pic_script_frontend');

    //to retrive the all attachments from database
    ob_start();
    $atts=shortcode_atts(array(
        'id'=>'',
    ),$atts);

  $setting_view = get_option('image_settings'); 

    // Query attachments from the database
    $args = array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'post_parent' => $atts['id'],
    );
    $attachments = get_posts($args);

    if ( $setting_view === 'slider' ){
        ?>
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
        <?php
    }

    if ( $setting_view === 'grid' ) {
        include( 'templates/pic_grid.php' );
    } elseif( $setting_view === 'slider' ) {
        include( 'templates/pic_slider.php' );
    } elseif( $setting_view === 'normal' ) {
        include( 'templates/pic_normal.php' );
    }

    if ( $setting_view === 'slider' ){
        ?>
            </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        <?php
    }

return ob_get_clean();
}
add_shortcode('picperfect_display_view','picperfect_display_image_view');

/**
 *  Add setting submenu 
 */
function picperfect_setting_submenu(){
    add_submenu_page(
        'edit.php?post_type=picperfects',
        'Settings',
        'Settings',
        'manage_options',
        'setting',//slug
        'picperfect_setting_callback'
    );
}
add_action('admin_menu','picperfect_setting_submenu');

/**
 * Setting submenu callback function
 */
function picperfect_setting_callback(){
    // to display content 
    $post_view=get_option('image_settings');
    // echo "<pre>";
    // print_r($post_view);
    // $checkbox_value = isset( $post_view['enable_slider_view'] ) ? $post_view['enable_slider_view'] : 'off';

    ?>
        <h1>Image Settings</h1>
        <form method='post'>
            <label for="grid_view">
            <input type="radio" name="view_type" id="grid_view" value="grid" 
                <?php
                    if($post_view == 'grid'){
                        echo 'checked';
                    }
                ?>
            /> Enable Grid View
            </label><br><br>
    
            <label for="slider_view">
                <input type="radio" name="view_type" id="slider_view" value="slider"
                    <?php
                        if($post_view == 'slider'){
                            echo 'checked';
                        }
                    ?>
                /> Enable Slider View
            </label><br><br>
            <label for="normal_view">
                <input type="radio" name="view_type" id="normal_view" value="normal"
                    <?php
                        if($post_view == 'normal'){
                            echo 'checked';
                        }
                    ?>
                /> Enable normal View
            </label><br><br>
            <input type='submit'  id='submit' value='save changes' name='save'/>
       </form>
    <?php
   
}

/*
 * Save form data in database.
 */
function picperfect_save_settings() {
    // Check if the 'save' button was clicked
    if (isset($_POST['save'])) {
        // Check if the 'view_type' radio button is set
        if (isset($_POST['view_type'])) {
            // Sanitize the input to prevent any potential security issues
            $view_type = sanitize_text_field($_POST['view_type']);
            // Save the 'view_type' value in the database
            update_option('image_settings', $view_type);
        }
    }
}
add_action('admin_init', 'picperfect_save_settings');
