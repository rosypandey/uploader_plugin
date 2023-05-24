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
        'view'=>'',
    ),$atts);
    $post_per_page=get_option('image_settings');

    $posts_per_page = isset( $post_per_page['num_photo'] ) ? $post_per_page['num_photo'] : '';

    // Query attachments from the database
    $args = array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'post_parent' => $atts['id'],
        'posts_per_page'=>$posts_per_page,
    );
    $attachments = get_posts($args);

    if ( $atts['view'] === 'slider' ){
        ?>
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
        <?php
    }

    if ( $atts['view'] === 'grid' ) {
        include( 'templates/pic_grid.php' );
    } elseif( $atts['view'] === 'slider' ) {
        include( 'templates/pic_slider.php' );
    } else {
        include( 'templates/pic_normal.php' );
    }

    if ( $atts['view'] === 'slider' ){
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
    $post_per_page=get_option('image_settings');
    $posts_per_page = isset( $post_per_page['num_photo'] ) ? $post_per_page['num_photo'] : '';
    $checkbox_value = isset( $post_per_page['enable_image_download'] ) ? $post_per_page['enable_image_download'] : 'off';

    ?>
        <h1>Image Settings</h1>
        <form method='post'>
        <label for="num_photo"><strong>No of photos to display</strong></label><br>
        <input type="number" id="num_photo" name="num_photo" min="1" max="20" value="<?php echo $posts_per_page; ?>"><br><br>
        <input type="checkbox" id="checkbox1" name="enable_image_download" <?php checked( 'on', $checkbox_value );  ?> >
        <label for="checkbox1"><strong>Enable image download</strong></label><br><br>
        <input type='submit'  id='submit' value='save changes' name='save'/>
       </form>
    <?php
   
}

/*
 * Save form data in database.
 */
function picperfect_save_settings(){
 // To save datas in database.
 if( isset($_POST['save']) ) {
    // print_r($_POST);
    $Postdata = array();
    $Postdata['num_photo']=sanitize_text_field($_POST['num_photo']);
    $Postdata['enable_image_download']=isset($_POST['enable_image_download'])?$_POST['enable_image_download']:'off';
    update_option('image_settings', $Postdata);
}
}
add_action('admin_init','picperfect_save_settings');