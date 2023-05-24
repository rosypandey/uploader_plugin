<?php
    foreach ($attachments as $attachment) {
        $image_src = wp_get_attachment_image_src($attachment->ID, 'full');
        $image_url = $image_src[0];
        ?>
        <div class="col" >
            <img src=<?php echo $image_url; ?> style="width:100%;" class="zoom"/>
        </div>
        <?php
    }
    