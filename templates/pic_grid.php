<div class="image-grid">
  <?php
    foreach ($attachments as $attachment) {
      $image_src = wp_get_attachment_image_src($attachment->ID, 'full');
      $image_url = $image_src[0];
      ?>
        <img src=<?php echo $image_url; ?> class="zoom">
      <?php
    }
  ?>
</div>