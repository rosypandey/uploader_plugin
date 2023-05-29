(function() {
        jQuery(document).ready(function($) {
            var mediaUploader;
            var selectedAttachmentIds = []; // Array to store selected attachment IDs
            // Handle image upload button click
            $('#my-image-upload-button').on('click', function(e) {
                e.preventDefault();
                // If the media uploader exists, open it with preselected images
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                // Create a new media uploader
                mediaUploader = wp.media({
                    frame: 'select',
                    multiple: true
                });
                // Preselect previously selected images
                mediaUploader.on('open', function() {
                    var selection = mediaUploader.state().get('selection');
                    var attachments = [];
                    
                    // Retrieve the previously selected attachments
                    $.each(selectedAttachmentIds, function(index, attachmentId) {
                        var attachment = wp.media.attachment(attachmentId);
                        attachments.push(attachment);
                    });
                    // Set the preselected attachments
                    selection.reset(attachments);
                });
                // When an image is selected, add it to the preview and hidden field
                mediaUploader.on('select', function() {
                    var attachments = mediaUploader.state().get('selection').toJSON();
                    var imageUrls = [];
                    var imagePreview = '';
                    // Build the preview and collect image URLs
                    $.each(attachments, function(index, attachment) {
                        imagePreview += '<div class="image-preview"><img src="' + attachment.url + '" alt="Image Preview" /><button class="remove-image">delete</button></div>';
                        imageUrls.push(attachment.url);
                    });
                    // Update the preview and hidden field values
                    $(imagePreview).insertBefore('#my-image-upload-button');
                    $('#my-image-urls').val(imageUrls.join(','));
                    // Store the selected attachment IDs for future reference
                    selectedAttachmentIds = attachments.map(function(attachment) {
                        return attachment.id;
                    });
                });
                // Open the media uploader
                mediaUploader.open();
            });
            // Handle individual image remove button click
            $(document).on('click', '.remove-image', function() {
                var imageContainer = $(this).closest('.image-preview');
                var imageUrl = imageContainer.find('img').attr('src');

               // Make an asynchronous request to delete the image from the server
                $.ajax({
                    url: custom_ajax.ajax_url,
                    type: 'POST',
                    data: { 
                        imageUrl: imageUrl,
                        action: 'delete_image' 
                    },
                    success: function(data) {
                        // If the image is successfully deleted from the server, remove the image preview and update the hidden field
                        // imageContainer.remove();
                        console.log(data);
                        // updateHiddenField();
                    },
                });
            });
            // Handle remove all images button click
            $('#my-image-remove-button').on('click', function(e) {
                e.preventDefault();
                // Clear the preview, hidden field values, and selected attachment IDs
                $('.image-preview').remove();
                $('#my-image-urls').val('');
                selectedAttachmentIds = [];
            });
            // Update the hidden field value
            function updateHiddenField() {
                var imageUrls = [];
                $('.image-preview img').each(function() {
                    imageUrls.push($(this).attr('src'));
                });
                $('#my-image-urls').val(imageUrls.join(','));
            }
        });
    })();
    



