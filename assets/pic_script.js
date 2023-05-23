(function(){
    jQuery(document).ready(function($) {
        var mediaUploader;
        // Handle image upload button click
        $('#my-image-upload-button').on('click', function(e) {
            e.preventDefault();
            // If the media uploader  exists, open it
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            // Create a new media uploader
            mediaUploader = wp.media({
                frame: 'select',
                multiple: true
            });
            // When an image is selected, add it to the preview and hidden field
            mediaUploader.on('select', function() {
                var attachments = mediaUploader.state().get('selection').toJSON();
                var imageUrls = [];
                var imagePreview = '';
                // Build the preview and collect image URLs
                $.each(attachments, function(index, attachment) {
                    imagePreview += '<div class="image-preview"><img src="' + attachment.url + '" alt="Image Preview" /></div>';
                    imageUrls.push(attachment.url);
                });
                // Update the preview and hidden field values
                $('.image-preview').remove();
                $('#my-image-urls').val(imageUrls.join(','));
                $(imagePreview).insertBefore('#my-image-upload-button');
            });
            // Open the media uploader
            mediaUploader.open();
        });
        // Handle image remove button click
        $('#my-image-remove-button').on('click', function(e) {
            e.preventDefault();
            // Clear the preview and hidden field values
            $('.image-preview').remove();
            $('#my-image-urls').val('');
        });
    });
    }());


