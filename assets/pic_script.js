
// (function(){
//     jQuery(document).ready(function($) {
//         var mediaUploader;
//         // Handle image upload button click
//         $('#my-image-upload-button').on('click', function(e) {
//             e.preventDefault();
//             // If the media uploader  exists, open it
//             if (mediaUploader) {
//                 mediaUploader.open();
//                 return;
//             }
//             // Create a new media uploader
//             mediaUploader = wp.media({
//                 frame: 'select',
//                 multiple: true
//             });
//             // When an image is selected, add it to the preview and hidden field
//             mediaUploader.on('select', function() {
//                 var attachments = mediaUploader.state().get('selection').toJSON();
//                 var imageUrls = [];
//                 var imagePreview = '';
//                 // Build the preview and collect image URLs
//                 $.each(attachments, function(index, attachment) {
//                     imagePreview += '<div class="image-preview"><img src="' + attachment.url + '" alt="Image Preview" /></div>';
//                     imageUrls.push(attachment.url);
//                 });
//                 // Update the preview and hidden field values
//                 $('.image-preview').remove();
//                 $('#my-image-urls').val(imageUrls.join(','));
//                 $(imagePreview).insertBefore('#my-image-upload-button');
//             });
//             // Open the media uploader
//             mediaUploader.open();
//         });
//         // Handle image remove button click
//         $('#my-image-remove-button').on('click', function(e) {
//             e.preventDefault();
//             debugger
//             // Clear the preview and hidden field values
//             $('.image-preview').remove();
//             $('#my-image-urls').val('');
//         });
//     });
//     }());

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
                        imagePreview += '<div class="image-preview"><img src="' + attachment.url + '" alt="Image Preview" /><span class="remove-image">&times;</span></div>';
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
                // Remove the image preview and update the hidden field value
                imageContainer.remove();
                updateHiddenField();
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