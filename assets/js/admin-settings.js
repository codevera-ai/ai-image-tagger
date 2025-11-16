(function($) {
    'use strict';

    $(document).ready(function() {
        // Move notices from inside header to after header
        $('.ait-header .notice').each(function() {
            $(this).insertAfter('.ait-header');
        });

        // Disclosure accordion
        $('.ait-disclosure-header').on('click', function() {
            const button = $(this);
            const content = button.next('.ait-disclosure-content');
            const toggle = button.find('.ait-disclosure-toggle');
            const isExpanded = button.attr('aria-expanded') === 'true';

            if (isExpanded) {
                content.slideUp(200);
                button.attr('aria-expanded', 'false');
                toggle.removeClass('ait-disclosure-open');
            } else {
                content.slideDown(200);
                button.attr('aria-expanded', 'true');
                toggle.addClass('ait-disclosure-open');
            }
        });

        // Show/hide API key fields based on selected provider
        function toggleApiKeyFields() {
            const selectedProvider = $('#default_provider').val();
            $('.api-key-row').hide();
            $('.api-key-row[data-provider="' + selectedProvider + '"]').show();
        }

        // Initialize on page load
        toggleApiKeyFields();

        // Handle provider dropdown change
        $('#default_provider').on('change', toggleApiKeyFields);

        // Test API connection
        $('.ai-test-connection').on('click', function() {
            const button = $(this);
            const provider = button.data('provider');
            const statusDiv = $('.ai-connection-status[data-provider="' + provider + '"]');
            const apiKeyInput = $('#ai_image_tagger_api_key_' + provider);
            const apiKey = apiKeyInput.val();

            button.addClass('testing').prop('disabled', true);
            button.text(aiImageTagger.strings.testing || 'Testing...');
            statusDiv.hide().removeClass('success error').empty();

            // Prepare data
            const ajaxData = {
                action: 'ai_test_connection',
                nonce: aiImageTagger.nonce,
                provider: provider
            };

            // If user entered a new key in the field, test that
            // Otherwise test the stored key (backend will use stored key when api_key is null)
            if (apiKey && apiKey.trim() !== '') {
                ajaxData.api_key = apiKey;
            }

            $.ajax({
                url: aiImageTagger.ajaxUrl,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    if (response.success) {
                        statusDiv.addClass('success')
                            .html('<span class="dashicons dashicons-yes-alt"></span> Connection successful')
                            .fadeIn();
                    } else {
                        const errorMessage = response.data && response.data.message
                            ? response.data.message
                            : 'Connection failed';

                        statusDiv.addClass('error')
                            .html('<span class="dashicons dashicons-warning"></span> <strong>Error:</strong> ' +
                                  $('<div>').text(errorMessage).html())
                            .fadeIn();
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'Connection failed';

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
                            // Use default error message
                            errorMessage = 'Connection failed: ' + (error || 'Unknown error');
                        }
                    }

                    statusDiv.addClass('error')
                        .html('<span class="dashicons dashicons-warning"></span> <strong>Error:</strong> ' +
                              $('<div>').text(errorMessage).html())
                        .fadeIn();
                },
                complete: function() {
                    button.removeClass('testing').prop('disabled', false);
                    button.text(aiImageTagger.strings.testConnection || 'Test connection');
                }
            });
        });

        // Delete API key
        $('.ai-delete-api-key').on('click', function() {
            const button = $(this);
            const provider = button.data('provider');
            const statusDiv = $('.ai-connection-status[data-provider="' + provider + '"]');

            if (!confirm(aiImageTagger.strings.confirmDelete || 'Are you sure you want to delete this API key?')) {
                return;
            }

            button.prop('disabled', true);
            statusDiv.hide().removeClass('success error');

            $.ajax({
                url: aiImageTagger.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_delete_api_key',
                    nonce: aiImageTagger.nonce,
                    provider: provider
                },
                success: function(response) {
                    if (response.success) {
                        statusDiv.addClass('success').text(response.data.message || 'API key deleted successfully').fadeIn();
                        // Reload the page after a short delay to update the UI
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        statusDiv.addClass('error').text(response.data.message || 'Failed to delete API key').fadeIn();
                        button.prop('disabled', false);
                    }
                },
                error: function() {
                    statusDiv.addClass('error').text('Failed to delete API key').fadeIn();
                    button.prop('disabled', false);
                }
            });
        });
    });
})(jQuery);
