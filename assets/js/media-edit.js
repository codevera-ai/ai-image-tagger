(function($) {
    'use strict';

    $(document).ready(function() {
        // Generate metadata
        $('.ai-generate-metadata').on('click', function() {
            const button = $(this);
            const attachmentId = button.data('attachment-id');
            const metabox = button.closest('.ai-image-tagger-metabox');
            const statusDiv = metabox.find('.ai-processing-status');
            const errorDiv = metabox.find('.ai-error-message');

            button.prop('disabled', true);
            statusDiv.show();
            errorDiv.hide();

            $.ajax({
                url: aiImageTagger.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_generate_metadata',
                    nonce: aiImageTagger.nonce,
                    attachment_id: attachmentId
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        const errorMessage = response.data && response.data.message
                            ? response.data.message
                            : 'Failed to generate metadata';
                        errorDiv.find('.ait-error-text').text(errorMessage);
                        errorDiv.show();
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'An error occurred';

                    // Try to extract error details from response
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errorMessage = xhr.responseJSON.data.message;
                    } else if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.data && response.data.message) {
                                errorMessage = response.data.message;
                            }
                        } catch (e) {
                            // Use custom error string or default
                            errorMessage = aiImageTagger.strings.error || 'Failed to generate metadata: ' + (error || 'Unknown error');
                        }
                    }

                    errorDiv.find('.ait-error-text').text(errorMessage);
                    errorDiv.show();
                },
                complete: function() {
                    button.prop('disabled', false);
                    statusDiv.hide();
                }
            });
        });

        // Regenerate metadata
        $('.ai-regenerate-metadata').on('click', function() {
            if (!confirm(aiImageTagger.strings.confirmRegenerate || 'Are you sure you want to regenerate the metadata?')) {
                return;
            }

            const button = $(this);
            const attachmentId = button.data('attachment-id');
            const metabox = button.closest('.ai-image-tagger-metabox');
            const statusDiv = metabox.find('.ai-processing-status');
            const errorDiv = metabox.find('.ai-error-message');

            button.prop('disabled', true);
            statusDiv.show();
            errorDiv.hide();

            $.ajax({
                url: aiImageTagger.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_regenerate_metadata',
                    nonce: aiImageTagger.nonce,
                    attachment_id: attachmentId
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        const errorMessage = response.data && response.data.message
                            ? response.data.message
                            : 'Failed to regenerate metadata';
                        errorDiv.find('.ait-error-text').text(errorMessage);
                        errorDiv.show();
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'An error occurred';

                    // Try to extract error details from response
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errorMessage = xhr.responseJSON.data.message;
                    } else if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.data && response.data.message) {
                                errorMessage = response.data.message;
                            }
                        } catch (e) {
                            // Use custom error string or default
                            errorMessage = aiImageTagger.strings.error || 'Failed to regenerate metadata: ' + (error || 'Unknown error');
                        }
                    }

                    errorDiv.find('.ait-error-text').text(errorMessage);
                    errorDiv.show();
                },
                complete: function() {
                    button.prop('disabled', false);
                    statusDiv.hide();
                }
            });
        });
    });
})(jQuery);
