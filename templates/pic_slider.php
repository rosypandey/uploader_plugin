<?php
foreach ($attachments as $attachment) {
    $image_src = wp_get_attachment_image_src($attachment->ID, 'thumbnail');
    $image_url = $image_src[0];
    ?>
        <div class="swiper-slide">
            <img src="<?php echo $image_url; ?>" >
        </div>
    <?php
}
